<?php

namespace App\Services;

use App\Repositories\DocumentRepository;
use App\Repositories\SignerRepository;

class EnvelopeService
{
    private DocumentRepository $documents;
    private SignerRepository $signers;
    private CertisignService $certisign;

    public function __construct()
    {
        $this->documents = new DocumentRepository();
        $this->signers = new SignerRepository();
        $this->certisign = new CertisignService();
    }

    public function createAndSend(int $userId, string $title, array $files, array $signerInput): int
    {
        $this->validate($title, $files, $signerInput);

        $normalizedFiles = $this->normalizeFiles($files);
        $file = $normalizedFiles[0];
        $localPath = $this->saveUploadedFile($file);
        $documentId = $this->documents->create($userId, $title, $file['name'], $localPath);

        try {
            foreach ($signerInput as $index => $signer) {
                $this->signers->create(
                    $documentId,
                    trim($signer['name']),
                    trim($signer['email']),
                    preg_replace('/\D/', '', $signer['cpf']),
                    $index + 1
                );
            }

            $uploadId = $this->certisign->uploadDocument($localPath, $file['name']);
            $this->documents->updateUploadId($documentId, $uploadId);

            $document = $this->documents->findById($documentId);
            $signers = $this->signers->allByDocument($documentId);
            $response = $this->certisign->createBatch([$document], $signers);
            error_log('[createBatch response] ' . json_encode($response));
            $this->saveBatchResponse([$document], $signers, $response);
            $this->documents->updateStatus($documentId, 'SENT');
        } catch (\Exception $exception) {
            $this->documents->delete($documentId);
            throw $exception;
        }

        return $documentId;
    }

    public function checkStatus(int $documentId): array
    {
        $document = $this->documents->findById($documentId);

        if (!$document) {
            throw new \Exception('Documento nao encontrado.');
        }

        $portalId = (string) ($document['certisign_document_id'] ?? '');

        if ($portalId === '') {
            return ['status' => $document['status']];
        }

        $details = $this->certisign->getDocumentDetails($portalId);
        error_log('[document/details ' . $portalId . '] ' . json_encode($details));

        $status = $this->parseDocumentStatus($details);
        $this->documents->updateStatus($documentId, $status);

        $key = $document['certisign_document_key'] ?? null;
        if ($key) {
            $signers = $this->signers->allByDocument($documentId);
            $validation = $this->certisign->validateSignatures($key);
            $this->updateSignerStatuses($signers, $validation['electronicSignatures'] ?? []);
        }

        return ['status' => $status];
    }

    private function updateSignerStatuses(array $signers, array $electronicSignatures): void
    {
        foreach ($signers as $signer) {
            $signed = false;
            $signerCpf = preg_replace('/\D/', '', (string) $signer['cpf']);
            $signerEmail = strtolower(trim($signer['email']));

            foreach ($electronicSignatures as $signature) {
                $evidences = $signature['evidences'] ?? [];
                $evidenceMap = array_column($evidences, 'value', 'name');

                $sigCpf = preg_replace('/\D/', '', (string) ($evidenceMap['signerIdentifier'] ?? ''));
                $sigEmail = strtolower(trim((string) ($evidenceMap['email'] ?? $evidenceMap['externalEmail'] ?? '')));

                if (($signerCpf !== '' && $signerCpf === $sigCpf) || ($signerEmail !== '' && $signerEmail === $sigEmail)) {
                    $signed = true;
                    break;
                }
            }

            $this->signers->updateStatus((int) $signer['id'], $signed ? 'SIGNED' : 'PENDING');
        }
    }

    public function downloadDocument(int $documentId, int $userId): array
    {
        $document = $this->documents->findForUser($documentId, $userId);

        if (!$document) {
            throw new \Exception('Documento nao encontrado.');
        }

        if ($document['status'] !== 'COMPLETED') {
            throw new \Exception('Documento ainda nao esta disponivel para download.');
        }

        $key = $document['certisign_document_key'] ?? null;

        if (!$key) {
            throw new \Exception('Chave do documento nao disponivel.');
        }

        $package = $this->certisign->downloadPackage($key);

        if (empty($package['bytes'])) {
            throw new \Exception('Pacote retornado pela Certisign esta incompleto.');
        }

        return $package;
    }

    private function parseDocumentStatus(array $details): string
    {
        if ($details['rejected'] ?? false) {
            return 'ERROR';
        }

        if ($details['completed'] ?? false) {
            return 'COMPLETED';
        }

        if ($details['partiallySigned'] ?? false) {
            return 'PARTIAL';
        }

        return 'SENT';
    }

