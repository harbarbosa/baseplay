import 'package:dio/dio.dart';
import '../../../core/api/endpoints.dart';
import '../../../core/network/api_client.dart';
import '../../../core/network/api_exception.dart';
import '../../../core/network/api_response_parser.dart';
import '../domain/models/attendance.dart';
import '../domain/models/event.dart';
import '../domain/models/participant.dart';

class EventRepository {
  final ApiClient _api;

  EventRepository(this._api);

  Future<List<Event>> listEvents({
    required DateTime from,
    required DateTime to,
  }) async {
    try {
      final response = await _api.dio.get(
        Endpoints.events,
        queryParameters: {
          'from_date': _formatDate(from),
          'to_date': _formatDate(to),
          'per_page': 100,
        },
      );

      final payload = ApiResponseParser.extractData(response.data);
      final data = ApiResponseParser.asMap(payload);
      final rawItems = (data['items'] as List?) ?? const [];

      return rawItems
          .map((item) => Event.fromJson(ApiResponseParser.asMap(item)))
          .toList();
    } on DioException catch (e) {
      throw _mapException(
        e,
        fallback: 'Não foi possível carregar a agenda.',
      );
    }
  }

  Future<Event> getEvent(int id) async {
    try {
      final response = await _api.dio.get(Endpoints.eventById(id));
      final payload = ApiResponseParser.extractData(response.data);
      return Event.fromJson(ApiResponseParser.asMap(payload));
    } on DioException catch (e) {
      throw _mapException(
        e,
        fallback: 'Não foi possível carregar o evento.',
      );
    }
  }

  Future<List<Participant>> getParticipants(int eventId) async {
    try {
      final response = await _api.dio.get(Endpoints.eventParticipants(eventId));
      final payload = ApiResponseParser.extractData(response.data);
      final rawItems = (payload as List?) ?? const [];
      return rawItems
          .map((item) => Participant.fromJson(ApiResponseParser.asMap(item)))
          .toList();
    } on DioException catch (e) {
      throw _mapException(
        e,
        fallback: 'Não foi possível carregar convocados.',
      );
    }
  }

  Future<List<Attendance>> getAttendance(int eventId) async {
    try {
      final response = await _api.dio.get(Endpoints.eventAttendance(eventId));
      final payload = ApiResponseParser.extractData(response.data);
      final rawItems = (payload as List?) ?? const [];
      return rawItems
          .map((item) => Attendance.fromJson(ApiResponseParser.asMap(item)))
          .toList();
    } on DioException catch (e) {
      throw _mapException(
        e,
        fallback: 'Não foi possível carregar presença.',
      );
    }
  }

  Future<void> upsertAttendance({
    required int eventId,
    required int athleteId,
    required String status,
    String? notes,
  }) async {
    try {
      await _api.dio.post(
        Endpoints.eventAttendance(eventId),
        data: {
          'athlete_id': athleteId,
          'status': status,
          if (notes != null && notes.trim().isNotEmpty) 'notes': notes.trim(),
        },
      );
    } on DioException catch (e) {
      throw _mapException(
        e,
        fallback: 'Não foi possível registrar presença.',
      );
    }
  }

  Future<void> confirmEvent(int eventId) async {
    try {
      await _api.dio.post(Endpoints.eventConfirm(eventId));
    } on DioException catch (e) {
      throw _mapException(
        e,
        fallback: 'Não foi possível confirmar o evento.',
      );
    }
  }

  Future<List<Event>> listUpcomingForConfirmation({
    required DateTime from,
    required DateTime to,
  }) async {
    try {
      final response = await _api.dio.get(
        Endpoints.events,
        queryParameters: {
          'from_date': _formatDate(from),
          'to_date': _formatDate(to),
          'status': 'scheduled',
          'per_page': 50,
        },
      );

      final payload = ApiResponseParser.extractData(response.data);
      final data = ApiResponseParser.asMap(payload);
      final rawItems = (data['items'] as List?) ?? const [];

      return rawItems
          .map((item) => Event.fromJson(ApiResponseParser.asMap(item)))
          .where((event) =>
              (event.invitationStatus ?? '').toLowerCase() == 'pending')
          .toList();
    } on DioException catch (e) {
      throw _mapException(
        e,
        fallback: 'Não foi possível carregar confirmações pendentes.',
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

  String _formatDate(DateTime value) {
    final year = value.year.toString().padLeft(4, '0');
    final month = value.month.toString().padLeft(2, '0');
    final day = value.day.toString().padLeft(2, '0');
    return '$year-$month-$day';
  }
}

