import 'dart:convert';

import '../../../core/storage/cache_storage.dart';

class NoticeReadCache {
  final CacheStorage _cache;
  final String _scopeKey;

  NoticeReadCache(this._cache, {required String scopeKey})
    : _scopeKey = scopeKey;

  String get _key => 'notice_read_ids_$_scopeKey';

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
