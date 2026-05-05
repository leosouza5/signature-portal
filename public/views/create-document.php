<?php
$appActive = 'documents';
$appHeader = 'user';
require __DIR__ . '/app-shell-start.php';
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
                <input type="text" name="title" required placeholder="Ex.: Contrato de Prestacao de Servicos" value="<?= old('title') ?>">
            </label>

            <label class="new-document-field">
                Arquivo (PDF)
                <span class="upload-dropzone" id="upload-dropzone">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M12 18v-6"/><path d="m9 15 3-3 3 3"/></svg>
                    <strong>Arraste e solte seu arquivo PDF aqui</strong>
                    <em>ou clique para selecionar</em>
                    <input type="file" name="documents[]" required accept="application/pdf" id="documents-input">
                </span>
                <ul class="selected-files" id="selected-files"></ul>
                <p class="upload-error" id="upload-error"></p>
                <?php old_array('file_names'); ?>
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
                $oldSigners = old_array('signers');
                if (empty($oldSigners)) {
                    $oldSigners = [['name' => '', 'email' => '', 'cpf' => '']];
                }
                foreach ($oldSigners as $i => $signer):
                ?>
                <div class="signer-row">
                    <span class="signer-number"><?= $i + 1 ?></span>
                    <label><input type="text" name="signers[<?= $i ?>][name]" required placeholder="Joao da Silva" value="<?= e($signer['name'] ?? '') ?>"></label>
                    <label><input type="email" name="signers[<?= $i ?>][email]" required placeholder="joao@email.com" value="<?= e($signer['email'] ?? '') ?>"></label>
                    <label><input type="text" name="signers[<?= $i ?>][cpf]" required placeholder="00000000000" value="<?= e($signer['cpf'] ?? '') ?>"></label>
                </div>
                <?php endforeach; ?>
            </div>

            <button type="button" class="add-signer-button" id="add-signer">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                Adicionar signatario
            </button>
        </section>
    </div>

    <aside class="new-document-summary">
        <header>Resumo do envio</header>
        <div class="summary-item">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/></svg>
            <div><span>Arquivo selecionado</span><strong id="document-count">0</strong></div>
        </div>
        <div class="summary-item">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <div><span>Total de signatarios</span><strong id="signer-count">1</strong></div>
        </div>
        <div class="summary-actions">
            <a href="/documents" class="summary-draft">Cancelar</a>
            <button type="submit" id="submit-envelope">Enviar para assinatura</button>
        </div>
    </aside>
</form>

<?php require __DIR__ . '/app-shell-end.php'; ?>
