# Quadro Tático (Web)

## Resumo
O módulo **Quadro Tático** permite criar pranchetas por equipe/categoria, posicionar peças no campo por drag & drop e salvar versões históricas do estado em JSON.

## Permissões (RBAC)
- `tactical_board.view`
- `tactical_board.create`
- `tactical_board.update`
- `tactical_board.delete`

Atribuição:
- Admin: todas
- Treinador: todas
- Auxiliar: `view`
- Atleta/Responsável: sem acesso

Seeder de permissões:
- `app/Database/Seeds/TacticalBoardPermissionsSeeder.php`

## Tabelas

### `tactical_boards`
- `id` (PK)
- `team_id` (FK teams.id)
- `category_id` (FK categories.id)
- `title`
- `description`
- `created_by` (FK users.id)
- `created_at`, `updated_at`, `deleted_at` (soft delete)

### `tactical_board_states`
- `id` (PK)
- `tactical_board_id` (FK tactical_boards.id)
- `state_json` (longtext)
- `version` (int)
- `created_by` (FK users.id)
- `created_at`

## Rotas Web
- `GET /tactical-boards` (listagem)
- `GET /tactical-boards/create` (form novo)
- `POST /tactical-boards` (criar)
- `GET /tactical-boards/{id}` (editor)
- `POST /tactical-boards/{id}/save` (salvar nova versão)
- `GET /tactical-boards/{id}/states` (histórico de versões)
- `GET /tactical-boards/{id}/load/{stateId}` (carregar versão no editor)
- `POST /tactical-boards/{id}/delete` (soft delete)

## Formato do `state_json`
```json
{
  "field": {
    "background": "soccer_field_v1",
    "aspectRatio": 1.6
  },
  "items": [
    {
      "id": "i1",
      "type": "player",
      "x": 20.5,
      "y": 35.0,
      "number": 1,
      "label": "GK",
      "color": "wine",
      "size": 44
    }
  ],
  "meta": {
    "notes": "",
    "formation": "4-3-3"
  }
}
```

## Regras do editor
- Posições em percentual (`x`,`y`) de `0..100` para responsividade.
- Drag com Pointer Events (mouse e toque).
- Toda gravação cria nova versão incremental.
- Auxiliar abre em modo leitura.

## Arquivos principais
- Controller: `app/Controllers/TacticalBoards.php`
- Services:
  - `app/Services/TacticalBoardService.php`
  - `app/Services/TacticalBoardStateService.php`
- Models:
  - `app/Models/TacticalBoardModel.php`
  - `app/Models/TacticalBoardStateModel.php`
- Views:
  - `app/Views/tactical_boards/index.php`
  - `app/Views/tactical_boards/create.php`
  - `app/Views/tactical_boards/editor.php`
  - `app/Views/tactical_boards/states.php`
- Migrações:
  - `app/Database/Migrations/2026-02-15-000042_CreateTacticalBoards.php`
  - `app/Database/Migrations/2026-02-15-000043_CreateTacticalBoardStates.php`
- Asset:
  - `public/assets/img/field-soccer.svg`

