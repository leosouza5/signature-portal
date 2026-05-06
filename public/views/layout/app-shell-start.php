<?php
$bodyClass = 'app-page';
$hideTopbar = true;
$telaAtiva = $telaAtiva ?? 'dashboard';
require __DIR__ . '/header.php';
?>

<section class="app-shell">
    <aside class="app-sidebar">
        <a class="app-logo" href="/dashboard">
            <span class="app-logo-icon"><i data-lucide="pencil"></i></span>
            <span><strong>Signature</strong><strong class="green">Portal</strong></span>
        </a>

        <nav class="app-nav">
            <a class="<?= $telaAtiva === 'dashboard' ? 'active' : '' ?>" href="/dashboard"><i data-lucide="house"></i>Inicio</a>
            <a class="<?= $telaAtiva === 'documents' ? 'active' : '' ?>" href="/documentos"><i data-lucide="file-text"></i>Documentos</a>
        </nav>

        <div class="app-sidebar-spacer"></div>

    </aside>

    <div class="app-main">
        <header class="app-header">
            <form action="/logout" method="post">
                <button type="submit" class="app-logout" aria-label="Sair">
                    <i data-lucide="log-out"></i>
                </button>
            </form>
        </header>

        <main class="app-content">
