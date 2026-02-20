# Documentos (Fase 1.5)

## Conceito
Central de documentos para atletas e equipes, com controle de vencimento, upload seguro e alertas de expiração.

## Tabelas
- `document_types`
- `documents`
- `document_alerts`

## Permissões
- `documents.view`
- `documents.upload`
- `documents.update`
- `documents.delete`
- `document_types.manage`

## Endpoints API

### Documents
- `GET /api/documents`
- `POST /api/documents`
- `GET /api/documents/{id}`
- `PUT /api/documents/{id}`
- `DELETE /api/documents/{id}`

Filtros: `athlete_id`, `team_id`, `document_type_id`, `status`, `expiring_in_days`, `page`, `per_page`

### Document Types
- `GET /api/document-types`
- `POST /api/document-types`
- `PUT /api/document-types/{id}`
- `DELETE /api/document-types/{id}`

### Alertas
- `GET /api/documents/alerts`

## Upload (exemplo)
```
POST /api/documents
Content-Type: multipart/form-data

- document_file: arquivo
- document_type_id: 1
- athlete_id: 10 (ou team_id)
- issued_at: 2026-02-08
- expires_at: 2027-02-08
- notes: texto
```

## Regras
- Obrigatório: `athlete_id` ou `team_id`
- Se `document_types.requires_expiration = 1`, `expires_at` é obrigatório
- `expires_at >= issued_at` se ambas informadas
- `expires_at` pode ser gerado automaticamente usando `default_valid_days`
- Status atualizado automaticamente para `expired` quando vencido

## Storage
Arquivos salvos em `writable/uploads/documents/AAAA/MM`.

## Alertas
Retorna vencidos e vencendo em 7/15/30 dias.