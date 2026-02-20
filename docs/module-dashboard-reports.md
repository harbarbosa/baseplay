# Dashboards e Relatórios (Fase 3.1)

## Resumo
Este módulo adiciona dashboards por perfil e relatórios operacionais com exportação PDF e Excel (CSV com extensão .xlsx).

## Dashboards (API)
- `GET /api/dashboard/admin`
- `GET /api/dashboard/trainer`
- `GET /api/dashboard/assistant`
- `GET /api/dashboard/athlete`

## Relatórios (API)
- `GET /api/reports/attendance`
- `GET /api/reports/trainings`
- `GET /api/reports/matches`
- `GET /api/reports/documents`
- `GET /api/reports/athlete/{id}`

Parï¿½fÂ¢metros comuns:
- `team_id`, `category_id`, `athlete_id`, `date_from`, `date_to`, `status`, `competition_name`
- `format=json|pdf|xlsx`

## Relatórios (WEB)
- `/reports/attendance`
- `/reports/trainings`
- `/reports/matches`
- `/reports/documents`
- `/reports/athlete/{id}`

## Exportações
- PDF: gerado por ExportService (PDF simples, texto)
- Excel: CSV com extensão `.xlsx`

## Consultas principais
- Presença: `attendance` + `events`
- Treinos: `training_sessions`
- Jogos: `matches`
- Documentos: `documents` + `document_types`
- Atleta: `attendance`, `training_session_athletes`, `match_callups`

## Observações
- A exportação XLSX é CSV com extensão `.xlsx` para compatibilidade sem biblioteca externa.
- PDFs são gerados por um writer simples (texto).