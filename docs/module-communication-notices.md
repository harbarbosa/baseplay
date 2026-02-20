# Comunicação - Avisos (Fase 1.4)

## Resumo
Módulo de comunicação para publicar avisos gerais ou segmentados por equipe/categoria, com confirmação de leitura e respostas simples. Inclui integração automática com eventos da agenda.

## Tabelas
- `notices`: avisos com prioridade, status e janela de publicação
- `notice_reads`: confirmações de leitura por usuário
- `notice_replies`: respostas simples ao aviso

## Permissões
- `notices.view`
- `notices.create`
- `notices.update`
- `notices.delete`
- `notices.publish`

Atribuição:
- Admin: todas
- Treinador: view, create, update, publish
- Auxiliar: view
- Atleta/Responsável: view

## Endpoints API

### Notices
- `GET /api/notices`
  - filtros: `team_id`, `category_id`, `priority`, `status`, `from_date`, `to_date`, `search`, `page`, `per_page`
- `POST /api/notices` (admin/treinador)
- `GET /api/notices/{id}`
- `PUT /api/notices/{id}` (admin/treinador)
- `DELETE /api/notices/{id}` (admin)

### Leitura
- `POST /api/notices/{id}/read`

### Respostas
- `GET /api/notices/{id}/replies`
- `POST /api/notices/{id}/reply`

## Regras
- Avisos podem ser gerais (`team_id`/`category_id` nulos) ou segmentados.
- Leitura não apaga histórico.
- Publicação requer `notices.publish`.
- `publish_at` deve ser <= `expires_at` quando ambos informados.

## Integração com eventos
- Ao criar ou atualizar eventos, o sistema gera aviso automático.
- Conteúdo inclui título, data/hora e link para o evento.

## Exemplos

### Criar aviso
```json
{
  "title": "Treino extra na sexta",
  "message": "Haverá treino extra na sexta-feira às 18h.",
  "priority": "important",
  "status": "published",
  "team_id": 1,
  "category_id": 3,
  "publish_at": "2026-02-10 18:00:00"
}
```

### Marcar como lido
```json
POST /api/notices/10/read
```

### Responder aviso
```json
{
  "message": "Confirmo presença."
}
```