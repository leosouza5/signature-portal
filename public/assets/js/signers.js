const lista = document.getElementById('signers');
const btn = document.getElementById('add-signer');
const total = document.getElementById('signer-count');
let idx = document.querySelectorAll('#signers .signer-row').length;

if (total) total.textContent = idx;

if (btn && lista) {
    btn.addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'signer-row';
        row.innerHTML = `
            <span class="signer-number">${idx + 1}</span>
            <label><input type="text" name="signers[${idx}][name]" required placeholder="Nome completo"></label>
            <label><input type="email" name="signers[${idx}][email]" required placeholder="email@exemplo.com"></label>
            <label><input type="text" name="signers[${idx}][cpf]" required oninput="maskCpf(this)" placeholder="000.000.000-00" minlength="14" maxlength="14" pattern="\\d{3}\\.\\d{3}\\.\\d{3}-\\d{2}"></label>
        `;
        lista.appendChild(row);
        idx++;
        if (total) total.textContent = idx;
    });
}
