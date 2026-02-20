# Module Alerts

## Conceito

O modulo de alertas centraliza avisos automaticos do sistema (documentos, presenca, eventos e faltas documentais) para operacao diaria e futura integracao com push.

## Tabela

`system_alerts`

- `id`
- `organization_id`
- `type` (`document_expiring`, `low_attendance`, `upcoming_event`, `missing_document`)
- `entity_type` (`athlete`, `event`, `document`)
- `entity_id`
- `title`
- `description`
- `severity` (`info`, `warning`, `critical`)
- `is_read`
- `created_at`
- `read_at`

## Geracao automatica

Servico: `App\Services\AlertGeneratorService`

Regras implementadas:

1. `checkExpiringDocuments()`
- Gera alerta para documento ativo com vencimento em ate 7 dias.

2. `checkLowAttendance()`
- Gera alerta para atleta com frequencia menor que 60% nos ultimos 30 dias.

3. `checkUpcomingEvents()`
- Gera alerta para eventos agendados que ocorrerao em ate 24 horas.

4. `checkMissingRequiredDocuments()`
- Suporte opcional com tabela `category_required_documents`.
- Quando a tabela existir, gera alerta para atleta sem documento obrigatorio da categoria.

## Execucao

Comando CLI:

```bash
php spark alerts:generate
```

Sugestao de CRON diario:

```bash
0 6 * * * php /caminho/baseplay/spark alerts:generate
```

## API

- `GET /api/alerts`
- `POST /api/alerts/{id}/read`

## Web

- `GET /alerts`
- `POST /alerts/{id}/read`

## Dashboard

- Card de "Alertas pendentes" no painel.
- Badge de pendentes no menu lateral em "Alertas".

## Futuro (App)

O app pode consumir diretamente `GET /api/alerts` para feed unificado e notificacoes push.