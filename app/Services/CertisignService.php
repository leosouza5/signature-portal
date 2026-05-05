<?php

namespace App\Services;

class CertisignService
{
    private string $baseUrl;
    private string $token;
    private string $code;

    public function __construct()
    {
        $config = require __DIR__ . '/../../config.php';
        $this->baseUrl = rtrim($config['certisign_base_url'], '/');
        $this->token = $config['certisign_token'];
        $this->code = $config['certisign_code'];
    }

    public function uploadDocument(string $filePath, string $fileName): string
    {
        $payload = [
            'fileName' => $fileName,
            'bytes' => $this->fileToBytes($filePath),
        ];

        $response = $this->request('POST', '/document/upload', $payload);

        if (empty($response['uploadId'])) {
            throw new \Exception('Upload enviado, mas uploadId nao foi retornado pela Certisign.');
        }

        return $response['uploadId'];
    }

    public function createBatch(array $documents, array $signers): array
    {
        $payload = ['documents' => []];

        foreach ($documents as $document) {
            $payload['documents'][] = [
                'typeId' => 1,
                'document' => [
                    'name' => $document['original_name'],
                    'upload' => [
                        'id' => $document['certisign_upload_id'],
                        'name' => $document['original_name'],
                    ],
                ],
                'electronicSigners' => $this->formatSigners($signers),
            ];
        }

        return $this->request('POST', '/document/createBatch', $payload);
    }

    public function downloadPackage(string $key): array
    {
        return $this->request('GET', '/document/package?key=' . urlencode($key) . '&includeOriginal=true');
    }

    private function formatSigners(array $signers): array
    {
        $items = [];

        foreach ($signers as $signer) {
            $items[] = [
                'step' => (int) $signer['step'],
                'name' => $signer['name'],
                'email' => $signer['email'],
                'individualIdentificationCode' => $signer['cpf'],
                'inPerson' => false,
            ];
        }

        return $items;
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

        $caBundle = ini_get('curl.cainfo');
        if ($caBundle && file_exists($caBundle)) {
            curl_setopt($curl, CURLOPT_CAINFO, $caBundle);
        }

        if ($payload !== null) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
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
