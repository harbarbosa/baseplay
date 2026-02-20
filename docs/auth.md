# Autenticaï¿½ï¿½o

- Login via sessï¿½o (web) e token (API).
- Recuperaï¿½ï¿½o de senha com token de redefiniï¿½ï¿½o.
- Hash de senha usando `password_hash`.

Fluxos principais:
- `GET /login`, `POST /login`
- `GET /logout`
- `GET /password/forgot`, `POST /password/forgot`
- `GET /password/reset/{token}`, `POST /password/reset`

Token API:
- `POST /api/auth/login` retorna `token`.
- Usar `Authorization: Bearer <token>` nas rotas protegidas.
