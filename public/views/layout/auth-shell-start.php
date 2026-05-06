<?php
$bodyClass = 'login-page';
$hideTopbar = true;
$authScreenClass = trim('login-screen ' . ($authScreenClass ?? ''));
require __DIR__ . '/header.php';
?>

<section class="<?= htmlspecialchars($authScreenClass) ?>">
    <div class="login-brand">
        <span class="login-brand-icon">
            <i data-lucide="pencil"></i>
        </span>
        <span><strong>Signature</strong><strong class="green">Portal</strong></span>
    </div>

    <div class="login-copy">
        <h1><?= htmlspecialchars($tituloAuth ?? '') ?></h1>
        <p><?= htmlspecialchars($subTituloAuth ?? '') ?></p>
    </div>

    <div class="login-circle circle-one"></div>
    <div class="login-circle circle-two"></div>
