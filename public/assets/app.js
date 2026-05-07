function maskCpf(input) {
    let value = input.value.replace(/\D/g, '').slice(0, 11);
    if (value.length > 9)      value = value.replace(/(\d{3})(\d{3})(\d{3})(\d{1,2})/, '$1.$2.$3-$4');
    else if (value.length > 6) value = value.replace(/(\d{3})(\d{3})(\d{1,3})/, '$1.$2.$3');
    else if (value.length > 3) value = value.replace(/(\d{3})(\d{1,3})/, '$1.$2');
    input.value = value;
}

let signerIndex = document.querySelectorAll('#signers .signer-row').length;
const addSignerButton = document.getElementById('add-signer');
const signersContainer = document.getElementById('signers');
const documentsInput = document.getElementById('documents-input');
const selectedFiles = document.getElementById('selected-files');
const documentCount = document.getElementById('document-count');
const uploadDropzone = document.getElementById('upload-dropzone');
const uploadError = document.getElementById('upload-error');
const submitEnvelope = document.getElementById('submit-envelope');
let selectedDocumentFiles = [];
const maxUploadBytes = 8 * 1024 * 1024;

const signerCount = document.getElementById('signer-count');
if (signerCount) {
    signerCount.textContent = signerIndex;
}

if (addSignerButton && signersContainer) {
    addSignerButton.addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'signer-row';
        row.innerHTML = `
            <span class="signer-number">${signerIndex + 1}</span>
            <label><input type="text" name="signers[${signerIndex}][name]" required placeholder="Nome completo"></label>
            <label><input type="email" name="signers[${signerIndex}][email]" required placeholder="email@exemplo.com"></label>
            <label><input type="text" name="signers[${signerIndex}][cpf]" required oninput="maskCpf(this)" placeholder="000.000.000-00" minlength="14" maxlength="14" pattern="\\d{3}\\.\\d{3}\\.\\d{3}-\\d{2}"></label>
        `;
        signersContainer.appendChild(row);
        signerIndex += 1;

        if (signerCount) {
            signerCount.textContent = signerIndex;
        }
    });
}

if (documentsInput && selectedFiles) {
    documentsInput.addEventListener('change', function () {
        selectedDocumentFiles = Array.from(documentsInput.files || []);
        renderSelectedFiles();
    });
}

function renderSelectedFiles() {
    if (!documentsInput || !selectedFiles) {
        return;
    }

    selectedFiles.innerHTML = '';

    if (documentCount) {
        documentCount.textContent = String(selectedDocumentFiles.length);
    }

    if (uploadDropzone) {
        uploadDropzone.classList.toggle('has-files', selectedDocumentFiles.length > 0);
    }

    const totalBytes = selectedDocumentFiles.reduce(function (total, file) {
        return total + file.size;
    }, 0);
    const isOverLimit = totalBytes > maxUploadBytes;

    if (uploadError) {
        uploadError.textContent = isOverLimit
            ? `O limite total do envio e 8MB. Selecionado: ${formatFileSize(totalBytes)}.`
            : '';
    }

    if (submitEnvelope) {
        submitEnvelope.disabled = isOverLimit;
    }

    selectedDocumentFiles.forEach(function (file, index) {
        const item = document.createElement('li');
        const fileInfo = document.createElement('span');
        const removeButton = document.createElement('button');

        fileInfo.textContent = `${file.name} (${formatFileSize(file.size)})`;
        removeButton.type = 'button';
        removeButton.setAttribute('aria-label', `Remover ${file.name}`);
        removeButton.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>';
        removeButton.addEventListener('click', function () {
            selectedDocumentFiles.splice(index, 1);
            syncDocumentsInput();
            renderSelectedFiles();
        });

        item.appendChild(fileInfo);
        item.appendChild(removeButton);
        selectedFiles.appendChild(item);
    });
}

function syncDocumentsInput() {
    if (!documentsInput) {
        return;
    }

    const dataTransfer = new DataTransfer();

    selectedDocumentFiles.forEach(function (file) {
        dataTransfer.items.add(file);
    });

    documentsInput.files = dataTransfer.files;
}

function formatFileSize(bytes) {
    if (bytes < 1024 * 1024) {
        return `${Math.max(1, Math.round(bytes / 1024))} KB`;
    }

    return `${(bytes / 1024 / 1024).toFixed(1)} MB`;
}
