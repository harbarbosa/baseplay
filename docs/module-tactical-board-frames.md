# Quadro Tático com Frames

## Conceito
- **Tactical Board**: prancheta.
- **Sequence**: jogada/rotina dentro da prancheta.
- **Frame**: estado do quadro em um passo da sequência.

## Tabelas

### `tactical_sequences`
- `id`
- `tactical_board_id`
- `title`
- `description`
- `fps` (1..10)
- `created_by`
- `created_at`, `updated_at`, `deleted_at`

### `tactical_sequence_frames`
- `id`
- `tactical_sequence_id`
- `frame_index` (0..N-1)
- `frame_json`
- `duration_ms`
- `created_at`, `updated_at`

## Permissão
- `tactical_sequence.manage`

Admin e Treinador: manage  
Auxiliar: somente view do quadro

## Endpoints API

### Sequences
- `GET /api/tactical-boards/{boardId}/sequences`
- `POST /api/tactical-boards/{boardId}/sequences`
- `GET /api/tactical-sequences/{sequenceId}`
- `PUT /api/tactical-sequences/{sequenceId}`
- `DELETE /api/tactical-sequences/{sequenceId}`

### Frames
- `GET /api/tactical-sequences/{sequenceId}/frames`
- `POST /api/tactical-sequences/{sequenceId}/frames`
- `PUT /api/tactical-sequence-frames/{frameId}`
- `DELETE /api/tactical-sequence-frames/{frameId}`
- `POST /api/tactical-sequences/{sequenceId}/save-all`

## Formato `frame_json`
Mesmo formato do state atual:

```json
{
  "field": {"background":"soccer_field_v1","aspectRatio":1.6},
  "items": [
    {"id":"i1","type":"player","x":20.5,"y":35.0,"number":1,"label":"GK","color":"wine","size":34}
  ],
  "meta":{"notes":"","formation":"4-3-3"}
}
```

## Regras
- `frames` precisa ter ao menos 1 item.
- `save-all` regrava todos os frames em transação.
- `frame_index` é reindexado para `0..N-1`.
- payload de `frames` limitado a ~1MB.
- playback usa transição suave de posição no editor.

## Web (editor)
No editor da prancheta:
- selecionar sequência
- criar/renomear/excluir sequência
- adicionar/duplicar/excluir frame
- prev/next
- play/pause
- salvar sequência

