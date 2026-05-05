<?php
$authTitle = 'Crie sua conta';
$authSubtitle = "Envie documentos, acompanhe assinaturas\ne valide arquivos com facilidade.";
$authScreenClass = 'register-screen';
require __DIR__ . '/auth-shell-start.php';
?>

<section class="login-card register-card">
    <h2>Criar conta</h2>

    <form action="/register" method="post" class="login-form">
        <label for="name">Nome completo</label>
        <div class="login-input register-input">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>
            <input id="name" type="text" name="name" required placeholder="Seu nome completo" value="<?= old('name') ?>">
        </div>

        <label for="email">E-mail</label>
        <div class="login-input register-input">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16v16H4z"/><path d="m4 7 8 6 8-6"/></svg>
            <input id="email" type="email" name="email" required placeholder="seu@email.com" value="<?= old('email') ?>">
        </div>

        <label for="password">Senha</label>
        <div class="login-input register-input">
            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
            <input id="password" type="password" name="password" required placeholder="Sua senha">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
        </div>

        <label for="password_confirmation">Confirmar senha</label>
        <div class="login-input register-input">
            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
            <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="Confirme sua senha">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
        </div>

        <button type="submit" class="login-submit">Criar conta</button>
    </form>

    <a class="login-create register-login-link" href="/login">Ja tenho conta</a>
</section>

<?php require __DIR__ . '/auth-shell-end.php'; ?>
