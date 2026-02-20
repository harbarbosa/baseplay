import 'package:dio/dio.dart';

import '../../../core/api/endpoints.dart';
import '../../../core/network/api_client.dart';
import '../../../core/network/api_exception.dart';
import '../../../core/network/api_response_parser.dart';
import 'notice_read_cache.dart';
import '../domain/models/notice.dart';

class NoticeRepository {
  final ApiClient _api;
  final NoticeReadCache _cache;

  NoticeRepository(this._api, this._cache);

  Future<List<Notice>> listNotices() async {
    try {
      final readIds = await _cache.getReadIds();
      final response = await _api.dio.get(
        Endpoints.notices,
        queryParameters: {'per_page': 100},
      );
      final payload = ApiResponseParser.extractData(response.data);
      final data = ApiResponseParser.asMap(payload);
      final rawItems = (data['items'] as List?) ?? const [];

      return rawItems
          .map(
            (item) => Notice.fromJson(
              ApiResponseParser.asMap(item),
              isRead: readIds.contains(
                int.tryParse('${ApiResponseParser.asMap(item)['id'] ?? 0}') ??
                    0,
              ),
            ),
          )
          .toList();
    } on DioException catch (e) {
      throw _mapException(
        e,
        fallback: 'Não foi possível carregar avisos.',
      );
    }
  }

  Future<Notice> getNotice(int id) async {
    try {
      final readIds = await _cache.getReadIds();
      final response = await _api.dio.get(Endpoints.noticeById(id));
      final payload = ApiResponseParser.extractData(response.data);
      return Notice.fromJson(
        ApiResponseParser.asMap(payload),
        isRead: readIds.contains(id),
      );
    } on DioException catch (e) {
      throw _mapException(
        e,
        fallback: 'Não foi possível carregar aviso.',
      );
    }
  }

  Future<void> markAsRead(int id) async {
    try {
      await _api.dio.post(Endpoints.noticeRead(id));
      await _cache.markRead(id);
    } on DioException catch (e) {
      throw _mapException(
        e,
        fallback: 'Não foi possível marcar como lido.',
      );
    }
  }

  ApiException _mapException(DioException error, {required String fallback}) {
    final statusCode = error.response?.statusCode;
    final message = ApiResponseParser.extractMessage(
      error.response?.data,
      fallback: fallback,
    );

    return ApiException(
      message,
      statusCode: statusCode,
      isUnauthorized: statusCode == 401,
    );
  }
}
