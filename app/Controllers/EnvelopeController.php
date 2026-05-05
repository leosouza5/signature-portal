<?php

namespace App\Controllers;

use App\Repositories\DocumentRepository;
use App\Repositories\SignerRepository;
use App\Services\EnvelopeService;

class EnvelopeController
{
    private const MAX_REQUEST_BYTES = 8 * 1024 * 1024;

    public function dashboard(): void
    {
        require_login();
        $repository = new DocumentRepository();
        view('dashboard', ['documents' => $repository->allByUser(current_user_id())]);
    }

    public function documents(): void
    {
        require_login();

        $documentRepository = new DocumentRepository();
        $signerRepository = new SignerRepository();
        $documents = $documentRepository->allByUser(current_user_id());
        $filter = $_GET['filter'] ?? 'all';
        $search = trim($_GET['search'] ?? '');

        $documents = array_filter($documents, function (array $document) use ($filter, $search): bool {
            $matchesFilter = match ($filter) {
                'pending' => in_array($document['status'], ['DRAFT', 'SENT'], true),
                'signed' => $document['status'] === 'COMPLETED',
                'error' => $document['status'] === 'ERROR',
                default => true,
            };

            $matchesSearch = $search === '' || stripos($document['title'], $search) !== false;

            return $matchesFilter && $matchesSearch;
        });

        $documents = array_values($documents);
        $signersByDocument = [];

        foreach ($documents as $document) {
            $signersByDocument[$document['id']] = $signerRepository->allByDocument((int) $document['id']);
        }

        view('documents', [
            'documents' => $documents,
            'signersByDocument' => $signersByDocument,
            'filter' => $filter,
            'search' => $search,
        ]);
    }

    public function createForm(): void
    {
        require_login();
        view('create-document');
    }

    public function store(): void
    {
        require_login();

        try {
            $this->validateRequestSize();

            $service = new EnvelopeService();
            $documentId = $service->createAndSend(
                current_user_id(),
                trim($_POST['title'] ?? ''),
                $_FILES['documents'] ?? [],
                $_POST['signers'] ?? []
            );

            flash('success', 'Documento enviado para assinatura.');
            redirect('/documentos/' . $documentId);
        } catch (\Exception $exception) {
            $fileNames = array_filter($_FILES['documents']['name'] ?? []);
            flash_old(['title' => $_POST['title'] ?? '', 'signers' => $_POST['signers'] ?? [], 'file_names' => array_values($fileNames)]);
            flash('error', $exception->getMessage());
            redirect('/documentos/criar');
        }
    }

    private function validateRequestSize(): void
    {
        $contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
        $postMaxSize = $this->phpSizeToBytes(ini_get('post_max_size'));

        if ($contentLength > self::MAX_REQUEST_BYTES) {
            throw new \Exception('O limite total do envio e 8MB. Remova alguns arquivos ou envie PDFs menores.');
        }

        if ($postMaxSize > 0 && $contentLength > $postMaxSize) {
            throw new \Exception('O envio excedeu o limite configurado no PHP (' . ini_get('post_max_size') . '). Para este MVP, envie no maximo 8MB no total.');
        }
    }

    private function phpSizeToBytes(string $size): int
    {
        $size = trim($size);
        $unit = strtolower(substr($size, -1));
        $value = (int) $size;

        return match ($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => $value,
        };
    }

    public function show(int $id): void
    {
        require_login();

        try {
            $service = new EnvelopeService();
            $service->checkStatus($id);
        } catch (\Throwable) {
        }

        $data = $this->loadDocument($id);
        view('document-detail', $data);
    }

    public function refreshStatus(int $id): void
    {
        require_login();
        $this->loadDocument($id);

        try {
            $service = new EnvelopeService();
            $service->checkStatus($id);
        } catch (\Throwable) {
        }

        redirect('/documentos/' . $id);
    }

    public function download(int $id): void
    {
        require_login();

        try {
            $service = new EnvelopeService();
            $package = $service->downloadDocument($id, current_user_id());
            $this->sendPackage($package);
        } catch (\Exception $exception) {
            flash('error', $exception->getMessage());
            redirect($_SERVER['HTTP_REFERER'] ?? '/documents');
        }
    }

    private function sendPackage(array $package): void
    {
        $rawBytes = $package['bytes'];

        if (is_array($rawBytes)) {
            $content = pack('C*', ...array_map('intval', $rawBytes));
        } elseif (is_string($rawBytes)) {
            $decoded = base64_decode($rawBytes, true);
            $content = $decoded !== false ? $decoded : $rawBytes;
        } else {
            throw new \Exception('Formato de bytes do pacote nao reconhecido.');
        }

        $fileName = basename($package['name'] ?? 'documento-assinado.zip');
        $mimeType = $package['mimeType'] ?? 'application/octet-stream';

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Length: ' . strlen($content));
        echo $content;
    }

    private function loadDocument(int $id): array
    {
        $documentRepository = new DocumentRepository();
        $signerRepository = new SignerRepository();

        $document = $documentRepository->findForUser($id, current_user_id());

        if (!$document) {
            http_response_code(404);
            echo 'Documento nao encontrado';
            exit;
        }

        return [
            'document' => $document,
            'signers' => $signerRepository->allByDocument($id),
        ];
    }
}
