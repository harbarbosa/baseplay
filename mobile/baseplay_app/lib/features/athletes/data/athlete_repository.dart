import 'package:dio/dio.dart';

import '../../../core/api/endpoints.dart';
import '../../../core/network/api_client.dart';
import '../../../core/network/api_exception.dart';
import '../../../core/network/api_response_parser.dart';
import '../../agenda/domain/models/event.dart';
import '../../agenda/domain/models/attendance.dart';
import '../domain/models/athlete_model.dart';
import '../domain/models/athlete_summary_model.dart';
import '../domain/models/attendance_history_model.dart';

class AthleteListResponse {
  final List<AthleteModel> items;
  final int currentPage;
  final int pageCount;

  const AthleteListResponse({
    required this.items,
    required this.currentPage,
    required this.pageCount,
  });
}

class DocumentIndicator {
  final bool hasPending;
  final int active;
  final int expired;
  final int expiringSoon;

  const DocumentIndicator({
    required this.hasPending,
    required this.active,
    required this.expired,
    required this.expiringSoon,
  });
}

class AthleteRepository {
  final ApiClient _api;

  AthleteRepository(this._api);

  Future<AthleteListResponse> listAthletes({
    String? search,
    int? categoryId,
    int page = 1,
    int perPage = 15,
  }) async {
    try {
      final response = await _api.dio.get(
        Endpoints.athletes,
        queryParameters: {
          'search': search,
          'category_id': categoryId,
          'page': page,
          'per_page': perPage,
        }..removeWhere((key, value) => value == null || '$value'.isEmpty),
      );

      final payload = ApiResponseParser.extractData(response.data);
      final data = ApiResponseParser.asMap(payload);
      final rawItems = (data['items'] as List?) ?? const [];
      final pager = ApiResponseParser.asMap(data['pager']);

      return AthleteListResponse(
        items: rawItems
            .map((item) => AthleteModel.fromJson(ApiResponseParser.asMap(item)))
            .toList(),
        currentPage: int.tryParse('${pager['currentPage'] ?? 1}') ?? 1,
        pageCount: int.tryParse('${pager['pageCount'] ?? 1}') ?? 1,
      );
    } on DioException catch (e) {
      throw _mapException(e, fallback: 'Não foi possível carregar atletas.');
    }
  }

  Future<AthleteModel> getAthlete(int athleteId) async {
    try {
      final response = await _api.dio.get(Endpoints.athleteById(athleteId));
      final data = ApiResponseParser.asMap(
        ApiResponseParser.extractData(response.data),
      );
      return AthleteModel.fromJson(data);
    } on DioException catch (e) {
      throw _mapException(e, fallback: 'Não foi possível carregar atleta.');
    }
  }

  Future<DocumentIndicator> getDocumentIndicator(int athleteId) async {
    try {
      final response = await _api.dio.get(
        Endpoints.documents,
        queryParameters: {'athlete_id': athleteId, 'per_page': 100},
      );
      final payload = ApiResponseParser.extractData(response.data);
      final data = ApiResponseParser.asMap(payload);
      final rawItems = (data['items'] as List?) ?? const [];

      var active = 0;
      var expired = 0;
      var expiringSoon = 0;

      final now = DateTime.now();
      final limit = now.add(const Duration(days: 30));

      for (final item in rawItems) {
        final row = ApiResponseParser.asMap(item);
        final status = '${row['status'] ?? ''}'.toLowerCase();
        if (status == 'expired') {
          expired++;
          continue;
        }

        if (status == 'active') {
          active++;
          final rawExpires = row['expires_at']?.toString();
          final expires = rawExpires == null || rawExpires.isEmpty
              ? null
              : DateTime.tryParse(rawExpires);
          if (expires != null &&
              !expires.isBefore(now) &&
              !expires.isAfter(limit)) {
            expiringSoon++;
          }
        }
      }

      return DocumentIndicator(
        hasPending: expired > 0 || expiringSoon > 0,
        active: active,
        expired: expired,
        expiringSoon: expiringSoon,
      );
    } on DioException catch (e) {
      throw _mapException(
        e,
        fallback: 'Não foi possível carregar documentos do atleta.',
      );
    }
  }

  Future<Map<int, DocumentIndicator>> getDocumentIndicators(
    List<int> athleteIds,
  ) async {
    final result = <int, DocumentIndicator>{};
    await Future.wait(
      athleteIds.map((id) async {
        result[id] = await getDocumentIndicator(id);
      }),
    );
    return result;
  }

  Future<AthleteSummaryModel> getAthleteSummary(int athleteId) async {
    final athlete = await getAthlete(athleteId);

    final report = await _getAthleteReport(athleteId);
    final lastActivity = await _getLastActivity(athleteId);
    final documents = await getDocumentIndicator(athleteId);
    final nextEvent = await _getNextEvent(athlete.categoryId);
    final history = await _getAttendanceHistory(athleteId, athlete.categoryId);

    final present = _extractMetric(report, 'Presencas');
    final late = _extractMetric(report, 'Atrasos');
    final justified = _extractMetric(report, 'Justificadas');
    final total = _extractMetric(report, 'Total de presenca');
    final absences = _extractMetric(report, 'Faltas');

    final percentage = total > 0
        ? (((present + late + justified) / total) * 100)
        : 0;

    final training = ApiResponseParser.asMap(lastActivity['last_training']);
    final match = ApiResponseParser.asMap(lastActivity['last_match']);

    return AthleteSummaryModel(
      presencePercentage: double.parse(percentage.toStringAsFixed(1)),
      totalSessions: total,
      absences: absences,
      lastTrainingTitle: training['title']?.toString(),
      lastTrainingDate: _parseDate(training['date']),
      lastMatchTitle: match['title']?.toString(),
      lastMatchDate: _parseDate(match['date']),
      documentsActive: documents.active,
      documentsExpired: documents.expired,
      documentsExpiringSoon: documents.expiringSoon,
      nextEventType: nextEvent?.type,
      nextEventDate: nextEvent?.startDateTime,
      nextEventTitle: nextEvent?.title,
      attendanceHistory: history,
    );
  }