    private function validate(string $title, array $files, array $signers): void
    {
        if (trim($title) === '') {
            throw new \Exception('Informe o titulo do documento.');
        }

        $fileCount = count(array_filter($files['name'] ?? [], fn($n) => $n !== ''));
        if ($fileCount === 0) {
            throw new \Exception('Envie o arquivo PDF do documento.');
        }
        if ($fileCount > 1) {
            throw new \Exception('Envie apenas um arquivo por documento.');
        }

        if (empty($signers)) {
            throw new \Exception('Informe pelo menos um assinante.');
        }

        foreach ($signers as $signer) {
            if (empty($signer['name']) || empty($signer['email']) || empty($signer['cpf'])) {
                throw new \Exception('Preencha nome, e-mail e CPF de todos os assinantes.');
            }
        }
    }

    private function normalizeFiles(array $files): array
    {
        $items = [];

        foreach ($files['name'] as $index => $name) {
            if ($files['error'][$index] === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            if ($files['error'][$index] !== UPLOAD_ERR_OK) {
                throw new \Exception('Falha no upload de ' . $name);
            }

            $items[] = [
                'name' => $name,
                'tmp_name' => $files['tmp_name'][$index],
                'type' => $files['type'][$index],
            ];
        }

        return $items;
    }

    private function saveUploadedFile(array $file): string
    {
        $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
        $uploadDir = __DIR__ . '/../../storage/uploads/';
        $fullPath = $uploadDir . uniqid('doc_', true) . '_' . $safeName;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            throw new \Exception('Nao foi possivel salvar o arquivo localmente.');
        }

        return $fullPath;
    }

    private function saveBatchResponse(array $documents, array $signers, array $response): void
    {
        $documentResults = $this->extractDocumentResults($response);

        foreach ($documents as $index => $document) {
            $result = $documentResults[$index] ?? $response;
            $documentId = $result['id'] ?? $result['documentId'] ?? null;
            $documentKey = $result['chave'] ?? $result['key'] ?? $result['documentKey'] ?? $result['packageKey'] ?? null;
            $this->documents->updateCertisignData((int) $document['id'], $documentId, $documentKey, 'SENT');
        }

        $attendees = $this->extractAttendees($response);

        foreach ($signers as $signer) {
            $signUrl = null;
            $attendeeId = null;

            foreach ($attendees as $attendee) {
                if (!is_array($attendee)) {
                    continue;
                }

                $attendeeEmail = strtolower(trim((string) ($attendee['email'] ?? '')));
                $attendeeCpf = preg_replace('/\D/', '', (string) ($attendee['individualIdentificationCode'] ?? $attendee['cpf'] ?? ''));
                $signerCpf = preg_replace('/\D/', '', (string) $signer['cpf']);

                $matchesEmail = $attendeeEmail !== '' && $attendeeEmail === strtolower(trim($signer['email']));
                $matchesCpf = $attendeeCpf !== '' && $attendeeCpf === $signerCpf;

                if (!$matchesEmail && !$matchesCpf) {
                    continue;
                }

                $attendeeId = $attendee['signerId'] ?? $attendee['id'] ?? $attendee['attendeeId'] ?? null;
                $batchSignUrl = $attendee['batchSignUrl'] ?? '';
                $signUrl = ($batchSignUrl !== '') ? $batchSignUrl : ($attendee['signUrl'] ?? $attendee['link'] ?? null);
                break;
            }

            $this->signers->updateCertisignData((int) $signer['id'], $attendeeId, $signUrl);
        }
    }

    private function extractDocumentResults(array $response): array
    {
        if (isset($response['documents']) && is_array($response['documents'])) {
            return $response['documents'];
        }

        if (array_is_list($response)) {
            return $response;
        }

        return [$response];
    }

    private function extractAttendees(array $response): array
    {
        $attendees = [];

        foreach ([$response['attendees'] ?? null] as $candidate) {
            if (is_array($candidate)) {
                $attendees = array_merge($attendees, $candidate);
            }
        }

        foreach ($this->extractDocumentResults($response) as $document) {
            if (isset($document['attendees']) && is_array($document['attendees'])) {
                $attendees = array_merge($attendees, $document['attendees']);
            }
        }

        if (isset($response['steps']) && is_array($response['steps'])) {
            foreach ($response['steps'] as $step) {
                if (isset($step['attendees']) && is_array($step['attendees'])) {
                    $attendees = array_merge($attendees, $step['attendees']);
                }
            }
        }

        return $attendees;
    }
}
