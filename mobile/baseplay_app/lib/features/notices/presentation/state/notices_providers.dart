import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/storage/cache_storage.dart';
import '../../../../presentation/state/providers.dart';
import '../../data/notice_read_cache.dart';
import '../../data/notice_repository.dart';
import '../../domain/models/notice.dart';

final noticeReadCacheProvider = Provider<NoticeReadCache>((ref) {
  return NoticeReadCache(CacheStorage());
});

final noticeRepositoryProvider = Provider<NoticeRepository>((ref) {
  return NoticeRepository(
    ref.read(apiClientProvider),
    ref.read(noticeReadCacheProvider),
  );
});

final noticesProvider = FutureProvider.autoDispose<List<Notice>>((ref) async {
  return ref.read(noticeRepositoryProvider).listNotices();
});

final noticeDetailProvider = FutureProvider.autoDispose.family<Notice, int>((
  ref,
  noticeId,
) {
  return ref.read(noticeRepositoryProvider).getNotice(noticeId);
});

final noticeActionControllerProvider = Provider<NoticeActionController>((ref) {
  return NoticeActionController(ref);
});

class NoticeActionController {
  final Ref _ref;

  NoticeActionController(this._ref);

  Future<void> markAsRead(int noticeId) async {
    await _ref.read(noticeRepositoryProvider).markAsRead(noticeId);
    _ref.invalidate(noticesProvider);
    _ref.invalidate(noticeDetailProvider(noticeId));
  }
}
