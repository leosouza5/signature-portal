<?php
$appActive = 'dashboard';
$total = count($envelopes);
$inProgress = count(array_filter($envelopes, fn ($item) => in_array($item['status'], ['DRAFT', 'SENT'], true)));
$signed = count(array_filter($envelopes, fn ($item) => $item['status'] === 'COMPLETED'));
$recent = array_slice($envelopes, 0, 4);

function dashboard_status_label(string $status): string
{
    return match ($status) {
        'DRAFT' => 'Rascunho',
        'SENT' => 'Em andamento',
        'COMPLETED' => 'Assinado',
        'ERROR' => 'Erro',
        default => $status,
    };
}

function dashboard_status_class(string $status): string
{
    return match ($status) {
        'COMPLETED' => 'signed',
        'ERROR' => 'error',
        'DRAFT' => 'draft',
        default => 'progress',
    };
}

require __DIR__ . '/app-shell-start.php';
?>

<div class="dashboard-title-row">
    <h1>Dashboard</h1>
    <a class="new-doc-button" href="/envelopes/create"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 8v8"/><path d="M8 12h8"/></svg>Novo documento</a>
</div>

<section class="stats-grid">
    <article class="stat-card">
        <span class="stat-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/></svg></span>
        <div><span>Documentos</span><strong><?= e((string) $total) ?></strong></div>
    </article>
    <article class="stat-card">
        <span class="stat-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 6v6l4 2"/><circle cx="12" cy="12" r="10"/></svg></span>
        <div><span>Em andamento</span><strong><?= e((string) $inProgress) ?></strong></div>
    </article>
    <article class="stat-card">
        <span class="stat-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="10"/></svg></span>
        <div><span>Assinados</span><strong><?= e((string) $signed) ?></strong></div>
    </article>
</section>

<section class="recent-card">
    <header>Documentos recentes</header>
    <div class="recent-table-wrap">
        <table class="recent-table">
            <thead>
                <tr>
                    <th>Documento</th>
                    <th>Status</th>
                    <th>Criado em</th>
                    <th>Acao</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recent)): ?>
                    <tr><td colspan="4" class="empty-row">Nenhum documento criado ainda.</td></tr>
                <?php else: ?>
                    <?php foreach ($recent as $envelope): ?>
                        <tr>
                            <td><span class="doc-name"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/></svg><?= e($envelope['title']) ?></span></td>
                            <td><span class="dashboard-badge <?= e(dashboard_status_class($envelope['status'])) ?>"><?= e(dashboard_status_label($envelope['status'])) ?></span></td>
                            <td><?= e($envelope['created_at']) ?></td>
                            <td><a class="view-action" href="/envelopes/<?= e((string) $envelope['id']) ?>" aria-label="Ver envelope"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg></a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <a class="recent-footer" href="/documents">Ver todos os documentos <span>›</span></a>
</section>

<?php require __DIR__ . '/app-shell-end.php'; ?>
