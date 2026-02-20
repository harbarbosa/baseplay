# Modulo Equipes e Categorias (Fase 1.1)

## Resumo
CRUD de Equipes e Categorias com soft delete, filtros, paginacao e Permissão por RBAC. Admin gerencia tudo; demais perfis apenas visualizam.

## Tabelas
- teams
- categories
- user_team_links (futuro)

## Permissões
- teams.view
- teams.create
- teams.update
- teams.delete
- categories.view
- categories.create
- categories.update
- categories.delete

## Endpoints API
Teams:
- GET /api/teams
- POST /api/teams
- GET /api/teams/{id}
- PUT /api/teams/{id}
- DELETE /api/teams/{id}

Categories:
- GET /api/teams/{teamId}/categories
- POST /api/teams/{teamId}/categories
- GET /api/categories/{id}
- PUT /api/categories/{id}
- DELETE /api/categories/{id}

## Exemplos
Criar Equipe:
```json
{
  "name": "BasePlay Academy",
  "short_name": "BasePlay",
  "status": "active"
}
```

Criar Categoria:
```json
{
  "name": "Sub-11",
  "year_from": 2015,
  "year_to": 2016,
  "gender": "mixed",
  "training_days": "Seg, Qua, Sex",
  "status": "active"
}
```
