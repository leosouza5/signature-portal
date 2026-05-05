<?php
$bodyClass = 'login-page';
$hideTopbar = true;
$authScreenClass = trim('login-screen ' . ($authScreenClass ?? ''));
require __DIR__ . '/layout-header.php';
?>

<section class="<?= e($authScreenClass) ?>">
    <div class="login-brand">
        <span class="login-brand-icon">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
        </span>
        <span><strong>Signature</strong><strong class="green">Portal</strong></span>
    </div>

    <div class="login-copy">
        <h1><?= nl2br(e($authTitle ?? '')) ?></h1>
        <p><?= nl2br(e($authSubtitle ?? '')) ?></p>
    </div>

    <div class="login-circle circle-one"></div>
    <div class="login-circle circle-two"></div>
