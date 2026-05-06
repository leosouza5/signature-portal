<?php

$telaAtiva = 'documents';
$appHeader = 'search';

$statusMap = [
    'DRAFT'     => ['label' => 'Rascunho',              'class' => 'draft'],
    'SENT'      => ['label' => 'Aguardando assinatura',  'class' => 'progress'],
    'PARTIAL'   => ['label' => 'Parcialmente assinado',  'class' => 'progress'],
    'COMPLETED' => ['label' => 'Assinado',               'class' => 'signed'],
    'ERROR'     => ['label' => 'Rejeitado / Erro',       'class' => 'error'],
];

require __DIR__ . '/../layout/app-shell-start.php';
?>

<header class="document-detail-header plain-detail-header">
    <div class="document-detail-title">
        <h1><?= htmlspecialchars($document['title']) ?></h1>
        <span class="detail-badge <?= $statusMap[$document['status']]['class'] ?? 'progress' ?>">
            <?= $statusMap[$document['status']]['label'] ?? 'Aguardando' ?>
        </span>
    </div>
</header>

<?php if (!empty($document['error_message'])): ?>
    <div class="alert error"><?= htmlspecialchars($document['error_message']) ?></div>
<?php endif; ?>

<section class="detail-stats">
    <article>
        <span class="detail-stat-icon">
            <i data-lucide="calendar"></i>
        </span>
        <div>
            <span>Criado em</span>
            <strong><?= date('d/m/Y', strtotime($document['created_at'])) ?></strong>
            <em><?= date('H:i', strtotime($document['created_at'])) ?></em>
        </div>
    </article>
    <article>
        <span class="detail-stat-icon">
            <i data-lucide="refresh-cw"></i>
        </span>
        <div>
            <span>Ultima atualizacao</span>
            <strong><?= date('d/m/Y', strtotime($document['updated_at'])) ?></strong>
            <em><?= date('H:i', strtotime($document['updated_at'])) ?></em>
        </div>
    </article>
    <article>
        <span class="detail-stat-icon">
            <i data-lucide="users"></i>
        </span>
        <div>
            <span>Signatarios</span>
            <strong><?= count($signers) ?></strong>
        </div>
    </article>
</section>

<section class="detail-layout">
    <div class="detail-main-column">

        <div class="detail-info-card">
            <header>Arquivo</header>
            <div class="detail-files">
                <div class="detail-file-row">
                    <i data-lucide="file"></i>
                    <div>
                        <strong><?= htmlspecialchars($document['original_name']) ?></strong>
                    </div>
                    <?php if ($document['status'] === 'COMPLETED'): ?>
                        <form action="/documentos/<?= $document['id'] ?>/download" method="post">
                            <button type="submit" title="Baixar documento assinado">
                                <i data-lucide="download"></i>
                            </button>
                        </form>
                    <?php else: ?>
                        <span title="Disponivel somente apos assinatura" style="opacity:.3">
                            <i data-lucide="download"></i>
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <dl class="detail-definition-list" style="margin-top:1.5rem">
                <div><dt>ID</dt><dd>#<?= $document['id'] ?></dd></div>
                <div><dt>Status</dt><dd><?= $statusMap[$document['status']]['label'] ?? 'Aguardando' ?></dd></div>
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
                        <span class="detail-signer-order"><?= $signer['step'] ?></span>
                        <div>
                            <strong><?= htmlspecialchars($signer['name']) ?></strong>
                            <small><?= htmlspecialchars($signer['email']) ?></small>
                            <?php if (!empty($signer['sign_url'])): ?>
                                <a href="<?= htmlspecialchars($signer['sign_url']) ?>" target="_blank">Abrir link</a>
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
            <form action="/documentos/<?= $document['id'] ?>/atualizar-status" method="post">
                <button type="submit" class="detail-action secondary-action">
                    <i data-lucide="refresh-cw"></i>
                    Atualizar status
                </button>
            </form>
        </section>
    </aside>
</section>

<?php require __DIR__ . '/../layout/app-shell-end.php'; ?>
