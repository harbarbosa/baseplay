import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../domain/services/auth_service.dart';
import 'auth_state.dart';

class AuthController extends StateNotifier<AuthState> {
  final AuthService _service;
  final void Function()? onLoggedIn;
  final void Function()? onLoggedOut;

  AuthController(this._service, {this.onLoggedIn, this.onLoggedOut})
    : super(const AuthState());

  Future<void> login(String email, String password) async {
    state = state.copyWith(isLoading: true, error: null);
    try {
      final response = await _service.login(email, password);
      onLoggedIn?.call();
      state = state.copyWith(isLoading: false, user: response.user);
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
    }
  }

  Future<void> loadSessionUser() async {
    if (state.user != null) {
      return;
    }

    try {
      final user = await _service.me();
      state = state.copyWith(user: user, error: null);
    } catch (_) {
      // Sem sessão válida ou erro transitório; o redirect global tratará 401.
    }
  }

  Future<void> logout() async {
    await _service.logout();
    state = const AuthState();
    onLoggedOut?.call();
  }
}
