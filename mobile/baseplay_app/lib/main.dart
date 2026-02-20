import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import 'core/theme/baseplay_theme.dart';
import 'features/agenda/presentation/screens/agenda_screen.dart';
import 'features/agenda/presentation/screens/event_attendance_field_screen.dart';
import 'features/agenda/presentation/screens/event_detail_screen.dart';
import 'features/athletes/presentation/screens/athlete_detail_screen.dart';
import 'features/athletes/presentation/screens/athletes_screen.dart';
import 'features/dashboard/presentation/screens/app_dashboard_screen.dart';
import 'features/documents/presentation/screens/athlete_documents_screen.dart';
import 'features/documents/presentation/screens/document_upload_screen.dart';
import 'features/documents/presentation/screens/documents_overview_screen.dart';
import 'features/notices/presentation/screens/notices_screen.dart';
import 'presentation/screens/login_screen.dart';
import 'presentation/screens/main_shell_screen.dart';
import 'presentation/state/providers.dart';

void main() {
  runApp(const ProviderScope(child: BasePlayApp()));
}

class BasePlayApp extends ConsumerWidget {
  const BasePlayApp({super.key});

  static const _homeRoutes = <String>[
    '/home/agenda',
    '/home/notices',
    '/home/athletes',
    '/home/profile',
  ];

  List<String> _allowedHomeRoutes(Set<String> permissions) {
    final has = (String permission) => permissions.contains(permission);

    final routes = <String>[];
    if (has('events.view')) {
      routes.add('/home/agenda');
    }
    if (has('notices.view')) {
      routes.add('/home/notices');
    }
    if (has('athletes.view')) {
      routes.add('/home/athletes');
    }
    if (has('dashboard.view')) {
      routes.add('/home/profile');
    }

    return routes.isEmpty ? const ['/home/profile'] : routes;
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    ref.watch(sessionExpiredProvider);
    final authState = ref.watch(authControllerProvider);

    final router = GoRouter(
      initialLocation: '/login',
      redirect: (context, state) async {
        final token = await ref.read(tokenStorageProvider).read();
        final loggedIn = token != null && token.isNotEmpty;
        final inLogin = state.matchedLocation == '/login';

        if (!loggedIn) {
          return inLogin ? null : '/login';
        }

        if (authState.user == null) {
          await ref.read(authControllerProvider.notifier).loadSessionUser();
        }

        final permissions = ref.read(authControllerProvider).user?.permissions.toSet() ?? <String>{};
        final allowedHomeRoutes = _allowedHomeRoutes(permissions);

        if (inLogin) {
          return allowedHomeRoutes.first;
        }

        final location = state.matchedLocation;
        final isHomeRoute = _homeRoutes.any(location.startsWith);
        if (isHomeRoute) {
          final isAllowed = allowedHomeRoutes.any(location.startsWith);
          if (!isAllowed) {
            return allowedHomeRoutes.first;
          }
        }

        return null;
      },
      routes: [
        GoRoute(
          path: '/login',
          builder: (context, state) => const LoginScreen(),
        ),
        ShellRoute(
          builder: (context, state, child) {
            return MainShellScreen(location: state.uri.toString(), child: child);
          },
          routes: [
            GoRoute(
              path: '/home/agenda',
              builder: (context, state) => const AgendaScreen(),
              routes: [
                GoRoute(
                  path: 'event/:id',
                  builder: (context, state) {
                    final id = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
                    return EventDetailScreen(eventId: id);
                  },
                  routes: [
                    GoRoute(
                      path: 'attendance',
                      builder: (context, state) {
                        final id = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
                        return EventAttendanceFieldScreen(eventId: id);
                      },
                    ),
                  ],
                ),
              ],
            ),
            GoRoute(
              path: '/home/notices',
              builder: (context, state) => const NoticesScreen(),
              routes: [
                GoRoute(
                  path: ':id',
                  builder: (context, state) {
                    final id = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
                    return NoticeDetailScreen(noticeId: id);
                  },
                ),
              ],
            ),
            GoRoute(
              path: '/home/athletes',
              builder: (context, state) => const AthletesScreen(),
              routes: [
                GoRoute(
                  path: ':id',
                  builder: (context, state) {
                    final id = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
                    return AthleteDetailScreen(athleteId: id);
                  },
                ),
              ],
            ),
            GoRoute(
              path: '/home/documents',
              builder: (context, state) => const DocumentsOverviewScreen(),
              routes: [
                GoRoute(
                  path: 'athlete/:id',
                  builder: (context, state) {
                    final id = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
                    return AthleteDocumentsScreen(athleteId: id);
                  },
                  routes: [
                    GoRoute(
                      path: 'upload',
                      builder: (context, state) {
                        final id = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
                        return DocumentUploadScreen(athleteId: id);
                      },
                    ),
                  ],
                ),
              ],
            ),
            GoRoute(
              path: '/home/profile',
              builder: (context, state) => const AppDashboardScreen(),
              routes: [
                GoRoute(
                  path: 'pending-center',
                  builder: (context, state) => const PendingCenterScreen(),
                ),
              ],
            ),
          ],
        ),
      ],
    );

    return MaterialApp.router(
      title: 'BasePlay',
      theme: BasePlayTheme.light(),
      routerConfig: router,
    );
  }
}
