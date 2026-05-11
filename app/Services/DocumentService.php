<?php

namespace App\Services;

use App\Repositories\DocumentRepository;
use App\Repositories\SignerRepository;

class DocumentService
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

        $normalizedFiles = $this->prepareFiles($files);
        $file = $normalizedFiles[0];
        $localPath = $this->saveUploadedFile($file);
        $documentId = $this->documents->create($userId, $title, $file['name'], $localPath);

        try {
            $order = 1;
            foreach ($signerInput as $signer) {
                $this->signers->create(
                    $documentId,
                    trim($signer['name']),
                    trim($signer['email']),
                    str_replace(['.', '-', '/', ' '], '', $signer['cpf']),
                    $order++
                );
            }

            $uploadId = $this->certisign->uploadDocument($localPath, $file['name']);
            $this->documents->updateUploadId($documentId, $uploadId);

            $document = $this->documents->getById($documentId);
            $signers = $this->signers->getAllByDocument($documentId);
            $response = $this->certisign->createDocument([$document], $signers);
            $this->saveResponse([$document], $signers, $response);
            $this->documents->updateStatus($documentId, 'SENT');
        } catch (\Exception $exception) {
            $this->signers->deleteByDocument($documentId);
            $this->documents->delete($documentId);
            throw $exception;
        }

        return $documentId;
    }

    public function checkStatus(int $documentId): array
    {
        $document = $this->documents->getById($documentId);

        if (!$document) {
            throw new \Exception('Documento nao encontrado.');
        }

        $key = $document['certisign_document_key'] ?? null;

        if (!$key) {
            return ['status' => $document['status']];
        }

        $signers = $this->signers->getAllByDocument($documentId);
        $validation = $this->certisign->validateSignatures($key);
        $signedCount = $this->updateSignerStatuses($signers, $validation['electronicSignatures'] ?? []);

        $total = count($signers);

        if ($signedCount === 0) {
            $status = 'SENT';
        } elseif ($signedCount < $total) {
            $status = 'PARTIAL';
        } else {
            $status = 'COMPLETED';
        }

        $this->documents->updateStatus($documentId, $status);

        return ['status' => $status];
    }

    private function updateSignerStatuses(array $signers, array $electronicSignatures): int
    {
        $signedCount = 0;

        foreach ($signers as $signer) {
            $signed = false;
            $cpf = str_replace(['.', '-', '/', ' '], '', (string) $signer['cpf']);
            $email = strtolower(trim($signer['email']));

            foreach ($electronicSignatures as $signature) {
                $evidences = $signature['evidences'] ?? [];
                $evidenceMap = array_column($evidences, 'value', 'name');

                $sigCpf = str_replace(['.', '-', '/', ' '], '', (string) ($evidenceMap['signerIdentifier'] ?? ''));
                $sigEmail = strtolower(trim((string) ($evidenceMap['email'] ?? $evidenceMap['externalEmail'] ?? '')));

                $cpfMatch = $cpf !== '' && $cpf === $sigCpf;
                $emailMatch = $email !== '' && $email === $sigEmail;

                if ($cpfMatch || $emailMatch) {
                    $signed = true;
                    break;
                }
            }

            $this->signers->updateStatus((int) $signer['id'], $signed ? 'SIGNED' : 'PENDING');

            if ($signed) {
                $signedCount++;
            }
        }

        return $signedCount;
    }

    public function downloadDocument(int $documentId, int $userId): array
    {
        $document = $this->documents->getByUser($documentId, $userId);

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

    private function prepareFiles(array $files): array
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
        $fileName = str_replace(' ', '_', $file['name']);
        $uploadDir = __DIR__ . '/../../storage/uploads/';
        $fullPath = $uploadDir . uniqid('doc_', true) . '_' . $fileName;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            throw new \Exception('Nao foi possivel salvar o arquivo localmente.');
        }

        return $fullPath;
    }

    private function saveResponse(array $documents, array $signers, array $response): void
    {
        $documentResults = $this->extractDocumentResults($response);

        foreach ($documents as $index => $document) {
            $result = $documentResults[$index] ?? $response;
            $documentId = $result['id'] ?? $result['documentId'] ?? null;
            $documentKey = $result['chave'] ?? $result['key'] ?? $result['documentKey'] ?? $result['packageKey'] ?? null;
            $this->documents->updateSignatureInfo((int) $document['id'], $documentId, $documentKey, 'SENT');
        }

        $responseSigners = $this->extractSigners($response);

        foreach ($signers as $signer) {
            $signUrl = null;
            $responseSignerId = null;

            foreach ($responseSigners as $responseSigner) {
                if (!is_array($responseSigner)) {
                    continue;
                }

                $email = strtolower(trim((string) ($responseSigner['email'] ?? '')));
                $cpf = str_replace(['.', '-', '/', ' '], '', (string) ($responseSigner['individualIdentificationCode'] ?? $responseSigner['cpf'] ?? ''));
                $signerCpf = str_replace(['.', '-', '/', ' '], '', (string) $signer['cpf']);

                $emailMatch = $email !== '' && $email === strtolower(trim($signer['email']));
                $cpfMatch = $cpf !== '' && $cpf === $signerCpf;

                if (!$emailMatch && !$cpfMatch) {
                    continue;
                }

                $responseSignerId = $responseSigner['signerId'] ?? $responseSigner['id'] ?? $responseSigner['attendeeId'] ?? null;
                $batchSignUrl = $responseSigner['batchSignUrl'] ?? '';
                $signUrl = $batchSignUrl !== '' ? $batchSignUrl : ($responseSigner['signUrl'] ?? $responseSigner['link'] ?? null);
                break;
            }

            $this->signers->updateSignatureInfo((int) $signer['id'], $responseSignerId, $signUrl);
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

    private function extractSigners(array $response): array
    {
        $signers = [];

        if (!empty($response['attendees'])) {
            $signers = array_merge($signers, $response['attendees']);
        }

        foreach ($this->extractDocumentResults($response) as $document) {
            if (!empty($document['attendees'])) {
                $signers = array_merge($signers, $document['attendees']);
            }
        }

        if (!empty($response['steps'])) {
            foreach ($response['steps'] as $step) {
                if (!empty($step['attendees'])) {
                    $signers = array_merge($signers, $step['attendees']);
                }
            }
        }

        return $signers;
    }

}
