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
import 'features/documents/presentation/screens/documents_self_screen.dart';
import 'features/pending/presentation/screens/pending_screen.dart';
import 'features/notices/presentation/screens/notices_screen.dart';
import 'presentation/screens/login_screen.dart';
import 'presentation/screens/main_shell_screen.dart';
import 'presentation/screens/team_select_screen.dart';
import 'presentation/state/providers.dart';
import 'core/context/team_context_provider.dart';
import 'core/navigation/nav_items.dart';
import 'core/router/route_guards.dart';
import 'core/auth/permissions.dart';
import 'presentation/screens/unauthorized_screen.dart';
import 'core/offline/outbox_provider.dart';

void main() {
  runApp(const ProviderScope(child: BasePlayApp()));
}

class BasePlayApp extends ConsumerWidget {
  const BasePlayApp({super.key});

  static final _messengerKey = GlobalKey<ScaffoldMessengerState>();

  static const _homeRoutes = <String>[
    '/home/agenda',
    '/home/notices',
    '/home/athletes',
    '/home/profile',
  ];

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final authState = ref.watch(authControllerProvider);
    final teamContext = ref.watch(teamContextProvider);

    final router = GoRouter(
      initialLocation: '/login',
      redirect: (context, state) async {
        final token = await ref.read(tokenStorageProvider).read();
        final loggedIn = token != null && token.isNotEmpty;
        final inLogin = state.matchedLocation == '/login';
        final inTeamSelect = state.matchedLocation == '/select-team';

        if (!loggedIn) {
          return inLogin ? null : '/login';
        }

        if (authState.user == null) {
          await ref.read(authControllerProvider.notifier).loadSessionUser();
        }

        final user = ref.read(authControllerProvider).user;
        final permissions = user?.capabilities.toSet() ??
            user?.permissions.toSet() ??
            <String>{};
        final homeRoutes = allowedHomeRoutes(permissions);

        if (user != null) {
          ref
              .read(teamContextProvider.notifier)
              .setScope('user_${user.id}');
        }
        await ref.read(teamContextProvider.notifier).ensureLoaded();
        final teams = user?.teams ?? const [];
        final activeTeamId = ref.read(teamContextProvider).activeTeamId;
        if (activeTeamId != null &&
            teams.isNotEmpty &&
            !teams.any((team) => team.id == activeTeamId)) {
          await ref.read(teamContextProvider.notifier).clear();
        }
        if (teams.length == 1 && teamContext.activeTeamId == null) {
          await ref
              .read(teamContextProvider.notifier)
              .setActiveTeam(teams.first.id);
        }

        final requiresTeamSelection =
            teams.length > 1 &&
            ref.read(teamContextProvider).activeTeamId == null;
        if (requiresTeamSelection && !inTeamSelect) {
          return '/select-team';
        }

        if (inLogin) {
          return homeRoutes.first;
        }

        if (inTeamSelect && !requiresTeamSelection) {
          return homeRoutes.first;
        }

        final location = state.matchedLocation;
        final isHomeRoute = _homeRoutes.any(location.startsWith);
        if (isHomeRoute) {
          final isAllowed = homeRoutes.any(location.startsWith);
          if (!isAllowed) {
            return homeRoutes.first;
          }
        }

        return null;
      },
      routes: [
        GoRoute(
          path: '/login',
          builder: (context, state) => const LoginScreen(),
        ),
        GoRoute(
          path: '/select-team',
          builder: (context, state) => const TeamSelectScreen(),
        ),
        GoRoute(
          path: '/unauthorized',
          builder: (context, state) => const UnauthorizedScreen(),
        ),
        ShellRoute(
          builder: (context, state, child) {
            return MainShellScreen(location: state.uri.toString(), child: child);
          },
          routes: [
            GoRoute(
              path: '/home/agenda',
              redirect: (context, state) => guardRoute(
                ref,
                state,
                requiredPermissions: const [Permissions.eventsView],
              ),
              builder: (context, state) => const AgendaScreen(),
              routes: [
                GoRoute(
                  path: 'event/:id',
                  redirect: (context, state) => guardRoute(
                    ref,
                    state,
                    requiredPermissions: const [Permissions.eventsView],
                  ),
                  builder: (context, state) {
                    final id =
                        int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
                    return EventDetailScreen(eventId: id);
                  },
                  routes: [
                    GoRoute(
                      path: 'attendance',
                      redirect: (context, state) => guardRoute(
                        ref,
                        state,
                        requiredPermissions: const [
                          Permissions.attendanceManage,
                        ],
                      ),
                      builder: (context, state) {
                        final id =
                            int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
                        return EventAttendanceFieldScreen(eventId: id);
                      },
                    ),
                  ],
                ),
              ],
            ),
            GoRoute(
              path: '/home/notices',
              redirect: (context, state) => guardRoute(
                ref,
                state,
                requiredPermissions: const [Permissions.noticesView],
              ),
              builder: (context, state) => const NoticesScreen(),
              routes: [
                GoRoute(
                  path: ':id',
                  redirect: (context, state) => guardRoute(
                    ref,
                    state,
                    requiredPermissions: const [Permissions.noticesView],
                  ),
                  builder: (context, state) {
                    final id =
                        int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
                    return NoticeDetailScreen(noticeId: id);
                  },
                ),
              ],
            ),
            GoRoute(
              path: '/home/athletes',
              redirect: (context, state) => guardRoute(
                ref,
                state,
                requiredPermissions: const [Permissions.athletesView],
              ),
              builder: (context, state) => const AthletesScreen(),
              routes: [
                GoRoute(
                  path: ':id',
                  redirect: (context, state) => guardRoute(
                    ref,
                    state,
                    requiredPermissions: const [Permissions.athletesView],
                  ),
                  builder: (context, state) {
                    final id =
                        int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
                    return AthleteDetailScreen(athleteId: id);
                  },
                ),
              ],
            ),
            GoRoute(
              path: '/home/documents',
              redirect: (context, state) => guardRoute(
                ref,
                state,
                requiredPermissions: const [
                  Permissions.documentsViewTeam,
                  Permissions.documentsViewSelf,
                ],
              ),
              builder: (context, state) {
                final user = ref.read(authUserProvider);
                final canTeam =
                    user?.hasPermission(Permissions.documentsViewTeam) ?? false;
                final canSelf =
                    user?.hasPermission(Permissions.documentsViewSelf) ?? false;

                if (canTeam) {
                  return const DocumentsOverviewScreen();
                }
                if (canSelf) {
                  return const DocumentsSelfScreen();
                }
                return const UnauthorizedScreen();
              },
              routes: [
                GoRoute(
                  path: 'athlete/:id',
                  redirect: (context, state) => guardRoute(
                    ref,
                    state,
                    requiredPermissions: const [
                      Permissions.documentsViewTeam,
                      Permissions.documentsViewSelf,
                    ],
                  ),
                  builder: (context, state) {
                    final id = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
                    return AthleteDocumentsScreen(athleteId: id);
                  },
                  routes: [
                    GoRoute(
                      path: 'upload',
                      redirect: (context, state) => guardRoute(
                        ref,
                        state,
                        requiredPermissions: const [
                          Permissions.documentsUpload,
                        ],
                      ),
                      builder: (context, state) {
                        final id =
                            int.tryParse(state.pathParameters['id'] ?? '') ?? 0;
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
                  redirect: (context, state) => guardRoute(
                    ref,
                    state,
                    requiredPermissions: const [
                      Permissions.dashboardView,
                      Permissions.documentsViewTeam,
                      Permissions.documentsViewSelf,
                      Permissions.noticesView,
                      Permissions.eventsView,
                    ],
                  ),
                  builder: (context, state) => const PendingScreen(),
                ),
              ],
            ),
          ],
        ),
      ],
    );

    ref.listen<bool>(sessionExpiredProvider, (previous, next) async {
      if (previous == true || next != true) {
        return;
      }

      await ref.read(authControllerProvider.notifier).logout();

      _messengerKey.currentState?.showSnackBar(
        const SnackBar(
          content: Text('Sessão expirada. Faça login novamente.'),
        ),
      );

      router.go('/login');
    });

    ref.listen<int>(teamContextRefreshProvider, (previous, next) async {
      await ref.read(outboxControllerProvider.notifier).processQueue();
    });

    ref.listen(authControllerProvider, (previous, next) async {
      if (previous?.user == null && next.user != null) {
        await ref.read(outboxControllerProvider.notifier).processQueue();
      }
    });

    return MaterialApp.router(
      title: 'BasePlay',
      theme: BasePlayTheme.light(),
      routerConfig: router,
      scaffoldMessengerKey: _messengerKey,
    );
  }
}
