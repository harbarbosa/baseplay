# Mini Dashboard do Atleta - Última Atividade

## Resumo
Foi adicionado no Perfil do Atleta um card de `Última atividade` com dois blocos:
- Último treino (data, título e "há X dias")
- Último jogo (data, título e "há X dias")

Quando não houver histórico, o card mostra `Sem registros`.

## Regras de prioridade
### Último treino
1. `training_sessions` + `training_session_athletes` (somente presença: `present`, `late`, `justified`)
2. Fallback: `attendance` + `events` com `events.type = TRAINING`

### Último jogo
1. `matches` com vínculo de participação claro (lineup, eventos de jogo ou callup confirmado)
2. Fallback: `events` com `type = MATCH` + (`attendance` de presença ou `event_participants` confirmado)

## Service
Arquivo: `app/Services/AthleteSummaryService.php`

Método:
- `getLastActivity(int $athleteId): array`

Retorno:
```json
{
  "last_training": {
    "date": "2026-02-01",
    "title": "Treino Técnico - Finalização",
    "source": "training_sessions",
    "days_ago": 13
  },
  "last_match": {
    "date": "2026-01-28",
    "title": "vs Escola X (Campeonato Y)",
    "source": "matches",
    "days_ago": 17
  }
}
```

## Endpoint
`GET /api/athletes/{id}/summary/last-activity`

Resposta:
```json
{
  "success": true,
  "message": "OK",
  "data": {
    "last_training": null,
    "last_match": null
  },
  "errors": null
}
```

## Índices adicionados
Migration: `app/Database/Migrations/2026-02-15-000040_AddLastActivityIndexes.php`

Índices:
- `training_session_athletes (athlete_id, training_session_id)`
- `training_sessions (session_date, category_id)`
- `attendance (athlete_id, event_id)`
- `events (type, start_datetime)`
- `match_callups (athlete_id, match_id)`

## Roteiro de teste manual
1. Abrir `Perfil do atleta` com registros de treino/jogo.
2. Validar se o card mostra:
- Data correta do último treino.
- Data correta do último jogo.
- Campo `há X dias` coerente.
3. Testar atleta sem histórico:
- Deve mostrar `Sem registros` nos dois blocos.
4. Testar endpoint:
- `GET /api/athletes/{id}/summary/last-activity` com token válido.
- Validar `success`, `message`, `data`, `errors`.
