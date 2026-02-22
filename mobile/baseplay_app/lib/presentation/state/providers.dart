import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import '../../core/network/api_client.dart';
import '../../core/storage/token_storage.dart';
import '../../core/context/team_context_provider.dart';
import '../../data/repositories/auth_repository_impl.dart';
import '../../domain/repositories/auth_repository.dart';
import '../../domain/services/auth_service.dart';
import 'auth_controller.dart';
import 'auth_state.dart';

final tokenStorageProvider = Provider<TokenStorage>((ref) {
  return TokenStorage(const FlutterSecureStorage());
});

final sessionExpiredProvider = StateProvider<bool>((ref) => false);

final apiClientProvider = Provider<ApiClient>((ref) {
  final tokenStorage = ref.read(tokenStorageProvider);
  return ApiClient(
    tokenStorage,
    loadTeamId: () async => ref.read(teamContextProvider).activeTeamId,
    onUnauthorized: () {
      ref.read(sessionExpiredProvider.notifier).state = true;
    },
  );
});

final authRepositoryProvider = Provider<AuthRepository>((ref) {
  final api = ref.read(apiClientProvider);
  final tokenStorage = ref.read(tokenStorageProvider);
  return AuthRepositoryImpl(api, tokenStorage);
});

final authServiceProvider = Provider<AuthService>((ref) {
  return AuthService(ref.read(authRepositoryProvider));
});

final authControllerProvider = StateNotifierProvider<AuthController, AuthState>(
  (ref) {
    return AuthController(
      ref.read(authServiceProvider),
      onLoggedIn: () {
        ref.read(sessionExpiredProvider.notifier).state = false;
      },
      onLoggedOut: () {
        ref.read(sessionExpiredProvider.notifier).state = false;
        ref.read(teamContextProvider.notifier).setScope('global');
      },
    );
  },
);

final authUserProvider = Provider((ref) => ref.watch(authControllerProvider).user);
