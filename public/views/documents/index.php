<?php

$telaAtiva = 'documents';
$filter = $filter ?? 'all';

$statusMap = [
    'DRAFT' => ['label' => 'Rascunho', 'class' => 'draft'],
    'SENT' => ['label' => 'Pendente', 'class' => 'progress'],
    'COMPLETED' => ['label' => 'Assinado', 'class' => 'signed'],
    'ERROR' => ['label' => 'Erro', 'class' => 'error'],
];

require __DIR__ . '/../layout/app-shell-start.php';
?>

<h1 class="documents-title">Documentos</h1>

<div class="documents-toolbar">
    <div class="documents-tabs" aria-label="Filtros de documentos">
        <a class="<?= $filter === 'all' ? 'active' : '' ?>" href="/documentos?filter=all">Todos</a>
        <a class="pending <?= $filter === 'pending' ? 'active' : '' ?>" href="/documentos?filter=pending">Pendentes</a>
        <a class="signed <?= $filter === 'signed' ? 'active' : '' ?>" href="/documentos?filter=signed">Assinados</a>
        <a class="error <?= $filter === 'error' ? 'active' : '' ?>" href="/documentos?filter=error">Erros</a>
    </div>

    <div class="documents-toolbar-spacer"></div>

    <a class="documents-new-button" href="/documentos/criar"><i data-lucide="plus"></i>Novo documento</a>
</div>

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
                    <tr>
                        <td colspan="5" class="empty-row">Nenhum documento criado ainda.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($documents as $document): ?>
                        <?php
                        $signers = $signersByDocument[$document['id']] ?? [];
                        $firstSigner = $signers[0] ?? null;
                        $extraSigners = count($signers) - 1;
                        ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($document['title']) ?></strong>
                            </td>
                            <td>
                                <?php if ($firstSigner): ?>
                                    <span class="signer-cell">
                                        <?php $parts = explode(' ', $firstSigner['name']); ?>
                                        <span class="signer-avatar"><?= strtoupper($parts[0][0] . ($parts[1][0] ?? '')) ?></span>
                                        <span><?= htmlspecialchars($firstSigner['name']) ?></span>
                                        <?php if ($extraSigners > 0): ?><em>+<?= $extraSigners ?></em><?php endif; ?>
                                    </span>
                                <?php else: ?>
                                    <span class="documents-muted">Sem signatarios</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="documents-badge <?= $statusMap[$document['status']]['class'] ?? 'progress' ?>">
                                    <?= $statusMap[$document['status']]['label'] ?? 'Aguardando' ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y', strtotime($document['created_at'])) ?></td>
                            <td>
                                <a class="view-action" href="/documentos/<?= $document['id'] ?>" aria-label="Abrir documento">
                                    <i data-lucide="eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/../layout/app-shell-end.php'; ?>