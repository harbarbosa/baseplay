import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';

import '../api/endpoints.dart';
import '../network/api_client.dart';
import '../storage/token_storage.dart';
import 'outbox_item.dart';
import 'outbox_storage.dart';

class OutboxService {
  OutboxService(this._storage, this._api, this._tokenStorage);

  final OutboxStorage _storage;
  final ApiClient _api;
  final TokenStorage _tokenStorage;
  bool _isProcessing = false;

  Future<List<OutboxItem>> load() => _storage.load();

  Future<List<OutboxItem>> enqueue(OutboxItem item) async {
    final items = await _storage.load();
    final next = [...items, item];
    await _storage.save(next);
    return next;
  }

  Future<List<OutboxItem>> updateAll(List<OutboxItem> items) async {
    await _storage.save(items);
    return items;
  }

  Future<List<OutboxItem>> processQueue() async {
    if (_isProcessing) {
      return _storage.load();
    }
    _isProcessing = true;
    final token = await _tokenStorage.read();
    if (token == null || token.isEmpty) {
      _isProcessing = false;
      return _storage.load();
    }

    var items = await _storage.load();
    final updated = <OutboxItem>[];

    for (final item in items) {
      if (item.status == OutboxStatus.done) {
        continue;
      }

      final result = await _processItem(item);
      if (result.status != OutboxStatus.done) {
        updated.add(result);
      }
    }

    await _storage.save(updated);
    _isProcessing = false;
    return updated;
  }

  Future<OutboxItem> _processItem(OutboxItem item) async {
    try {
      switch (item.type) {
        case 'attendance':
          await _sendAttendance(item.payload);
          return item.copyWith(status: OutboxStatus.done);
        default:
          return item.copyWith(status: OutboxStatus.error);
      }
    } on DioException catch (error) {
      final attempts = item.retries + 1;
      final status = attempts >= 3 ? OutboxStatus.error : OutboxStatus.pending;
      if (kDebugMode) {
        debugPrint('Outbox falha ${item.type}: ${error.message}');
      }
      return item.copyWith(retries: attempts, status: status);
    } catch (_) {
      final attempts = item.retries + 1;
      final status = attempts >= 3 ? OutboxStatus.error : OutboxStatus.pending;
      return item.copyWith(retries: attempts, status: status);
    }
  }

  Future<void> _sendAttendance(Map<String, dynamic> payload) async {
    final eventId = int.tryParse('${payload['eventId'] ?? ''}') ?? 0;
    final athleteId = int.tryParse('${payload['athleteId'] ?? ''}') ?? 0;
    final status = (payload['status'] ?? '').toString();
    final notes = payload['notes']?.toString();
    final teamId = payload['teamId'];

    if (eventId <= 0 || athleteId <= 0 || status.isEmpty) {
      throw DioException(
        requestOptions: RequestOptions(path: ''),
        message: 'Payload inválido para attendance.',
      );
    }

    final headers = <String, dynamic>{};
    if (teamId != null) {
      headers['X-Team-Id'] = teamId.toString();
    }

    await _api.dio.post(
      Endpoints.eventAttendance(eventId),
      data: {
        'athlete_id': athleteId,
        'status': status,
        if (notes != null && notes.trim().isNotEmpty) 'notes': notes.trim(),
      },
      options: Options(headers: headers),
    );
  }
}
