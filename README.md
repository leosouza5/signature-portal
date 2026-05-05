# Signature Portal

MVP em PHP puro para criar envelopes, enviar documentos para assinatura via Certisign e acompanhar links de assinatura.

## Stack

- PHP puro com POO
- SQLite
- HTML, CSS e JS puro
- Sem frameworks

## Configuracao

1. Habilite as extensoes PHP `pdo_sqlite` e `curl`.
2. Copie `.env.example` para `.env` e preencha as credenciais:

```txt
CERTISIGN_BASE_URL=https://api-sbx.portaldeassinaturas.com.br/api/v2
CERTISIGN_TOKEN=SEU_TOKEN
CERTISIGN_CODE=SEU_CODE
```

3. Rode a migracao:

```bash
php bootstrap.php
```

4. Suba o servidor local:

```bash
php -S localhost:8000 -t public
```

5. Acesse:

```txt
http://localhost:8000
```

## Fluxo

1. Cadastro ou login.
2. Criacao do envelope.
3. Upload local dos arquivos.
4. Upload dos bytes para `/document/upload`.
5. Envio batch para `/document/createBatch`.
6. Tela de detalhes com documentos, assinantes e links de assinatura.
7. Download usando `/document/package` quando a Certisign retornar a chave do documento.

## Observacao

O PRD nao informou um endpoint de consulta de status da Certisign. Por isso, o botao `Atualizar status` apenas recarrega o status salvo localmente e deixa essa decisao explicita no codigo.
