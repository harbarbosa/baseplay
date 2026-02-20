# API

Base:
- Controllers em `app/Controllers/Api`.
- Respostas padronizadas com `status`, `message`, `data`.

Endpoints:
- `POST /api/auth/login`
- `GET /api/auth/me` (protegido)
- `GET /api/users` (protegido)

Autenticaï¿½ï¿½o:
- Header `Authorization: Bearer <token>`.
