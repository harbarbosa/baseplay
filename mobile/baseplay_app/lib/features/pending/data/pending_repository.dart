import '../../../core/api/endpoints.dart';
import '../../../core/network/api_client.dart';
import '../../../core/network/api_response_parser.dart';
import '../../agenda/data/event_repository.dart';
import '../../agenda/domain/models/event.dart';
import '../../notices/data/notice_repository.dart';
import '../../notices/domain/models/notice.dart';
import '../domain/pending_item.dart';
import '../../../domain/models/user.dart';
import '../../../core/context/team_context.dart';
import 'package:dio/dio.dart';

class PendingRepository {
  PendingRepository(
    this._api,
    this._notices,
    this._events,
    this._teamContext,
  );

  final ApiClient _api;
  final NoticeRepository _notices;
  final EventRepository _events;
  final TeamContextState _teamContext;

  Future<List<PendingItem>> loadPending(User user) async {
    final items = <PendingItem>[];
    final isStaff = _isStaff(user);

    items.addAll(await _loadUnreadNotices());

    if (isStaff) {
      items.addAll(await _loadTeamDocumentAlerts());
      items.addAll(await _loadMissingRequiredDocuments());
    } else {
      items.addAll(await _loadSelfDocumentAlerts());
      items.addAll(await _loadEventConfirmations());
    }

    return items;
  }

  bool _isStaff(User user) {
    final roles = user.roles.map((r) => r.toLowerCase().trim()).toSet();
    return roles.contains('admin') ||
        roles.contains('trainer') ||
        roles.contains('assistant') ||
        roles.contains('treinador') ||
        roles.contains('auxiliar');
  }

  Future<List<PendingItem>> _loadUnreadNotices() async {
    try {
      final notices = await _notices.listNotices();
      final unread = notices.where((n) => !n.isRead).toList();
      return unread
          .map(
            (notice) => PendingItem(
              id: 'notice-${notice.id}',
              type: PendingType.notices,
              title: notice.title,
              description: notice.message,
              priority: _mapNoticePriority(notice.priority),
              ctaLabel: 'Ver aviso',
              actionType: PendingActionType.openRoute,
              route: '/home/notices/${notice.id}',
            ),
          )
          .toList();
    } catch (_) {
      return const [];
    }
  }

  PendingPriority _mapNoticePriority(String raw) {
    switch (raw.toLowerCase()) {
      case 'urgent':
        return PendingPriority.high;
      case 'important':
        return PendingPriority.medium;
      default:
        return PendingPriority.low;
    }
  }

  Future<List<PendingItem>> _loadTeamDocumentAlerts() async {
    try {
      final response = await _api.dio.get(Endpoints.documentAlerts);
      final payload = ApiResponseParser.extractData(response.data);
      final data = ApiResponseParser.asMap(payload);

      final expiredItems = (data['expired'] as List?) ?? const [];
      final expiringMap = ApiResponseParser.asMap(data['expiring']);
      final expiring7 = (expiringMap['7'] as List?) ?? const [];
      final expiring15 = (expiringMap['15'] as List?) ?? const [];
      final expiring30 = (expiringMap['30'] as List?) ?? const [];

      final items = <PendingItem>[];
      if (expiredItems.isNotEmpty) {
        items.add(
          PendingItem(
            id: 'docs-expired',
            type: PendingType.documents,
            title: 'Documentos vencidos',
            description: '${expiredItems.length} documento(s) vencido(s).',
            priority: PendingPriority.high,
            ctaLabel: 'Ver documentos',
            actionType: PendingActionType.openRoute,
            route: '/home/documents',
          ),
        );
      }

      final expiringCount = {
        ...expiring7.map((e) => (e as Map)['id']),
        ...expiring15.map((e) => (e as Map)['id']),
        ...expiring30.map((e) => (e as Map)['id']),
      }.length;
      if (expiringCount > 0) {
        items.add(
          PendingItem(
            id: 'docs-expiring',
            type: PendingType.documents,
            title: 'Documentos a vencer',
            description: '$expiringCount documento(s) a vencer.',
            priority: PendingPriority.medium,
            ctaLabel: 'Ver documentos',
            actionType: PendingActionType.openRoute,
            route: '/home/documents',
          ),
        );
      }

      return items;
    } on DioException {
      return const [];
    }
  }

  Future<List<PendingItem>> _loadSelfDocumentAlerts() async {
    try {
      final response = await _api.dio.get(Endpoints.documentAlerts);
      final payload = ApiResponseParser.extractData(response.data);
      final data = ApiResponseParser.asMap(payload);
      final expiredItems = (data['expired'] as List?) ?? const [];

      if (expiredItems.isEmpty) {
        return const [];
      }

      return [
        PendingItem(
          id: 'self-docs-expired',
          type: PendingType.documents,
          title: 'Documentos vencidos',
          description: '${expiredItems.length} documento(s) vencido(s).',
          priority: PendingPriority.high,
          ctaLabel: 'Ver documentos',
          actionType: PendingActionType.openRoute,
          route: '/home/documents',
        ),
      ];
    } on DioException {
      return const [];
    }
  }

  Future<List<PendingItem>> _loadMissingRequiredDocuments() async {
    final teamId = _teamContext.activeTeamId;
    if (teamId == null || teamId <= 0) {
      return const [];
    }

    final categoryId = _teamContext.activeCategoryId;
    try {
      final response = await _api.dio.get(
        Endpoints.documentsMissingRequired,
        queryParameters: {
          'team_id': teamId,
          if (categoryId != null && categoryId > 0) 'category_id': categoryId,
        },
      );
      final payload = ApiResponseParser.extractData(response.data);
      final data = ApiResponseParser.asMap(payload);
      final items = (data['items'] as List?) ?? const [];
      final pager = ApiResponseParser.asMap(data['pager']);
      final count =
          int.tryParse('${pager['total'] ?? items.length}') ?? items.length;
      if (count <= 0) {
        return const [];
      }

      return [
        PendingItem(
          id: 'docs-missing',
          type: PendingType.documents,
          title: 'Documentos obrigatórios pendentes',
          description: '$count pendência(s) encontrada(s).',
          priority: PendingPriority.high,
          ctaLabel: 'Ver documentos',
          actionType: PendingActionType.openRoute,
          route: '/home/documents',
        ),
      ];
    } on DioException {
      return const [];
    }
  }

  Future<List<PendingItem>> _loadEventConfirmations() async {
    try {
      final now = DateTime.now();
      final to = now.add(const Duration(days: 20));
      final events = await _events.listUpcomingForConfirmation(
        from: now,
        to: to,
      );
      return events
          .map(
            (event) => PendingItem(
              id: 'confirm-${event.id}',
              type: PendingType.events,
              title: 'Confirmar presença',
              description: event.title,
              priority: PendingPriority.high,
              ctaLabel: 'Confirmar',
              actionType: PendingActionType.confirmEvent,
              actionPayload: {'eventId': event.id},
            ),
          )
          .toList();
    } catch (_) {
      return const [];
    }
  }
}

