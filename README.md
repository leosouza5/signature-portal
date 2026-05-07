# Signature Portal

MVP em PHP puro para criar envelopes, enviar documentos para assinatura via Certisign e acompanhar o status de cada assinante.

## Stack

- PHP puro com POO
- PostgreSQL + Docker
- HTML, CSS e JS puro (sem frameworks)

## Requisitos

- PHP 8.1+
- Extensões: `pdo_pgsql` e `curl`
- Docker e Docker Compose

## Configuracao

1. Copie `.env.example` para `.env` e preencha as credenciais:

```env
DB_HOST=localhost
DB_PORT=5432
DB_NAME=signature_portal
DB_USER=postgres
DB_PASSWORD=secret

CERTISIGN_BASE_URL=https://api-sbx.portaldeassinaturas.com.br/api/v2
CERTISIGN_TOKEN=SEU_TOKEN
CERTISIGN_CODE=SEU_CODE
```

2. Suba o banco:

```bash
docker compose up -d
```

3. Rode as migrations:

```bash
php bootstrap.php
```

4. Suba o servidor:

```bash
php -S localhost:8000 -t public
```

5. Acesse `http://localhost:8000`

## Fluxo

1. Cadastro ou login
2. Criacao do envelope com upload do PDF e informacoes dos assinantes
3. Upload dos bytes para `/document/upload`
4. Envio para `/document/createBatch`
5. Tela de detalhes com status, assinantes e links de assinatura
6. Botao "Atualizar status" consulta `/document/ValidateSignatures` e atualiza cada assinante por CPF ou e-mail
7. Download via `/document/package` apos todos assinarem
