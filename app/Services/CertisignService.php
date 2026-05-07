<?php

namespace App\Services;

class CertisignService
{
    private string $baseUrl;
    private string $token;
    private string $code;

    public function __construct()
    {
        $this->baseUrl = rtrim($_ENV['CERTISIGN_BASE_URL'], '/');
        $this->token = $_ENV['CERTISIGN_TOKEN'];
        $this->code = $_ENV['CERTISIGN_CODE'];
    }

    public function uploadDocument(string $filePath, string $fileName): string
    {
        $payload = [
            'fileName' => $fileName,
            'bytes' => $this->fileToBytes($filePath),
        ];

        $response = $this->request('POST', '/document/upload', $payload);
        error_log('[document/upload response] ' . json_encode($response));

        if (empty($response['uploadId'])) {
            throw new \Exception('Upload enviado, mas uploadId nao foi retornado pela Certisign.');
        }

        return $response['uploadId'];
    }

    public function createBatch(array $documents, array $signers): array
    {
        $documents_payload = [];

        foreach ($documents as $document) {
            $name = $this->ensurePdfFileName($document['original_name']);
            $documents_payload[] = [
                'document' => [
                    'name' => $document['original_name'],
                    'upload' => [
                        'id' => $document['certisign_upload_id'],
                        'name' => $name,
                    ],
                ],
                'electronicSigners' => $this->formatSigners($signers),
            ];
        }

        try {
            return $this->request('POST', '/document/createBatch', ['documents' => $documents_payload]);
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'HTTP 406') && str_contains($e->getMessage(), '739')) {
                return $this->request('POST', '/document/create', $documents_payload[0]);
            }
            throw $e;
        }
    }

    public function getDocumentDetails(string $documentId): array
    {
        return $this->request('GET', '/document/details/' . rawurlencode($documentId));
    }

public function validateSignatures(string $documentKey): array
    {
        $response = $this->request('GET', '/document/ValidateSignatures?key=' . rawurlencode($documentKey));
        $response['isValid'] = ($response['isValid'] ?? false) === true;
        $electronic = $response['electronicSignatures'] ?? [];
        $response['hasElectronicSignature'] = is_array($electronic) && count($electronic) > 0;
        return $response;
    }

    public function downloadPackage(string $key): array
    {
        return $this->request('GET', '/document/package?key=' . urlencode($key) . '&includeOriginal=true');
    }

    private function formatSigners(array $signers): array
    {
        $items = [];

        foreach ($signers as $index => $signer) {
            $cpf = preg_replace('/\D/', '', (string) $signer['cpf']);
            $items[] = [
                'step' => (int) $signer['step'],
                'title' => 'Assinante ' . ($index + 1),
                'name' => $signer['name'],
                'email' => $signer['email'],
                'individualIdentificationCode' => $cpf,
                'identificationType' => [
                    'accessCode' => true,
                ],
                'accessCode' => substr(str_pad($cpf, 3, '0', STR_PAD_LEFT), -3),
            ];
        }

        return $items;
    }

    private function ensurePdfFileName(string $fileName): string
    {
        $trimmed = trim($fileName);
        if (strtolower(pathinfo($trimmed, PATHINFO_EXTENSION)) === 'pdf') {
            return $trimmed;
        }
        return $trimmed . '.pdf';
    }

    private function fileToBytes(string $filePath): array
    {
        $previousLimit = ini_get('memory_limit');
        ini_set('memory_limit', '512M');

        try {
            $content = file_get_contents($filePath);

            if ($content === false) {
                throw new \Exception('Nao foi possivel ler o arquivo.');
            }

            $bytes = array_values(unpack('C*', $content));
        } finally {
            ini_set('memory_limit', $previousLimit);
        }

        return $bytes;
    }

    private function request(string $method, string $endpoint, ?array $payload = null): array
    {
        if (!function_exists('curl_init')) {
            throw new \Exception('Extensao cURL do PHP nao esta habilitada.');
        }

        $curl = curl_init($this->baseUrl . $endpoint);
        $headers = [
            'token: ' . $this->token,
            'code: ' . $this->code,
            'Content-Type: application/json',
        ];

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        $caBundle = ini_get('curl.cainfo');
        if ($caBundle && file_exists($caBundle)) {
            curl_setopt($curl, CURLOPT_CAINFO, $caBundle);
        }

        if ($payload !== null) {
            $encoded = json_encode($payload);
            error_log('[Certisign] ' . $method . ' ' . $endpoint . ' => ' . $encoded);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $encoded);
        }

        $body = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($body === false) {
            throw new \Exception('Erro na chamada Certisign: ' . $error);
        }

        $data = json_decode($body, true);

        if ($status < 200 || $status >= 300) {
            $message = is_array($data) ? json_encode($data) : $body;
            throw new \Exception('Certisign retornou HTTP ' . $status . ': ' . $message);
        }

        if (!is_array($data)) {
            throw new \Exception('Certisign retornou JSON invalido.');
        }

        return $data;
    }
}
