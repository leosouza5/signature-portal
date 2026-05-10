const fileInput = document.getElementById('documents-input');
const fileList = document.getElementById('selected-files');
const fileCount = document.getElementById('document-count');
const dropzone = document.getElementById('upload-dropzone');
const uploadErr = document.getElementById('upload-error');
const submitBtn = document.getElementById('submit-documento');
const maxSize = 8 * 1024 * 1024;
let arquivos = [];

if (fileInput) {
    fileInput.addEventListener('change', function () {
        arquivos = Array.from(fileInput.files || []);
        renderLista();
    });
}

function renderLista() {
    if (!fileList) return;
    fileList.innerHTML = '';

    if (fileCount) fileCount.textContent = arquivos.length;
    if (dropzone) dropzone.classList.toggle('has-files', arquivos.length > 0);

    const total = arquivos.reduce((acc, f) => acc + f.size, 0);
    const passou = total > maxSize;

    if (uploadErr) {
        uploadErr.textContent = passou
            ? `O limite total do envio e 8MB. Selecionado: ${tamanho(total)}.`
            : '';
    }

    if (submitBtn) submitBtn.disabled = passou;

    arquivos.forEach(function (f, i) {
        const item = document.createElement('li');
        const info = document.createElement('span');
        const del = document.createElement('button');

        info.textContent = `${f.name} (${tamanho(f.size)})`;
        del.type = 'button';
        del.setAttribute('aria-label', `Remover ${f.name}`);
        del.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>';
        del.addEventListener('click', function () {
            arquivos.splice(i, 1);
            syncInput();
            renderLista();
        });

        item.appendChild(info);
        item.appendChild(del);
        fileList.appendChild(item);
    });
}

function syncInput() {
    const dt = new DataTransfer();
    arquivos.forEach(f => dt.items.add(f));
    fileInput.files = dt.files;
}

function tamanho(bytes) {
    if (bytes < 1024 * 1024) return `${Math.max(1, Math.round(bytes / 1024))} KB`;
    return `${(bytes / 1024 / 1024).toFixed(1)} MB`;
}