  Future<List<List<dynamic>>> _getAthleteReport(int athleteId) async {
    try {
      final response = await _api.dio.get(Endpoints.reportAthlete(athleteId));
      final payload = ApiResponseParser.extractData(response.data);
      final data = ApiResponseParser.asMap(payload);
      final rows = (data['rows'] as List?) ?? const [];

      return rows.whereType<List>().map((row) => row.toList()).toList();
    } on DioException catch (e) {
      throw _mapException(
        e,
        fallback: 'Não foi possível carregar resumo do atleta.',
      );
    }
  }

  Future<Map<String, dynamic>> _getLastActivity(int athleteId) async {
    try {
      final response = await _api.dio.get(
        Endpoints.athleteLastActivity(athleteId),
      );
      return ApiResponseParser.asMap(
        ApiResponseParser.extractData(response.data),
      );
    } on DioException catch (e) {
      throw _mapException(
        e,
        fallback: 'Não foi possível carregar última atividade.',
      );
    }
  }

  Future<Event?> _getNextEvent(int? categoryId) async {
    if (categoryId == null || categoryId <= 0) {
      return null;
    }

    try {
      final now = DateTime.now();
      final to = now.add(const Duration(days: 30));
      final response = await _api.dio.get(
        Endpoints.events,
        queryParameters: {
          'category_id': categoryId,
          'from_date': _formatDate(now),
          'to_date': _formatDate(to),
          'per_page': 25,
        },
      );

      final payload = ApiResponseParser.extractData(response.data);
      final data = ApiResponseParser.asMap(payload);
      final rawItems = (data['items'] as List?) ?? const [];
      final events =
          rawItems
              .map((item) => Event.fromJson(ApiResponseParser.asMap(item)))
              .where((event) => event.startDateTime != null)
              .toList()
            ..sort((a, b) => a.startDateTime!.compareTo(b.startDateTime!));

      return events.isEmpty ? null : events.first;
    } on DioException {
      return null;
    }
  }

  Future<List<AttendanceHistoryModel>> _getAttendanceHistory(
    int athleteId,
    int? categoryId,
  ) async {
    if (categoryId == null || categoryId <= 0) {
      return const [];
    }

    try {
      final now = DateTime.now();
      final from = now.subtract(const Duration(days: 45));

      final eventsResponse = await _api.dio.get(
        Endpoints.events,
        queryParameters: {
          'category_id': categoryId,
          'from_date': _formatDate(from),
          'to_date': _formatDate(now),
          'per_page': 30,
        },
      );

      final eventsPayload = ApiResponseParser.extractData(eventsResponse.data);
      final eventsData = ApiResponseParser.asMap(eventsPayload);
      final eventsRaw = (eventsData['items'] as List?) ?? const [];
      final events = eventsRaw
          .map((item) => Event.fromJson(ApiResponseParser.asMap(item)))
          .toList();

      final history = <AttendanceHistoryModel>[];

      await Future.wait(
        events.map((event) async {
          try {
            final attendanceResponse = await _api.dio.get(
              Endpoints.eventAttendance(event.id),
            );
            final payload = ApiResponseParser.extractData(
              attendanceResponse.data,
            );
            final rows = (payload as List?) ?? const [];
            for (final row in rows) {
              final attendance = Attendance.fromJson(
                ApiResponseParser.asMap(row),
              );
              if (attendance.athleteId == athleteId) {
                history.add(
                  AttendanceHistoryModel(
                    eventDate: event.startDateTime,
                    eventType: event.type,
                    eventTitle: event.title,
                    status: attendance.status,
                  ),
                );
              }
            }
          } on DioException {
            // ignore eventos sem permissao de attendance para manter a tela funcional
          }
        }),
      );

      history.sort((a, b) {
        final left = a.eventDate ?? DateTime.fromMillisecondsSinceEpoch(0);
        final right = b.eventDate ?? DateTime.fromMillisecondsSinceEpoch(0);
        return right.compareTo(left);
      });

      return history.take(20).toList();
    } on DioException {
      return const [];
    }
  }

  int _extractMetric(List<List<dynamic>> rows, String indicator) {
    for (final row in rows) {
      if (row.length < 2) {
        continue;
      }
      if ('${row[0]}' == indicator) {
        return int.tryParse('${row[1]}') ?? 0;
      }
    }
    return 0;
  }

  DateTime? _parseDate(dynamic value) {
    final raw = value?.toString();
    if (raw == null || raw.isEmpty) {
      return null;
    }
    return DateTime.tryParse(raw);
  }

  String _formatDate(DateTime value) {
    final y = value.year.toString().padLeft(4, '0');
    final m = value.month.toString().padLeft(2, '0');
    final d = value.day.toString().padLeft(2, '0');
    return '$y-$m-$d';
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

