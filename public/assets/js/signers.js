const lista = document.getElementById('signers');
const btn = document.getElementById('add-signer');
const total = document.getElementById('signer-count');

function renumberSigners() {
    const rows = lista.querySelectorAll('.signer-row');
    rows.forEach(function (row, index) {
        row.querySelector('.signer-number').textContent = index + 1;
        row.querySelector('input[type="text"][name*="[name]"]').name = `signers[${index}][name]`;
        row.querySelector('input[type="email"]').name = `signers[${index}][email]`;
        row.querySelector('input[type="text"][name*="[cpf]"]').name = `signers[${index}][cpf]`;
    });
    if (total) total.textContent = rows.length;
}

function removeSigner(btn) {
    const rows = lista.querySelectorAll('.signer-row');
    if (rows.length <= 1) return;
    btn.closest('.signer-row').remove();
    renumberSigners();
}

if (btn && lista) {
    renumberSigners();

    btn.addEventListener('click', function () {
        const rows = lista.querySelectorAll('.signer-row');
        const index = rows.length;
        const row = document.createElement('div');
        row.className = 'signer-row';
        row.innerHTML = `
            <span class="signer-number">${index + 1}</span>
            <label><input type="text" name="signers[${index}][name]" required ></label>
            <label><input type="email" name="signers[${index}][email]" required ></label>
            <label><input type="text" name="signers[${index}][cpf]" required oninput="maskCpf(this)" placeholder="000.000.000-00" minlength="14" maxlength="14" pattern="\\d{3}\\.\\d{3}\\.\\d{3}-\\d{2}"></label>
            <button type="button" class="remove-signer" onclick="removeSigner(this)">✕</button>
        `;
        lista.appendChild(row);
        if (total) total.textContent = lista.querySelectorAll('.signer-row').length;
    });
}
