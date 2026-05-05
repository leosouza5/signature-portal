<?php
$appActive = 'documents';
$filter = $filter ?? 'all';
$search = $search ?? '';

function documents_filter_url(string $filter, string $search): string
{
    $params = ['filter' => $filter];

    if ($search !== '') {
        $params['search'] = $search;
    }

    return '/documents?' . http_build_query($params);
}

function documents_status_label(string $status): string
{
    return match ($status) {
        'DRAFT' => 'Rascunho',
        'SENT' => 'Pendente',
        'COMPLETED' => 'Assinado',
        'ERROR' => 'Erro',
        default => $status,
    };
}

function documents_status_class(string $status): string
{
    return match ($status) {
        'COMPLETED' => 'signed',
        'ERROR' => 'error',
        'DRAFT' => 'draft',
        default => 'pending',
    };
}

function signer_initials(array $signer): string
{
    $parts = preg_split('/\s+/', trim($signer['name'] ?? '')) ?: [];
    $first = strtoupper(substr($parts[0] ?? 'S', 0, 1));
    $second = strtoupper(substr($parts[1] ?? '', 0, 1));

    return $first . ($second ?: '');
}

require __DIR__ . '/app-shell-start.php';
?>

<h1 class="documents-title">Documentos</h1>

<form class="documents-toolbar" action="/documents" method="get">
    <input type="hidden" name="filter" value="<?= e($filter) ?>">
    <label class="documents-search">
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="search" name="search" value="<?= e($search) ?>" placeholder="Buscar documentos...">
    </label>

    <button type="submit" class="documents-filter-submit" aria-label="Buscar documentos"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg></button>

    <div class="documents-tabs" aria-label="Filtros de documentos">
        <a class="<?= $filter === 'all' ? 'active' : '' ?>" href="<?= e(documents_filter_url('all', $search)) ?>">Todos</a>
        <a class="pending <?= $filter === 'pending' ? 'active' : '' ?>" href="<?= e(documents_filter_url('pending', $search)) ?>">Pendentes</a>
        <a class="signed <?= $filter === 'signed' ? 'active' : '' ?>" href="<?= e(documents_filter_url('signed', $search)) ?>">Assinados</a>
        <a class="error <?= $filter === 'error' ? 'active' : '' ?>" href="<?= e(documents_filter_url('error', $search)) ?>">Erros</a>
    </div>

    <div class="documents-toolbar-spacer"></div>

    <a class="documents-new-button" href="/documentos/criar"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"/><path d="M5 12h14"/></svg>Novo documento</a>
</form>

<section class="documents-card">
    <div class="documents-table-wrap">
        <table class="documents-table">
            <thead>
                <tr>
                    <th>Documento</th>
                    <th>Signatarios</th>
                    <th>Status</th>
                    <th>Data</th>
                    <th>Acoes</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($documents)): ?>
                    <tr><td colspan="5" class="empty-row">Nenhum documento criado ainda.</td></tr>
                <?php else: ?>
                    <?php foreach ($documents as $document): ?>
                        <?php
                        $signers = $signersByDocument[$document['id']] ?? [];
                        $firstSigner = $signers[0] ?? null;
                        $extraSigners = max(count($signers) - 1, 0);
                        ?>
                        <tr>
                            <td>
                                <strong><?= e($document['title']) ?></strong>
                            </td>
                            <td>
                                <?php if ($firstSigner): ?>
                                    <span class="signer-cell">
                                        <span class="signer-avatar"><?= e(signer_initials($firstSigner)) ?></span>
                                        <span><?= e($firstSigner['name']) ?></span>
                                        <?php if ($extraSigners > 0): ?><em>+<?= e((string) $extraSigners) ?></em><?php endif; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="documents-muted">Sem signatarios</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="documents-badge <?= e(documents_status_class($document['status'])) ?>"><?= e(documents_status_label($document['status'])) ?></span></td>
                            <td><?= e(date('d/m/Y', strtotime($document['created_at']))) ?></td>
                            <td><a class="view-action" href="/documentos/<?= e((string) $document['id']) ?>" aria-label="Abrir documento"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/app-shell-end.php'; ?>
