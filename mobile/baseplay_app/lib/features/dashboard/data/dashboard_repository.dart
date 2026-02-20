import 'package:dio/dio.dart';

import '../../../core/api/endpoints.dart';
import '../../../core/network/api_client.dart';
import '../../../core/network/api_exception.dart';
import '../../../core/network/api_response_parser.dart';
import '../domain/models/dashboard_summary.dart';

class DashboardRepository {
  final ApiClient _api;

  DashboardRepository(this._api);

  Future<DashboardSummary> getDashboardByProfile() async {
    final attempts = <({String endpoint, String profile})>[
      (endpoint: Endpoints.dashboardAdmin, profile: 'admin'),
      (endpoint: Endpoints.dashboardTrainer, profile: 'treinador'),
      (endpoint: Endpoints.dashboardAssistant, profile: 'auxiliar'),
      (endpoint: Endpoints.dashboardAthlete, profile: 'atleta'),
    ];

    DioException? lastError;

    for (final attempt in attempts) {
      try {
        final response = await _api.dio.get(attempt.endpoint);
        final payload = ApiResponseParser.extractData(response.data);
        final data = ApiResponseParser.asMap(payload);
        final kpis = ApiResponseParser.asMap(data['kpis']);
        return DashboardSummary(profile: attempt.profile, kpis: kpis);
      } on DioException catch (error) {
        final code = error.response?.statusCode;
        if (code == 401) {
          throw _mapException(error, fallback: 'Sessao expirada.');
        }

        if (code == 403 || code == 404) {
          lastError = error;
          continue;
        }

        throw _mapException(error, fallback: 'Nao foi possivel carregar painel.');
      }
    }

    if (lastError != null) {
      throw _mapException(lastError, fallback: 'Nao foi possivel identificar o perfil para o painel.');
    }

    throw const ApiException('Nao foi possivel carregar painel.');
  }

  Future<PendingCenterSummary> getPendingCenterSummary() async {
    int expired = 0;
    int expiring = 0;
    int missingRequired = 0;
    int upcomingWithoutCallups = 0;

    try {
      final response = await _api.dio.get(Endpoints.documentAlerts);
      final payload = ApiResponseParser.extractData(response.data);
      final data = ApiResponseParser.asMap(payload);

      final expiredItems = (data['expired'] as List?) ?? const [];
      final expiringMap = ApiResponseParser.asMap(data['expiring']);
      final expiring7 = (expiringMap['7'] as List?) ?? const [];
      final expiring15 = (expiringMap['15'] as List?) ?? const [];
      final expiring30 = (expiringMap['30'] as List?) ?? const [];

      expired = expiredItems.length;
      expiring = {
        ...expiring7.map((e) => (e as Map)['id']),
        ...expiring15.map((e) => (e as Map)['id']),
        ...expiring30.map((e) => (e as Map)['id']),
      }.length;
    } on DioException catch (error) {
      final code = error.response?.statusCode;
      if (code != 403 && code != 404) {
        throw _mapException(error, fallback: 'Nao foi possivel carregar pendencias de documentos.');
      }
    }

    try {
      final alertsResponse = await _api.dio.get(
        Endpoints.alerts,
        queryParameters: {'type': 'missing_document', 'is_read': 0, 'per_page': 100},
      );
      final payload = ApiResponseParser.extractData(alertsResponse.data);
      final data = ApiResponseParser.asMap(payload);
      final items = (data['items'] as List?) ?? const [];
      missingRequired = items.length;
    } on DioException catch (error) {
      final code = error.response?.statusCode;
      if (code != 403 && code != 404) {
        throw _mapException(error, fallback: 'Nao foi possivel carregar alertas de documentos obrigatorios.');
      }
    }

    try {
      final now = DateTime.now();
      final nextWeek = now.add(const Duration(days: 7));
      final response = await _api.dio.get(
        Endpoints.events,
        queryParameters: {
          'from_date': _formatDate(now),
          'to_date': _formatDate(nextWeek),
          'status': 'scheduled',
          'per_page': 20,
        },
      );
      final payload = ApiResponseParser.extractData(response.data);
      final data = ApiResponseParser.asMap(payload);
      final events = (data['items'] as List?) ?? const [];

      for (final rawEvent in events) {
        final event = ApiResponseParser.asMap(rawEvent);
        final eventId = int.tryParse('${event['id'] ?? 0}') ?? 0;
        if (eventId <= 0) {
          continue;
        }

        try {
          final participantsResponse = await _api.dio.get(Endpoints.eventParticipants(eventId));
          final participantPayload = ApiResponseParser.extractData(participantsResponse.data);
          final participants = (participantPayload as List?) ?? const [];
          if (participants.isEmpty) {
            upcomingWithoutCallups++;
          }
        } on DioException catch (error) {
          final code = error.response?.statusCode;
          if (code == 403 || code == 404) {
            break;
          }
          throw _mapException(error, fallback: 'Nao foi possivel validar convocacoes dos eventos.');
        }
      }
    } on DioException catch (error) {
      final code = error.response?.statusCode;
      if (code != 403 && code != 404) {
        throw _mapException(error, fallback: 'Nao foi possivel carregar eventos pendentes.');
      }
    }

    return PendingCenterSummary(
      expiredDocuments: expired,
      expiringDocuments: expiring,
      missingRequiredDocuments: missingRequired,
      upcomingEventsWithoutCallups: upcomingWithoutCallups,
    );
  }

  ApiException _mapException(DioException error, {required String fallback}) {
    final statusCode = error.response?.statusCode;
    final message = ApiResponseParser.extractMessage(error.response?.data, fallback: fallback);
    return ApiException(message, statusCode: statusCode, isUnauthorized: statusCode == 401);
  }

  String _formatDate(DateTime value) {
    final year = value.year.toString().padLeft(4, '0');
    final month = value.month.toString().padLeft(2, '0');
    final day = value.day.toString().padLeft(2, '0');
    return '$year-$month-$day';
  }
}
