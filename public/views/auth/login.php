<?php

use App\Http\Session;

$tituloAuth = "Assinaturas digitais sem complicação";
$subTituloAuth = "Envie documentos, acompanhe assinaturas e valide arquivos com facilidade.";
$session = new Session();

require __DIR__ . '/../layout/auth-shell-start.php';
?>

<section class="login-card">
    <h2>Entrar</h2>

    <form action="/login" method="post" class="login-form">
        <label for="email">E-mail</label>
        <div class="login-input">
            <i data-lucide="mail"></i>
            <input id="email" type="email" name="email" required placeholder="seu@email.com" value="<?= $session->getFormValue('email') ?>">
        </div>

        <label for="password">Senha</label>
        <div class="login-input">
            <i data-lucide="lock"></i>
            <input id="password" type="password" name="password" required placeholder="Sua senha">
            <i data-lucide="eye"></i>
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

<?php require __DIR__ . '/../layout/auth-shell-end.php'; ?>
