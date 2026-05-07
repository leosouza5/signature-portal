<?php

$telaAtiva = 'dashboard';
$total = count($documents);
$qtdEmProgresso = count(array_filter($documents, fn ($item) => in_array($item['status'], ['DRAFT', 'SENT'], true)));
$qtdAssinados = count(array_filter($documents, fn ($item) => $item['status'] === 'COMPLETED'));
$docsRecentes = array_slice($documents, 0, 4);

$statusMap = [
    'DRAFT'     => ['label' => 'Rascunho',      'class' => 'draft'],
    'SENT'      => ['label' => 'Em andamento',   'class' => 'progress'],
    'COMPLETED' => ['label' => 'Assinado',       'class' => 'signed'],
    'ERROR'     => ['label' => 'Erro',           'class' => 'error'],
];

require __DIR__ . '/layout/app-shell-start.php';
?>

<div class="dashboard-title-row">
    <h1>Dashboard</h1>
    <a class="new-doc-button" href="/documentos/criar">
        <i data-lucide="circle-plus"></i>Novo documento</a>
</div>

<section class="stats-grid">
    <article class="stat-card">
        <span class="stat-icon"><i data-lucide="file"></i></span>
        <div><span>Documentos</span><strong><?= $total ?></strong></div>
    </article>
    <article class="stat-card">
        <span class="stat-icon"><i data-lucide="clock"></i></span>
        <div><span>Em andamento</span><strong><?= $qtdEmProgresso ?></strong></div>
    </article>
    <article class="stat-card">
        <span class="stat-icon"><i data-lucide="circle-check"></i></span>
        <div><span>Assinados</span><strong><?= $qtdAssinados ?></strong></div>
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
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($docsRecentes)): ?>
                    <tr><td colspan="4" class="empty-row">Nenhum documento criado ainda.</td></tr>
                <?php else: ?>
                    <?php foreach ($docsRecentes as $doc): ?>
                        <tr>
                            <td><span class="doc-name"><i data-lucide="file"></i><?= htmlspecialchars($doc['title']) ?></span></td>
                            <td><span class="dashboard-badge <?= $statusMap[$doc['status']]['class'] ?? 'progress' ?>"><?= $statusMap[$doc['status']]['label'] ?? 'Aguardando' ?></span></td>
                            <td><?= date('d/m/Y H:i', strtotime($doc['created_at'])) ?></td>
                            <td><a class="view-action" href="/documentos/<?= $doc['id'] ?>" aria-label="Ver documento"><i data-lucide="eye"></i></a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <a class="recent-footer" href="/documentos">Ver todos os documentos <span>›</span></a>
</section>

<?php require __DIR__ . '/layout/app-shell-end.php'; ?>
