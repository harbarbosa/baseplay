import 'dart:convert';

import 'package:shared_preferences/shared_preferences.dart';

import 'outbox_item.dart';

class OutboxStorage {
  static const _key = 'outbox_items';

  Future<List<OutboxItem>> load() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getString(_key);
    if (raw == null || raw.isEmpty) {
      return const [];
    }
    try {
      final decoded = jsonDecode(raw);
      if (decoded is! List) {
        return const [];
      }
      return decoded
          .whereType<Map>()
          .map((item) => OutboxItem.fromJson(item.cast<String, dynamic>()))
          .toList();
    } catch (_) {
      return const [];
    }
  }

  Future<void> save(List<OutboxItem> items) async {
    final prefs = await SharedPreferences.getInstance();
    final data = items.map((item) => item.toJson()).toList();
    await prefs.setString(_key, jsonEncode(data));
  }
}
