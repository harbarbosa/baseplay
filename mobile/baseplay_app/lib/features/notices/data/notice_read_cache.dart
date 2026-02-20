import 'dart:convert';

import '../../../core/storage/cache_storage.dart';

class NoticeReadCache {
  static const _key = 'notice_read_ids';
  final CacheStorage _cache;

  NoticeReadCache(this._cache);

  Future<Set<int>> getReadIds() async {
    final value = await _cache.getString(_key);
    if (value == null || value.isEmpty) {
      return <int>{};
    }

    try {
      final data = jsonDecode(value);
      if (data is! List) {
        return <int>{};
      }
      return data.map((item) => int.tryParse('$item')).whereType<int>().toSet();
    } catch (_) {
      return <int>{};
    }
  }

  Future<void> markRead(int noticeId) async {
    final ids = await getReadIds();
    ids.add(noticeId);
    await _cache.setString(_key, jsonEncode(ids.toList()));
  }
}
