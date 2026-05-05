<?php

namespace App\Controllers;

use App\Repositories\DocumentRepository;
use App\Repositories\EnvelopeRepository;
use App\Repositories\SignerRepository;
use App\Services\EnvelopeService;

class EnvelopeController
{
    private const MAX_REQUEST_BYTES = 8 * 1024 * 1024;

    public function dashboard(): void
    {
        require_login();
        $repository = new EnvelopeRepository();
        view('dashboard', ['envelopes' => $repository->allByUser(current_user_id())]);
    }

    public function documents(): void
    {
        require_login();

        $envelopeRepository = new EnvelopeRepository();
        $signerRepository = new SignerRepository();
        $envelopes = $envelopeRepository->allByUser(current_user_id());
        $filter = $_GET['filter'] ?? 'all';
        $search = trim($_GET['search'] ?? '');

        $envelopes = array_filter($envelopes, function (array $envelope) use ($filter, $search): bool {
            $matchesFilter = match ($filter) {
                'pending' => in_array($envelope['status'], ['DRAFT', 'SENT'], true),
                'signed' => $envelope['status'] === 'COMPLETED',
                'error' => $envelope['status'] === 'ERROR',
                default => true,
            };

            $matchesSearch = $search === '' || stripos($envelope['title'], $search) !== false;

            return $matchesFilter && $matchesSearch;
        });

        $envelopes = array_values($envelopes);
        $signersByEnvelope = [];

        foreach ($envelopes as $envelope) {
            $signersByEnvelope[$envelope['id']] = $signerRepository->allByEnvelope((int) $envelope['id']);
        }

        view('documents', [
            'envelopes' => $envelopes,
            'signersByEnvelope' => $signersByEnvelope,
            'filter' => $filter,
            'search' => $search,
        ]);
    }

    public function createForm(): void
    {
        require_login();
        view('create-envelope');
    }

    public function store(): void
    {
        require_login();

        try {
            $this->validateRequestSize();

            $service = new EnvelopeService();
            $envelopeId = $service->createAndSend(
                current_user_id(),
                trim($_POST['title'] ?? ''),
                $_FILES['documents'] ?? [],
                $_POST['signers'] ?? []
            );

            flash('success', 'Envelope criado e enviado para assinatura.');
            redirect('/envelopes/' . $envelopeId);
        } catch (\Exception $exception) {
            $fileNames = array_filter($_FILES['documents']['name'] ?? []);
            flash_old(['title' => $_POST['title'] ?? '', 'signers' => $_POST['signers'] ?? [], 'file_names' => array_values($fileNames)]);
            flash('error', $exception->getMessage());
            redirect('/envelopes/create');
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
        $data = $this->loadEnvelope($id);
        view('envelope-detail', $data);
    }

    public function refreshStatus(int $id): void
    {
        require_login();
        $this->loadEnvelope($id);
        flash('success', 'Status local atualizado. O PRD nao informou endpoint de consulta de status da Certisign.');
        redirect('/envelopes/' . $id);
    }

    public function download(int $id): void
    {
        require_login();
        $this->loadEnvelope($id);

        try {
            $service = new EnvelopeService();
            $package = $service->downloadFirstPackage($id);
            $bytes = array_map('intval', $package['bytes']);
            $content = pack('C*', ...$bytes);
            $fileName = $package['name'];
            $mimeType = $package['mimeType'] ?? 'application/zip';

            header('Content-Type: ' . $mimeType);
            header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
            header('Content-Length: ' . strlen($content));
            echo $content;
        } catch (\Exception $exception) {
            flash('error', $exception->getMessage());
            redirect('/envelopes/' . $id);
        }
    }

    private function loadEnvelope(int $id): array
    {
        $envelopeRepository = new EnvelopeRepository();
        $documentRepository = new DocumentRepository();
        $signerRepository = new SignerRepository();

        $envelope = $envelopeRepository->findForUser($id, current_user_id());

        if (!$envelope) {
            http_response_code(404);
            echo 'Envelope nao encontrado';
            exit;
        }

        return [
            'envelope' => $envelope,
            'documents' => $documentRepository->allByEnvelope($id),
            'signers' => $signerRepository->allByEnvelope($id),
        ];
    }
}
