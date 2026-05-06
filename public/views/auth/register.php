<?php

use App\Http\Session;

$tituloAuth = 'Crie sua conta';
$subTituloAuth = "Envie documentos, acompanhe assinaturas\ne valide arquivos com facilidade.";
$authScreenClass = 'register-screen';
$session = new Session();

require __DIR__ . '/../layout/auth-shell-start.php';
?>

<section class="login-card register-card">
    <h2>Criar conta</h2>

    <form action="/register" method="post" class="login-form">
        <label for="name">Nome completo</label>
        <div class="login-input register-input">
            <i data-lucide="user"></i>
            <input id="name" type="text" name="name" required placeholder="Seu nome completo" value="<?= $session->getFormValue('name') ?>">
        </div>

        <label for="email">E-mail</label>
        <div class="login-input register-input">
            <i data-lucide="mail"></i>
            <input id="email" type="email" name="email" required placeholder="seu@email.com" value="<?= $session->getFormValue('email') ?>">
        </div>

        <label for="password">Senha</label>
        <div class="login-input register-input">
            <i data-lucide="lock"></i>
            <input id="password" type="password" name="password" required placeholder="Sua senha">
            <i data-lucide="eye"></i>
        </div>

        <label for="password_confirmation">Confirmar senha</label>
        <div class="login-input register-input">
            <i data-lucide="lock"></i>
            <input id="password_confirmation" type="password" name="password_confirmation" required placeholder="Confirme sua senha">
            <i data-lucide="eye"></i>
        </div>

        <button type="submit" class="login-submit">Criar conta</button>
    </form>

    <a class="login-create register-login-link" href="/login">Ja tenho conta</a>
</section>

<?php require __DIR__ . '/../layout/auth-shell-end.php'; ?>
