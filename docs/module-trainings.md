# Módulo Treinos

## Validação de presença no modo campo

Rota web:
- `POST /training-sessions/{id}/athletes`

Fluxo:
1. O controller `TrainingSessions::saveAthlete()` recebe `athlete_id`, `attendance_status`, `rating`.
2. Injeta `training_session_id` a partir da rota.
3. Valida com regra `trainingSessionAthleteValid`.
4. Se válido, grava/atualiza em `training_session_athletes`.

Regra atual (`TeamCategoryRules::trainingSessionAthleteValid`):
- Aceita atualização imediata quando já existe vínculo `(training_session_id, athlete_id)`.
- Para novos vínculos, valida existência de sessão e atleta (considerando soft delete).
- Não bloqueia por troca histórica de categoria do atleta.

## Debug implementado

Logs no controller (`TrainingSessions::saveAthlete`):
- payload recebido
- erros de validação
- bypass defensivo quando sessão e atleta existem

Logs na regra (`trainingSessionAthleteValid`):
- valores de entrada
- motivo de falha (`Payload incompleto`, `Sessão inválida`, `Atleta inválido`)
- caminho de sucesso

## Consulta SQL de conferência (sessão 1)

```sql
SELECT
  ts.id AS session_id,
  ts.category_id AS session_category_id,
  a.id AS athlete_id,
  a.category_id AS athlete_category_id,
  tsa.id AS link_id
FROM training_sessions ts
LEFT JOIN training_session_athletes tsa ON tsa.training_session_id = ts.id
LEFT JOIN athletes a ON a.id = tsa.athlete_id
WHERE ts.id = 1;
```

Objetivo:
- confirmar sessão existente
- confirmar atleta existente
- confirmar vínculo na sessão
