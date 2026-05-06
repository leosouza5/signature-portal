<?php

namespace App\Controllers;

use App\Http\Auth;
use App\Http\Response;
use App\Http\Session;
use App\Repositories\DocumentRepository;
use App\Repositories\SignerRepository;
use App\Services\DocumentService;

class DocumentController
{
    private const MAX_REQUEST_BYTES = 8 * 1024 * 1024;

    private Session $session;
    private Auth $auth;

    public function __construct()
    {
        $this->session = new Session();
        $this->auth = new Auth($this->session);
    }

    public function dashboard(): void
    {
        $repository = new DocumentRepository();
        Response::view('dashboard', ['documents' => $repository->getAllByUser($this->auth->userId())]);
    }

    public function documents(): void
    {
        $this->auth->requireLogin();

        $documentRepository = new DocumentRepository();
        $signerRepository = new SignerRepository();

        $filter = $_GET['filter'] ?? 'all';
        $documents = match ($filter) {
            'pending' => $documentRepository->getPendingByUser($this->auth->userId()),
            'signed' => $documentRepository->getSignedByUser($this->auth->userId()),
            'error' => $documentRepository->getErrorByUser($this->auth->userId()),
            default => $documentRepository->getAllByUser($this->auth->userId()),
        };

        $signersByDocument = [];
        foreach ($documents as $document) {
            $signersByDocument[$document['id']] = $signerRepository->getAllByDocument((int) $document['id']);
        }

        Response::view('documents/index', [
            'documents' => $documents,
            'signersByDocument' => $signersByDocument,
            'filter' => $filter,
        ]);
    }

    public function createForm(): void
    {
        Response::view('documents/create');
    }

    public function store(): void
    {
        $this->auth->requireLogin();

        try {
            $this->validaTamanho();

            $service = new DocumentService();
            $documentId = $service->createAndSend(
                $this->auth->userId(),
                trim($_POST['title'] ?? ''),
                $_FILES['documents'] ?? [],
                $_POST['signers'] ?? []
            );

            $this->session->setMessage('success', 'Documento enviado para assinatura.');
            Response::redirect('/documentos/' . $documentId);
        } catch (\Exception $e) {
            $fileNames = array_filter($_FILES['documents']['name'] ?? []);
            $this->session->saveForm(['title' => $_POST['title'] ?? '', 'signers' => $_POST['signers'] ?? [], 'file_names' => array_values($fileNames)]);
            $this->session->setMessage('error', $e->getMessage());
            Response::redirect('/documentos/criar');
        }
    }

    private function validaTamanho(): void
    {
        $contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);

        if ($contentLength > self::MAX_REQUEST_BYTES) {
            throw new \Exception('O arquivo é muito grande. Envie PDFs de até 8MB.');
        }
    }

    public function show(int $id): void
    {
        $this->auth->requireLogin();

        try {
            $service = new DocumentService();
            $service->checkStatus($id);
        } catch (\Throwable) {
        }

        $data = $this->loadDocument($id);
        Response::view('documents/detail', $data);
    }

    public function refreshStatus(int $id): void
    {
        $this->loadDocument($id);

        try {
            $service = new DocumentService();
            $service->checkStatus($id);
        } catch (\Throwable) {
        }

        Response::redirect('/documentos/' . $id);
    }

    public function download(int $id): void
    {
        $this->auth->requireLogin();

        try {
            $service = new DocumentService();
            $package = $service->downloadDocument($id, $this->auth->userId());
            $this->sendPackage($package);
        } catch (\Exception $e) {
            $this->session->setMessage('error', $e->getMessage());
            Response::redirect($_SERVER['HTTP_REFERER'] ?? '/documentos');
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

        $document = $documentRepository->getByUser($id, $this->auth->userId());

        if (!$document) {
            Response::abort(404, 'Documento nao encontrado');
        }

        return [
            'document' => $document,
            'signers' => $signerRepository->getAllByDocument($id),
        ];
    }
}
