import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../storage/prefs_keys.dart';
import 'team_context.dart';

final teamContextRefreshProvider = StateProvider<int>((ref) => 0);

final teamContextProvider =
    StateNotifierProvider<TeamContextController, TeamContextState>((ref) {
  final controller = TeamContextController(ref);
  controller.ensureLoaded();
  return controller;
});

class TeamContextController extends StateNotifier<TeamContextState> {
  final Ref _ref;
  SharedPreferences? _prefs;
  bool _isLoading = false;
  String _scopeKey = 'global';

  TeamContextController(this._ref) : super(const TeamContextState());

  Future<void> ensureLoaded() async {
    if (state.isLoaded || _isLoading) {
      return;
    }
    _isLoading = true;
    _prefs ??= await SharedPreferences.getInstance();
    final teamId = _prefs!.getInt(_scoped(PrefsKeys.activeTeamId));
    final categoryId = _prefs!.getInt(_scoped(PrefsKeys.activeCategoryId));
    state = state.copyWith(
      activeTeamId: teamId,
      activeCategoryId: categoryId,
      isLoaded: true,
    );
    _isLoading = false;
  }

  Future<void> setActiveTeam(
    int teamId, {
    int? categoryId,
  }) async {
    await ensureLoaded();
    _prefs ??= await SharedPreferences.getInstance();
    await _prefs!.setInt(_scoped(PrefsKeys.activeTeamId), teamId);
    if (categoryId == null) {
      await _prefs!.remove(_scoped(PrefsKeys.activeCategoryId));
    } else {
      await _prefs!.setInt(_scoped(PrefsKeys.activeCategoryId), categoryId);
    }

    state = state.copyWith(
      activeTeamId: teamId,
      activeCategoryId: categoryId,
      isLoaded: true,
    );
    _bumpRefresh();
  }

  Future<void> clear() async {
    await ensureLoaded();
    _prefs ??= await SharedPreferences.getInstance();
    await _prefs!.remove(_scoped(PrefsKeys.activeTeamId));
    await _prefs!.remove(_scoped(PrefsKeys.activeCategoryId));
    state = state.copyWith(
      activeTeamId: null,
      activeCategoryId: null,
      isLoaded: true,
    );
    _bumpRefresh();
  }

  void setScope(String scopeKey) {
    final normalized = scopeKey.trim().isEmpty ? 'global' : scopeKey.trim();
    if (_scopeKey == normalized) {
      return;
    }
    _scopeKey = normalized;
    _prefs = null;
    state = const TeamContextState();
  }

  void _bumpRefresh() {
    final current = _ref.read(teamContextRefreshProvider);
    _ref.read(teamContextRefreshProvider.notifier).state = current + 1;
  }

  String _scoped(String key) => '${key}_$_scopeKey';
}
