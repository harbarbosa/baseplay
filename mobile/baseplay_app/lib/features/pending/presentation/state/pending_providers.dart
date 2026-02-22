import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../presentation/state/providers.dart';
import '../../../agenda/presentation/state/agenda_providers.dart';
import '../../../notices/presentation/state/notices_providers.dart';
import '../../../../core/network/api_client.dart';
import '../../../../core/context/team_context_provider.dart';
import '../../data/pending_repository.dart';
import '../../domain/pending_item.dart';

final pendingRepositoryProvider = Provider<PendingRepository>((ref) {
  return PendingRepository(
    ref.read(apiClientProvider),
    ref.read(noticeRepositoryProvider),
    ref.read(eventRepositoryProvider),
    ref.read(teamContextProvider),
  );
});

final pendingItemsProvider =
    FutureProvider.autoDispose<List<PendingItem>>((ref) async {
      ref.watch(teamContextRefreshProvider);
      final user = ref.watch(authUserProvider);
      if (user == null) {
        return const [];
      }
      return ref.read(pendingRepositoryProvider).loadPending(user);
    });

final pendingCountProvider = Provider<int>((ref) {
  final pendingAsync = ref.watch(pendingItemsProvider);
  return pendingAsync.maybeWhen(data: (items) => items.length, orElse: () => 0);
});

final pendingActionControllerProvider = Provider<PendingActionController>((ref) {
  return PendingActionController(ref);
});

class PendingActionController {
  final Ref _ref;

  PendingActionController(this._ref);

  Future<void> confirmEvent(int eventId) async {
    await _ref.read(eventRepositoryProvider).confirmEvent(eventId);
    _ref.invalidate(pendingItemsProvider);
    _ref.invalidate(agendaEventsProvider);
  }
}
