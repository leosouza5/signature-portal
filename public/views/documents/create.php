<?php

use App\Http\Session;

$telaAtiva = 'documents';
$appHeader = 'user';
$session = new Session();

require __DIR__ . '/../layout/app-shell-start.php';
?>

<h1 class="new-document-title">Novo documento</h1>

<form action="/documentos" method="post" enctype="multipart/form-data" class="new-document-form">
    <div class="new-document-main">
        <section class="new-document-card">
            <header class="new-document-section-title">
                <span>1</span>
                <h2>Informacoes do documento</h2>
            </header>

            <label class="new-document-field">
                Nome do documento
                <input type="text" name="title" required value="<?= $session->getFormValue('title') ?>">
            </label>

            <label class="new-document-field">
                Arquivo (PDF)
                <span class="upload-dropzone" id="upload-dropzone">
                    <i data-lucide="file-up"></i>
                    <strong>Arraste e solte seu arquivo PDF aqui</strong>
                    <em>ou clique para selecionar</em>
                    <input type="file" name="documents[]" required accept="application/pdf" id="documents-input">
                </span>
                <ul class="selected-files" id="selected-files"></ul>
                <p class="upload-error" id="upload-error"></p>
                <?php $session->getFormArray('file_names'); ?>
                <small>Apenas PDF. Limite: 8MB.</small>
            </label>
        </section>

        <section class="new-document-card">
            <header class="new-document-section-title">
                <span>2</span>
                <h2>Signatarios</h2>
            </header>

            <div class="signers-table" id="signers">
                <div class="signers-header">
                    <span>#</span>
                    <span>Nome</span>
                    <span>E-mail</span>
                    <span>CPF</span>
                </div>

                <?php
                $oldSigners = $session->getFormArray('signers');
                if (empty($oldSigners)) {
                    $oldSigners = [['name' => '', 'email' => '', 'cpf' => '']];
                }
                foreach ($oldSigners as $i => $signer):
                    ?>
                    <div class="signer-row">
                        <span class="signer-number"><?= $i + 1 ?></span>
                        <label>
                            <input type="text" name="signers[<?= $i ?>][name]" required
                                value="<?= htmlspecialchars($signer['name'] ?? '') ?>">
                        </label>
                        <label>
                            <input type="email" name="signers[<?= $i ?>][email]" required
                                value="<?= htmlspecialchars($signer['email'] ?? '') ?>">
                        </label>
                        <label>
                            <input type="text" name="signers[<?= $i ?>][cpf]" required
                                value="<?= htmlspecialchars($signer['cpf'] ?? '') ?>">
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="add-signer-button" id="add-signer">
                <i data-lucide="plus"></i>
                Adicionar signatario
            </button>
        </section>
    </div>

    <aside class="new-document-summary">
        <header>Resumo do envio</header>
        <div class="summary-item">
            <i data-lucide="file"></i>
            <div><span>Arquivo selecionado</span><strong id="document-count">0</strong></div>
        </div>
        <div class="summary-item">
            <i data-lucide="users"></i>
            <div><span>Total de signatarios</span><strong id="signer-count">1</strong></div>
        </div>
        <div class="summary-actions">
            <a href="/documentos" class="summary-draft">Cancelar</a>
            <button type="submit" id="submit-envelope">Enviar para assinatura</button>
        </div>
    </aside>
</form>

<?php require __DIR__ . '/../layout/app-shell-end.php'; ?>