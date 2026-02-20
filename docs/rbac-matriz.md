# RBAC Matrix - BasePlay

## Padrão de nomenclatura

Todas as permissões seguem `modulo.acao`.

Exemplos:
- `teams.view`, `teams.create`, `teams.update`, `teams.delete`
- `documents.view`, `documents.upload`
- `tactical_boards.export`

## Papéis fixos

- `admin`
- `treinador`
- `auxiliar`
- `atleta`
- `responsavel`

## Permissões por módulo

- Autenticação/Admin:
  - `dashboard.view`, `admin.access`, `users.manage`, `roles.manage`, `settings.manage`
- Equipes/Categorias:
  - `teams.*`, `categories.*`
- Atletas/Responsáveis:
  - `athletes.*`, `guardians.*`, `profile.view`
- Agenda/Convocação/Presença:
  - `events.*`, `callups.view`, `callups.create`, `callups.update`, `callups.delete`, `callups.confirm`, `invitations.manage` (legado), `attendance.manage`
- Avisos/Alertas:
  - `notices.*`, `notices.publish`, `alerts.view`
- Documentos:
  - `document_types.manage`, `documents.view`, `documents.upload`, `documents.update`, `documents.delete`
- Treinos:
  - `exercises.*`, `training_plans.*`, `training_sessions.*`
- Jogos:
  - `matches.*`, `match_stats.manage`, `match_lineup.manage`, `match_reports.manage`
- Quadro tático:
  - `tactical_boards.view`, `tactical_boards.create`, `tactical_boards.update`, `tactical_boards.delete`, `tactical_boards.export`, `tactical_sequences.manage`
  - aliases legados mantidos: `tactical_board.*`, `tactical_sequence.manage`
- Relatórios:
  - `reports.view`

## Matriz resumida (papel x capacidade)

- `admin`: todas as permissões.
- `treinador`: CRUD operacional do seu trabalho (eventos, treinos, jogos, prancheta, documentos, avisos), presença e relatórios.
- `auxiliar`: foco em visualização e operação de campo (presença, leitura e parte da operação sem exclusões críticas).
- `atleta`: acesso pessoal de consulta (`dashboard.view`, `profile.view`, agenda, avisos, documentos, prancheta viewer, jogos, relatórios básicos).
- `responsavel`: consulta do atleta vinculado + `callups.confirm` + `documents.upload`.

## Aplicação técnica

### Seeders

- `PermissionsSeeder`: cria/atualiza catálogo de permissões.
- `RolesSeeder`: garante os 5 papéis fixos e migra legados (`cordenador/coordenador/superadmin`) para `admin`.
- `RolePermissionsSeeder`: aplica matriz por papel (idempotente).
- `RolesPermissionsSeeder`: wrapper legado para compatibilidade.

### Web

- Rotas protegidas por `permission:<perm>` em `app/Config/Routes.php`.
- Menus e botões condicionados por `has_permission()`.
- Relatórios consolidados em `reports.view`.

### API

- Endpoints validam permissão por action via `ensurePermission()`.
- Resposta 403 padronizada:

```json
{
  "success": false,
  "message": "Acesso negado",
  "data": null,
  "errors": null
}
```

## Regras de escopo

- RBAC define **o que** pode fazer.
- Escopo define **em quais dados** pode agir.
- Reforços aplicados nesta fase:
  - `documents` na API restringe `responsavel` a documentos próprios/do atleta vinculado.
- Reforço já existente no projeto para avisos/documentos usa vínculo por equipe/categoria/guardião em services específicos.

## Como adicionar nova feature com RBAC

1. Criar permissões novas em `PermissionsSeeder` (`modulo.acao`).
2. Adicionar o mapeamento por papel em `RolePermissionsSeeder::matrix()`.
3. Proteger rotas web com `permission:<perm>`.
4. Proteger endpoints API com `ensurePermission('<perm>')`.
5. Esconder ações na UI com `has_permission('<perm>')`.
6. Se houver dados sensíveis, aplicar escopo em service/controller.
