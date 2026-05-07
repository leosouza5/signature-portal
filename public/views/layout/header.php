<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Signature Portal</title>
    <link rel="icon" type="image/svg+xml" href="/assets/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/base.css">
    <link rel="stylesheet" href="/assets/css/layout.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/documents.css">
    <link rel="stylesheet" href="/assets/css/auth.css">
</head>
<body class="<?= htmlspecialchars($bodyClass ?? '') ?>">
<?php
use App\Http\Session;
$session = new Session();
?>
<?php if (empty($hideTopbar)): ?>
    <header class="topbar">
        <a class="brand" href="/dashboard">Signature Portal</a>
        <?php if ($session->getUserId()): ?>
            <nav>
                <a href="/dashboard">Dashboard</a>
                <a href="/envelopes/create">Novo envio</a>
                <form action="/logout" method="post">
                    <button type="submit" class="link-button">Sair</button>
                </form>
            </nav>
        <?php endif; ?>
    </header>
<?php endif; ?>
<main class="container">
    <?php if ($message = $session->getMessage('success')): ?>
        <div class="alert success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <?php if ($message = $session->getMessage('error')): ?>
        <div class="alert error"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
