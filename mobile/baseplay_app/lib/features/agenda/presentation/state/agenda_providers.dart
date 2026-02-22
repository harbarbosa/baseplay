import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../presentation/state/providers.dart';
import '../../../../core/context/team_context_provider.dart';
import '../../../../core/offline/outbox_provider.dart';
import '../../../../core/offline/outbox_item.dart';
import '../../data/event_repository.dart';
import '../../domain/models/attendance.dart';
import '../../domain/models/event.dart';
import '../../domain/models/participant.dart';

enum AgendaRange { today, week }

final eventRepositoryProvider = Provider<EventRepository>((ref) {
  return EventRepository(ref.read(apiClientProvider));
});

final agendaRangeProvider = StateProvider<AgendaRange>(
  (ref) => AgendaRange.today,
);

final agendaEventsProvider = FutureProvider.autoDispose<List<Event>>((
  ref,
) async {
  ref.watch(teamContextRefreshProvider);
  final range = ref.watch(agendaRangeProvider);
  final now = DateTime.now();
  final start = DateTime(now.year, now.month, now.day);
  final end = range == AgendaRange.today
      ? start
      : start.add(const Duration(days: 6));

  return ref.read(eventRepositoryProvider).listEvents(from: start, to: end);
});

final eventDetailProvider = FutureProvider.autoDispose.family<Event, int>((
  ref,
  eventId,
) {
  ref.watch(teamContextRefreshProvider);
  return ref.read(eventRepositoryProvider).getEvent(eventId);
});

final eventParticipantsProvider = FutureProvider.autoDispose
    .family<List<Participant>, int>((ref, eventId) {
      ref.watch(teamContextRefreshProvider);
      return ref.read(eventRepositoryProvider).getParticipants(eventId);
    });

final eventAttendanceProvider = FutureProvider.autoDispose
    .family<List<Attendance>, int>((ref, eventId) {
      ref.watch(teamContextRefreshProvider);
      return ref.read(eventRepositoryProvider).getAttendance(eventId);
    });

final eventAttendanceControllerProvider = Provider<EventAttendanceController>((
  ref,
) {
  return EventAttendanceController(ref);
});

final eventConfirmationControllerProvider =
    Provider<EventConfirmationController>((ref) {
      return EventConfirmationController(ref);
    });

enum AttendanceSaveStatus { synced, pending, error }

class EventAttendanceController {
  final Ref _ref;

  EventAttendanceController(this._ref);

  Future<AttendanceSaveStatus> save({
    required int eventId,
    required int athleteId,
    required String status,
    String? notes,
  }) async {
    try {
      await _ref
          .read(eventRepositoryProvider)
          .upsertAttendance(
            eventId: eventId,
            athleteId: athleteId,
            status: status,
            notes: notes,
          );
      _ref.invalidate(eventAttendanceProvider(eventId));
      return AttendanceSaveStatus.synced;
    } catch (_) {
      final teamId = _ref.read(teamContextProvider).activeTeamId;
      final item = OutboxItem(
        id: '${DateTime.now().microsecondsSinceEpoch}-$athleteId-$eventId',
        type: 'attendance',
        payload: {
          'eventId': eventId,
          'athleteId': athleteId,
          'status': status,
          if (notes != null && notes.trim().isNotEmpty) 'notes': notes.trim(),
          if (teamId != null) 'teamId': teamId,
          'timestamp': DateTime.now().toIso8601String(),
        },
        createdAt: DateTime.now(),
        retries: 0,
        status: OutboxStatus.pending,
      );
      await _ref.read(outboxControllerProvider.notifier).enqueue(item);
      return AttendanceSaveStatus.pending;
    }
  }
}

class EventConfirmationController {
  final Ref _ref;

  EventConfirmationController(this._ref);

  Future<void> confirm(int eventId) async {
    await _ref.read(eventRepositoryProvider).confirmEvent(eventId);
    _ref.invalidate(agendaEventsProvider);
    _ref.invalidate(eventDetailProvider(eventId));
  }
}
