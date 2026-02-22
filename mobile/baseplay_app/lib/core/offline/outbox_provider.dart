import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../presentation/state/providers.dart';
import 'outbox_item.dart';
import 'outbox_service.dart';
import 'outbox_storage.dart';

final outboxServiceProvider = Provider<OutboxService>((ref) {
  return OutboxService(
    OutboxStorage(),
    ref.read(apiClientProvider),
    ref.read(tokenStorageProvider),
  );
});

final outboxControllerProvider =
    StateNotifierProvider<OutboxController, List<OutboxItem>>((ref) {
  final controller = OutboxController(ref.read(outboxServiceProvider));
  controller.load();
  return controller;
});

class OutboxController extends StateNotifier<List<OutboxItem>> {
  final OutboxService _service;

  OutboxController(this._service) : super(const []);

  Future<void> load() async {
    state = await _service.load();
  }

  Future<void> enqueue(OutboxItem item) async {
    state = await _service.enqueue(item);
  }

  Future<void> processQueue() async {
    state = await _service.processQueue();
  }
}
