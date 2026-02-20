# baseplay_app

BasePlay mobile app (Flutter).

## Setup

### Base URL (dev/prod)
Use `BASE_URL` via `--dart-define`.

Dev (web/chrome com Laragon):
```bash
flutter run -d chrome --dart-define=BASE_URL=http://baseplay.test
```

Dev (Android Emulator):
```bash
flutter run -d emulator-5554 --dart-define=BASE_URL=http://10.0.2.2:8080
```

Prod:
```bash
flutter run --dart-define=BASE_URL=https://api.seudominio.com
```

### Endpoints
Os caminhos da API ficam centralizados em:
- `lib/core/api/endpoints.dart`

Se o backend mudar rota, ajuste somente esse arquivo.

### Estrutura de pastas
- `lib/core` (config, api, network, storage, theme)
- `lib/data` (repositorios globais)
- `lib/domain` (modelos/servicos globais)
- `lib/presentation` (auth, shell, telas base)
- `lib/features/agenda` (agenda, detalhe, presenca)
- `lib/features/notices` (feed, detalhe, marcar lido)
- `lib/features/athletes` (lista, perfil, mini dashboard, historico)

### Fase 1 entregue
- Agenda: Hoje/Semana com pull-to-refresh
- Detalhe do evento com convocados
- Modo campo para presenca (upsert)
- Avisos (feed + detalhe + marcar como lido)
- Navegacao principal com BottomNavigation (Agenda/Avisos/Perfil)

### Fase 2 entregue
- Aba Atletas no menu inferior
- Lista de atletas com busca, paginacao e indicador de documentos
- Perfil do atleta com mini dashboard:
  - Presenca (30 dias)
  - Ultima atividade (treino/jogo)
  - Documentos (ativos/vencidos/a vencer)
  - Proximo evento
- Historico de presenca por atleta