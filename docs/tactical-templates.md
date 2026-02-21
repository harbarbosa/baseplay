# Modelos de Prancheta Tática

Este módulo adiciona modelos (templates) para agilizar a criação de pranchetas.

## Rotas
- `GET /tactical-boards/templates` lista modelos ativos.
- `GET /tactical-boards/create` agora permite criar em branco ou a partir de modelo.

## Permissões
- `templates.view`: visualizar modelos (treinador/auxiliar/atleta).
- `templates.manage`: administrar modelos (admin).

## Seed
Para carregar os modelos padrão:
```
php spark db:seed TemplatesSeeder
```

## Como funciona
- Ao selecionar um modelo na criação, a prancheta é criada copiando o `template_json` para `tactical_board_states`.
- O campo `template_json` usa o mesmo formato do `state_json` do editor (field/items/meta).

## Templates padrão
- Formação 4-3-3
- Formação 4-4-2
- Formação 3-5-2
- Treino de finalização
- Saída de bola
- Bola parada (escanteio ofensivo)
