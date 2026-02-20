# Módulo de Jogos (Fase 2.2)

## Resumo
O módulo de Jogos permite cadastrar partidas, convocar atletas, montar escalação, registrar estatísticas e salvar relatório pós-jogo. Integra com a Agenda quando o evento é do tipo MATCH.

## Tabelas
- `matches`
- `match_callups`
- `match_lineup_positions`
- `match_events`
- `match_reports`
- `match_attachments`

## Fluxo
1. Criar jogo manualmente ou a partir de um evento MATCH da Agenda.
2. Convocar atletas (bulk da categoria ou importação dos participantes do evento).
3. Definir escalação (titulares/banco, posição e número).
4. Registrar eventos do jogo (gols, assistências, cartões, substituições).
5. Salvar relatório pós-jogo e anexos.

## Permissões
- `matches.view`
- `matches.create`
- `matches.update`
- `matches.delete`
- `match_stats.manage`
- `match_lineup.manage`
- `match_reports.manage`

## Endpoints (API)

### Matches
- `GET /api/matches`
- `POST /api/matches`
- `GET /api/matches/{id}`
- `PUT /api/matches/{id}`
- `DELETE /api/matches/{id}`
- `POST /api/matches/from-event/{eventId}`

### Callups
- `GET /api/matches/{matchId}/callups`
- `POST /api/matches/{matchId}/callups`
- `PUT /api/match-callups/{id}`
- `DELETE /api/match-callups/{id}`
- `POST /api/matches/{matchId}/confirm`

### Lineup
- `GET /api/matches/{matchId}/lineup`
- `POST /api/matches/{matchId}/lineup`
- `PUT /api/match-lineup/{id}`
- `DELETE /api/match-lineup/{id}`

### Eventos do jogo
- `GET /api/matches/{matchId}/events`
- `POST /api/matches/{matchId}/events`
- `PUT /api/match-events/{id}`
- `DELETE /api/match-events/{id}`

### Relatório
- `GET /api/matches/{matchId}/report`
- `POST /api/matches/{matchId}/report`

### Anexos
- `GET /api/matches/{matchId}/attachments`
- `POST /api/matches/{matchId}/attachments`
- `DELETE /api/match-attachments/{id}`

## Exemplos de Payload

### Criar partida
```json
{
  "team_id": 1,
  "category_id": 1,
  "opponent_name": "Adversário FC",
  "competition_name": "Copa BasePlay",
  "round_name": "Rodada 1",
  "match_date": "2026-02-08",
  "start_time": "10:00",
  "location": "Estádio principal",
  "home_away": "home",
  "status": "scheduled"
}
```

### Convocar atletas (bulk)
```json
{
  "athlete_ids": [1,2,3,4]
}
```

### Registrar evento (gol)
```json
{
  "event_type": "goal",
  "athlete_id": 1,
  "minute": 12,
  "notes": "Gol de cabeça"
}
```

### Relatório
```json
{
  "summary": "Jogo equilibrado.",
  "strengths": "Pressão pós-perda.",
  "weaknesses": "Bola parada.",
  "next_actions": "Treinar escanteios.",
  "coach_notes": "Boa evolução."
}
```

## Observações
- A criação a partir de evento MATCH importa participantes do evento como convocados.
- O placar é recalculado com base nos eventos de gol do time (score_for).
- `score_against` permanece manual.