<?php
$appActive = 'documents';
$appHeader = 'search';
$firstDocument = $documents[0] ?? null;
$signedCount = count(array_filter($signers, fn ($signer) => !empty($signer['sign_url']) && $envelope['status'] === 'COMPLETED'));

function detail_status_label(string $status): string
{
    return match ($status) {
        'DRAFT' => 'Rascunho',
        'SENT' => 'Em andamento',
        'COMPLETED' => 'Assinado',
        'ERROR' => 'Erro',
        default => $status,
    };
}

function detail_status_class(string $status): string
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

<header class="document-detail-header plain-detail-header">
    <div class="document-detail-title">
        <h1>Detalhes do documento</h1>
        <span class="detail-badge <?= e(detail_status_class($envelope['status'])) ?>"><?= e(detail_status_label($envelope['status'])) ?></span>
    </div>
</header>

<?php if (!empty($envelope['error_message'])): ?>
    <div class="alert error"><?= e($envelope['error_message']) ?></div>
<?php endif; ?>

<section class="detail-stats">
    <article>
        <span class="detail-stat-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 2v4"/><path d="M16 2v4"/><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M3 10h18"/></svg></span>
        <div><span>Criado em</span><strong><?= e(date('d/m/Y', strtotime($envelope['created_at']))) ?></strong><em><?= e(date('H:i', strtotime($envelope['created_at']))) ?></em></div>
    </article>
    <article>
        <span class="detail-stat-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 12a9 9 0 1 1-3-6.7"/><path d="M21 3v6h-6"/></svg></span>
        <div><span>Ultima atualizacao</span><strong><?= e(date('d/m/Y', strtotime($envelope['updated_at']))) ?></strong><em><?= e(date('H:i', strtotime($envelope['updated_at']))) ?></em></div>
    </article>
    <article>
        <span class="detail-stat-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
        <div><span>Signatarios</span><strong><?= e((string) count($signers)) ?></strong><em><?= e($signedCount > 0 ? $signedCount . ' assinados' : 'Pendentes') ?></em></div>
    </article>
</section>

<section class="detail-layout">
    <div class="detail-info-card">
        <header>Informacoes do documento</header>
        <dl class="detail-definition-list">
            <div><dt>ID do envelope</dt><dd>#<?= e((string) $envelope['id']) ?></dd></div>
            <div><dt>ID Certisign</dt><dd><?= e($firstDocument['certisign_document_id'] ?? '-') ?></dd></div>
            <div><dt>Chave Certisign</dt><dd><?= e($firstDocument['certisign_document_key'] ?? '-') ?></dd></div>
            <div><dt>Status</dt><dd><?= e(detail_status_label($envelope['status'])) ?></dd></div>
        </dl>

        <div class="detail-files">
            <?php foreach ($documents as $document): ?>
                <div class="detail-file-row">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/></svg>
                    <div><strong><?= e($document['original_name']) ?></strong><span>Upload ID: <?= e($document['certisign_upload_id'] ?: '-') ?></span></div>
                    <form action="/envelopes/<?= e((string) $envelope['id']) ?>/download" method="post">
                        <button type="submit" aria-label="Baixar documento"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg></button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>

        <section class="detail-timeline">
            <h2>Linha do tempo</h2>
            <div><span></span><p><strong>Envelope criado</strong><small><?= e($envelope['created_at']) ?></small></p></div>
            <div><span></span><p><strong>Documentos enviados para processamento</strong><small>Status atual: <?= e(detail_status_label($envelope['status'])) ?></small></p></div>
            <div><span></span><p><strong>Ultima atualizacao</strong><small><?= e($envelope['updated_at']) ?></small></p></div>
        </section>
    </div>

    <aside class="detail-side-column">
        <section class="detail-actions-card">
            <header>Acoes</header>
            <form action="/envelopes/<?= e((string) $envelope['id']) ?>/refresh-status" method="post">
                <button type="submit" class="detail-action secondary-action"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 6v6l4 2"/><circle cx="12" cy="12" r="10"/></svg>Consultar status</button>
            </form>
            <a class="detail-action secondary-action" href="#detail-signers"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>Ver links</a>
        </section>

        <section class="detail-signers-card" id="detail-signers">
            <header>Signatarios</header>
            <div class="detail-signers-head"><span>Ordem</span><span>Signatario</span><span>Status</span></div>
            <?php if (empty($signers)): ?>
                <p class="detail-empty">Nenhum signatario cadastrado.</p>
            <?php else: ?>
                <?php foreach ($signers as $signer): ?>
                    <article class="detail-signer-row">
                        <span class="detail-signer-order"><?= e((string) $signer['step']) ?></span>
                        <div><strong><?= e($signer['name']) ?></strong><small><?= e($signer['email']) ?></small><?php if (!empty($signer['sign_url'])): ?><a href="<?= e($signer['sign_url']) ?>" target="_blank">Abrir link</a><?php endif; ?></div>
                        <span class="detail-signer-status">Pendente</span>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </aside>
</section>

<?php require __DIR__ . '/app-shell-end.php'; ?>
