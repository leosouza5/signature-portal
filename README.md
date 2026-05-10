# Signature Portal

Case de estudo para apresentação de um fluxo completo de assinatura digital de documentos, integrando com a API da Certisign. O objetivo é demonstrar como uma aplicação web pode gerenciar o envio de documentos PDF, controle de assinantes e acompanhamento de status de assinatura em tempo real.

## Stack

- **PHP** — back-end em PHP puro com orientação a objetos, sem frameworks
- **PostgreSQL** — banco de dados relacional
- **HTML, CSS e JavaScript** — front-end vanilla, sem dependências externas

## Funcionalidades

- Cadastro e autenticação de usuários
- Upload de documentos PDF
- Cadastro de múltiplos assinantes por documento (nome, e-mail e CPF)
- Envio do documento para assinatura via API da Certisign
- Acompanhamento de status por assinante (pendente, parcial, concluído)
- Links individuais de assinatura para cada assinante
- Download do pacote assinado após conclusão
- Filtros de documentos por status (todos, pendentes, assinados, erro)

## Requisitos

- PHP 8.1+
- Extensões: `pdo_pgsql` e `curl`
- Docker e Docker Compose

## Como rodar

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

## Fluxo da aplicacao

1. Usuario faz cadastro ou login
2. Faz upload de um PDF e informa os dados dos assinantes
3. O documento e enviado para a Certisign via API
4. Cada assinante recebe um link individual para assinar
5. A tela de detalhes exibe o status de cada assinante em tempo real
6. Apos todos assinarem, o documento fica disponivel para download
