# Mï¿½dulo Atletas e Responsï¿½veis (Fase 1.2)

## Resumo
Cadastro e gestï¿½o de atletas e responsï¿½veis, com vï¿½nculo N:N entre atleta e responsï¿½vel. Atletas pertencem a uma categoria ativa. Admin gerencia tudo; treinadores e auxiliares apenas visualizam.

## Tabelas e relacionamentos
- athletes (1 atleta -> 1 categoria)
- guardians
- athlete_guardians (N:N)

## Permissï¿½es
- athletes.view
- athletes.create
- athletes.update
- athletes.delete
- guardians.view
- guardians.create
- guardians.update
- guardians.delete

## Endpoints API
Atletas:
- GET /api/athletes
- POST /api/athletes
- GET /api/athletes/{id}
- PUT /api/athletes/{id}
- DELETE /api/athletes/{id}

Responsï¿½veis:
- GET /api/guardians
- POST /api/guardians
- GET /api/guardians/{id}
- PUT /api/guardians/{id}
- DELETE /api/guardians/{id}

Vï¿½nculos:
- GET /api/athletes/{athleteId}/guardians
- POST /api/athletes/{athleteId}/guardians
- PUT /api/athlete-guardians/{id}
- DELETE /api/athlete-guardians/{id}

## Regras do responsï¿½vel primï¿½rio
- Quando `is_primary = 1`, outros vï¿½nculos do atleta sï¿½o desmarcados automaticamente.
- Nï¿½o permite vï¿½nculo duplicado (athlete_id + guardian_id).

## Exemplos
Criar atleta:
```json
{
  "category_id": 1,
  "first_name": "Joï¿½o",
  "last_name": "Silva",
  "birth_date": "2012-05-10",
  "position": "Goleiro",
  "dominant_foot": "right",
  "status": "active"
}
```

Criar responsï¿½vel:
```json
{
  "full_name": "Maria Silva",
  "phone": "11 99999-0001",
  "email": "maria@exemplo.com",
  "relation_type": "Mï¿½e",
  "status": "active"
}
```

Vincular responsï¿½vel:
```json
{
  "guardian_id": 1,
  "is_primary": 1,
  "notes": "Contato principal"
}
```

Criar responsï¿½vel inline e vincular:
```json
{
  "full_name": "Carlos Silva",
  "phone": "11 99999-0002",
  "email": "carlos@exemplo.com",
  "relation_type": "Pai",
  "is_primary": 0
}
```

## Observaï¿½ï¿½es
- TODO futuro: limitar listagens por vï¿½nculo de treinador/equipe.
- TODO futuro: `user_links` para login de atleta/responsï¿½vel.
