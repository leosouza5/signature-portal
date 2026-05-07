import { maskCpf } from './cpf.js';
import { initSigners } from './signers.js';
import { initUpload } from './upload.js';

window.maskCpf = maskCpf;

initSigners();
initUpload();
