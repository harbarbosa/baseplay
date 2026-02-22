import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../core/storage/cache_storage.dart';
import '../../../../presentation/state/providers.dart';
import '../../data/notice_read_cache.dart';
import '../../data/notice_repository.dart';
import '../../data/notice_replies_repository.dart';
import '../../domain/models/notice.dart';
import '../../domain/models/notice_reply.dart';

final noticeReadCacheProvider = Provider<NoticeReadCache>((ref) {
  final userId = ref.watch(authUserProvider)?.id;
  return NoticeReadCache(
    CacheStorage(),
    scopeKey: userId?.toString() ?? 'anonymous',
  );
});

final noticeRepositoryProvider = Provider<NoticeRepository>((ref) {
  return NoticeRepository(
    ref.read(apiClientProvider),
    ref.read(noticeReadCacheProvider),
  );
});

final noticeRepliesRepositoryProvider = Provider<NoticeRepliesRepository>((ref) {
  return NoticeRepliesRepository(ref.read(apiClientProvider));
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

final noticeRepliesProvider =
    FutureProvider.autoDispose.family<List<NoticeReply>, int>((ref, noticeId) {
      return ref.read(noticeRepliesRepositoryProvider).listReplies(noticeId);
    });

final noticeReplyControllerProvider = Provider<NoticeReplyController>((ref) {
  return NoticeReplyController(ref);
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

class NoticeReplyController {
  final Ref _ref;

  NoticeReplyController(this._ref);

  Future<void> send(int noticeId, String message) async {
    await _ref
        .read(noticeRepliesRepositoryProvider)
        .sendReply(noticeId, message);
    _ref.invalidate(noticeRepliesProvider(noticeId));
  }
}
