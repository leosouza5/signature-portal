<?php

namespace App\Services;

use App\Repositories\DocumentRepository;
use App\Repositories\EnvelopeRepository;
use App\Repositories\SignerRepository;

class EnvelopeService
{
    private EnvelopeRepository $envelopes;
    private DocumentRepository $documents;
    private SignerRepository $signers;
    private CertisignService $certisign;

    public function __construct()
    {
        $this->envelopes = new EnvelopeRepository();
        $this->documents = new DocumentRepository();
        $this->signers = new SignerRepository();
        $this->certisign = new CertisignService();
    }

    public function createAndSend(int $userId, string $title, array $files, array $signerInput): int
    {
        $this->validate($title, $files, $signerInput);

        $envelopeId = $this->envelopes->create($userId, $title);

        try {
            foreach ($signerInput as $index => $signer) {
                $this->signers->create(
                    $envelopeId,
                    trim($signer['name']),
                    trim($signer['email']),
                    preg_replace('/\D/', '', $signer['cpf']),
                    $index + 1
                );
            }

            foreach ($this->normalizeFiles($files) as $file) {
                $localPath = $this->saveUploadedFile($file);
                $documentId = $this->documents->create($envelopeId, $file['name'], $localPath);
                $uploadId = $this->certisign->uploadDocument($localPath, $file['name']);
                $this->documents->updateUploadId($documentId, $uploadId);
            }

            $documents = $this->documents->allByEnvelope($envelopeId);
            $signers = $this->signers->allByEnvelope($envelopeId);
            $response = $this->certisign->createBatch($documents, $signers);
            $this->saveBatchResponse($documents, $signers, $response);
            $this->envelopes->updateStatus($envelopeId, 'SENT');
        } catch (\Exception $exception) {
            $this->envelopes->updateStatus($envelopeId, 'ERROR', $exception->getMessage());
            throw $exception;
        }

        return $envelopeId;
    }

    public function downloadFirstPackage(int $envelopeId): array
    {
        $documents = $this->documents->allByEnvelope($envelopeId);
        $key = $documents[0]['certisign_document_key'] ?? null;

        if (!$key) {
            throw new \Exception('Documento ainda nao possui chave da Certisign para download.');
        }

        $package = $this->certisign->downloadPackage($key);

        if (empty($package['bytes']) || empty($package['name'])) {
            throw new \Exception('Pacote retornado pela Certisign esta incompleto.');
        }

        $this->envelopes->updateStatus($envelopeId, 'COMPLETED');
        return $package;
    }

    private function validate(string $title, array $files, array $signers): void
    {
        if (trim($title) === '') {
            throw new \Exception('Informe o titulo do envelope.');
        }

        if (empty($files['name'][0])) {
            throw new \Exception('Envie pelo menos um documento.');
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
            
                throw new \Exception('Falha no upload de' . $name );
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
        $relativePath = 'storage/uploads/' . uniqid('doc_', true) . '_' . $safeName;
        $fullPath = __DIR__ . '/../../' . $relativePath;

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
            $documentKey = $result['key'] ?? $result['documentKey'] ?? $result['packageKey'] ?? null;
            $this->documents->updateCertisignData((int) $document['id'], $documentId, $documentKey, 'SENT');
        }

        $attendees = $this->extractAttendees($response);

        foreach ($signers as $index => $signer) {
            $attendee = $attendees[$index] ?? [];
            $this->signers->updateCertisignData(
                (int) $signer['id'],
                $attendee['id'] ?? $attendee['attendeeId'] ?? null,
                $attendee['signUrl'] ?? $attendee['url'] ?? null
            );
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
        if (isset($response['attendees']) && is_array($response['attendees'])) {
            return $response['attendees'];
        }

        foreach ($this->extractDocumentResults($response) as $document) {
            if (isset($document['attendees']) && is_array($document['attendees'])) {
                return $document['attendees'];
            }
        }

        return [];
    }
}
