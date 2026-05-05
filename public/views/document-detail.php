<?php
$appActive = 'documents';
$appHeader = 'search';

function env_status_label(string $status): string
{
    return match ($status) {
        'COMPLETED' => 'Assinado',
        'PARTIAL'   => 'Parcialmente assinado',
        'ERROR'     => 'Rejeitado / Erro',
        'DRAFT'     => 'Rascunho',
        default     => 'Aguardando assinatura',
    };
}

function env_status_class(string $status): string
{
    return match ($status) {
        'COMPLETED' => 'signed',
        'PARTIAL'   => 'progress',
        'ERROR'     => 'error',
        'DRAFT'     => 'draft',
        default     => 'progress',
    };
}

require __DIR__ . '/app-shell-start.php';
?>

<header class="document-detail-header plain-detail-header">
    <div class="document-detail-title">
        <h1><?= e($document['title']) ?></h1>
        <span class="detail-badge <?= e(env_status_class($document['status'])) ?>">
            <?= e(env_status_label($document['status'])) ?>
        </span>
    </div>
</header>

<?php if (!empty($document['error_message'])): ?>
    <div class="alert error"><?= e($document['error_message']) ?></div>
<?php endif; ?>

<section class="detail-stats">
    <article>
        <span class="detail-stat-icon">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 2v4"/><path d="M16 2v4"/><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M3 10h18"/></svg>
        </span>
        <div>
            <span>Criado em</span>
            <strong><?= e(date('d/m/Y', strtotime($document['created_at']))) ?></strong>
            <em><?= e(date('H:i', strtotime($document['created_at']))) ?></em>
        </div>
    </article>
    <article>
        <span class="detail-stat-icon">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 12a9 9 0 1 1-3-6.7"/><path d="M21 3v6h-6"/></svg>
        </span>
        <div>
            <span>Ultima atualizacao</span>
            <strong><?= e(date('d/m/Y', strtotime($document['updated_at']))) ?></strong>
            <em><?= e(date('H:i', strtotime($document['updated_at']))) ?></em>
        </div>
    </article>
    <article>
        <span class="detail-stat-icon">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </span>
        <div>
            <span>Signatarios</span>
            <strong><?= e((string) count($signers)) ?></strong>
        </div>
    </article>
</section>

<section class="detail-layout">
    <div class="detail-main-column">

        <div class="detail-info-card">
            <header>Arquivo</header>
            <div class="detail-files">
                <div class="detail-file-row">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/></svg>
                    <div>
                        <strong><?= e($document['original_name']) ?></strong>
                    </div>
                    <?php if ($document['status'] === 'COMPLETED'): ?>
                        <form action="/documents/<?= e((string) $document['id']) ?>/download" method="post">
                            <button type="submit" title="Baixar documento assinado">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg>
                            </button>
                        </form>
                    <?php else: ?>
                        <span title="Disponivel somente apos assinatura" style="opacity:.3">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M7 10l5 5 5-5"/><path d="M12 15V3"/></svg>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <dl class="detail-definition-list" style="margin-top:1.5rem">
                <div><dt>ID</dt><dd>#<?= e((string) $document['id']) ?></dd></div>
                <div><dt>Status</dt><dd><?= e(env_status_label($document['status'])) ?></dd></div>
            </dl>
        </div>

        <section class="detail-signers-card" id="detail-signers">
            <header>Signatarios</header>
            <div class="detail-signers-head"><span>Ordem</span><span>Signatario</span></div>
            <?php if (empty($signers)): ?>
                <p class="detail-empty">Nenhum signatario cadastrado.</p>
            <?php else: ?>
                <?php foreach ($signers as $signer): ?>
                    <article class="detail-signer-row">
                        <span class="detail-signer-order"><?= e((string) $signer['step']) ?></span>
                        <div>
                            <strong><?= e($signer['name']) ?></strong>
                            <small><?= e($signer['email']) ?></small>
                            <?php if (!empty($signer['sign_url'])): ?>
                                <a href="<?= e($signer['sign_url']) ?>" target="_blank">Abrir link</a>
                            <?php endif; ?>
                        </div>
                        <?php if (($signer['status'] ?? '') === 'SIGNED'): ?>
                            <span class="detail-badge signed" title="Ja assinou">&#10003; Assinou</span>
                        <?php else: ?>
                            <span class="detail-badge progress" title="Aguardando assinatura">Pendente</span>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

    </div>

    <aside class="detail-side-column">
        <section class="detail-actions-card">
            <header>Acoes</header>
            <form action="/documentos/<?= e((string) $document['id']) ?>/atualizar-status" method="post">
                <button type="submit" class="detail-action secondary-action">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 12a9 9 0 1 1-3-6.7"/><path d="M21 3v6h-6"/></svg>
                    Atualizar status
                </button>
            </form>
        </section>
    </aside>
</section>

<?php require __DIR__ . '/app-shell-end.php'; ?>
