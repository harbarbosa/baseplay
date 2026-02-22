import 'package:dio/dio.dart';

import '../../../core/api/endpoints.dart';
import '../../../core/network/api_client.dart';
import '../../../core/network/api_exception.dart';
import '../../../core/network/api_response_parser.dart';
import '../domain/models/notice_reply.dart';

class NoticeRepliesRepository {
  final ApiClient _api;

  NoticeRepliesRepository(this._api);

  Future<List<NoticeReply>> listReplies(int noticeId) async {
    try {
      final response = await _api.dio.get(Endpoints.noticeReplies(noticeId));
      final payload = ApiResponseParser.extractData(response.data);
      final rawItems = (payload as List?) ?? const [];
      return rawItems
          .map((item) => NoticeReply.fromJson(ApiResponseParser.asMap(item)))
          .toList();
    } on DioException catch (e) {
      throw _mapException(
        e,
        fallback: 'Não foi possível carregar respostas.',
      );
    }
  }

  Future<void> sendReply(int noticeId, String message) async {
    try {
      await _api.dio.post(
        Endpoints.noticeReply(noticeId),
        data: {'message': message},
      );
    } on DioException catch (e) {
      throw _mapException(
        e,
        fallback: 'Não foi possível enviar resposta.',
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
