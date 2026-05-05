<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Signature Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/style.css">
</head>
<body class="<?= e($bodyClass ?? '') ?>">
<?php if (empty($hideTopbar)): ?>
    <header class="topbar">
        <a class="brand" href="/dashboard">Signature Portal</a>
        <?php if (current_user_id()): ?>
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
    <?php if ($message = flash_message('success')): ?>
        <div class="alert success"><?= e($message) ?></div>
    <?php endif; ?>
    <?php if ($message = flash_message('error')): ?>
        <div class="alert error"><?= e($message) ?></div>
    <?php endif; ?>
