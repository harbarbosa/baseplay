# Mï¿½dulo Agenda, Convocaï¿½ï¿½o e Presenï¿½a (Fase 1.3)

## Resumo
Agenda com eventos por equipe/categoria, convocaï¿½ï¿½es de atletas e marcaï¿½ï¿½o de presenï¿½a. Eventos usam ENUM para tipos para manter simplicidade e performance nesta fase.

Justificativa do ENUM:
- Tipos sï¿½o fixos e conhecidos.
- Evita tabela extra e joins para fase inicial.
- Fï¿½cil validaï¿½ï¿½o e filtro.

## Tabelas
- events
- event_participants
- attendance

## Permissï¿½es
- events.view
- events.create
- events.update
- events.delete
- attendance.manage
- invitations.manage

## Endpoints API
Eventos:
- GET /api/events
- POST /api/events
- GET /api/events/{id}
- PUT /api/events/{id}
- DELETE /api/events/{id}

Convocados:
- GET /api/events/{eventId}/participants
- POST /api/events/{eventId}/participants
- PUT /api/event-participants/{id}
- DELETE /api/event-participants/{id}
- POST /api/events/{eventId}/confirm

Presenï¿½a:
- GET /api/events/{eventId}/attendance
- POST /api/events/{eventId}/attendance
- PUT /api/attendance/{id}
- DELETE /api/attendance/{id}

## Regras de negï¿½cio
- Presenï¿½a sï¿½ ï¿½ registrada se atleta estiver convocado.
- Confirmaï¿½ï¿½o nï¿½o pode ser feita se evento estiver cancelado.
- Convocaï¿½ï¿½o nï¿½o permite duplicado por evento.
- Presenï¿½a faz upsert (atualiza se jï¿½ existir).

## Exemplos
Criar evento:
```json
{
  "team_id": 1,
  "category_id": 1,
  "type": "TRAINING",
  "title": "Treino Tï¿½cnico",
  "start_datetime": "2026-02-10 18:00:00",
  "end_datetime": "2026-02-10 19:30:00",
  "location": "Campo 1",
  "status": "scheduled"
}
```

Convocar atletas:
```json
{
  "athlete_ids": [1, 2, 3]
}
```

Confirmar convite:
```json
{
  "athlete_id": 1,
  "invitation_status": "confirmed"
}
```

Marcar presenï¿½a:
```json
{
  "athlete_id": 1,
  "status": "present",
  "notes": "Chegou no horï¿½rio"
}
```

## Observaï¿½ï¿½es
- TODO futuro: escopo por equipe para treinadores e auxiliares.
- TODO futuro: confirmaï¿½ï¿½o restrita por vï¿½nculo real de atleta/responsï¿½vel.
