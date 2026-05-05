<?php
$authTitle = "Assinaturas digitais\nsem complicacao";
$authSubtitle = "Envie documentos, acompanhe assinaturas\ne valide arquivos com facilidade.";
require __DIR__ . '/auth-shell-start.php';
?>

<section class="login-card">
    <h2>Entrar</h2>

    <form action="/login" method="post" class="login-form">
        <label for="email">E-mail</label>
        <div class="login-input">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16v16H4z"/><path d="m4 7 8 6 8-6"/></svg>
            <input id="email" type="email" name="email" required placeholder="seu@email.com" value="<?= old('email') ?>">
        </div>

        <label for="password">Senha</label>
        <div class="login-input">
            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
            <input id="password" type="password" name="password" required placeholder="Sua senha">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
        </div>

        <button type="submit" class="login-submit">Entrar</button>
    </form>

    <div class="login-divider">
        <span></span>
        <em>ou</em>
        <span></span>
    </div>

    <a class="login-create" href="/register">Criar conta</a>

</section>

<?php require __DIR__ . '/auth-shell-end.php'; ?>
