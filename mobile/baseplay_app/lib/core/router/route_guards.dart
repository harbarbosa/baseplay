import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../presentation/state/providers.dart';

Future<String?> guardRoute(
  WidgetRef ref,
  GoRouterState state, {
  List<String> requiredPermissions = const [],
}) async {
  final token = await ref.read(tokenStorageProvider).read();
  final loggedIn = token != null && token.isNotEmpty;

  if (!loggedIn) {
    return '/login';
  }

  if (requiredPermissions.isEmpty) {
    return null;
  }

  final user = ref.read(authUserProvider);
  if (user == null) {
    return '/login';
  }

  final hasPermission = requiredPermissions.any(user.hasPermission);
  if (!hasPermission) {
    return '/unauthorized';
  }

  return null;
}
