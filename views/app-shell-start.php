<?php
$bodyClass = 'app-page';
$hideTopbar = true;
$appActive = $appActive ?? 'dashboard';
require __DIR__ . '/layout-header.php';
?>

<section class="app-shell">
    <aside class="app-sidebar">
        <a class="app-logo" href="/dashboard">
            <span class="app-logo-icon"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></span>
            <span><strong>Signature</strong><strong class="green">Portal</strong></span>
        </a>

        <nav class="app-nav">
            <a class="<?= $appActive === 'dashboard' ? 'active' : '' ?>" href="/dashboard"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 10v10h14V10"/><path d="M9 20v-6h6v6"/></svg>Inicio</a>
            <a class="<?= $appActive === 'documents' ? 'active' : '' ?>" href="/documents"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>Documentos</a>
        </nav>

        <div class="app-sidebar-spacer"></div>

    </aside>

    <div class="app-main">
        <header class="app-header">
            <form action="/logout" method="post">
                <button type="submit" class="app-logout" aria-label="Sair">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                </button>
            </form>
        </header>

        <main class="app-content">
