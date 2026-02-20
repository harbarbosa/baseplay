# Tema BasePlay (Branco + Vinho)

## Arquivo principal
- `public/assets/css/baseplay-theme.css`

## Como usar
O tema é carregado em:
- `app/Views/layouts/base.php`
- `app/Views/layouts/auth.php`

Se criar uma nova view/layout, inclua:
```html
<link rel="stylesheet" href="<= base_url('assets/css/baseplay-theme.css') >">
```

## Variáveis CSS
```css
:root {
  --primary: #7a1126;
  --primary-hover: #5c0d1d;
  --primary-soft: #f4d6db;
  --bg: #ffffff;
  --bg-soft: #f6f7f9;
  --ink: #1f2933;
  --muted: #6b7280;
}
```

## Componentes padronizados
- Botões: `.button`, `button`, `.button.secondary`
- Cards: `.card`
- Tabelas: `.table`
- Inputs: `input`, `select`, `textarea`
- Badges: `.badge`, `.badge-*`
- Alerts: `.alert`, `.alert.error`, `.alert.success`

## Observações
- Sidebar e Topbar são brancas com destaque vinho.
- Itens ativos do menu usam `.active` (já aplicado na sidebar).
- Modo campo usa `.field-mode` para botões maiores.
