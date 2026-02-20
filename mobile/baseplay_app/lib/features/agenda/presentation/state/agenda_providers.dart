import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../../presentation/state/providers.dart';
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
  return ref.read(eventRepositoryProvider).getEvent(eventId);
});

final eventParticipantsProvider = FutureProvider.autoDispose
    .family<List<Participant>, int>((ref, eventId) {
      return ref.read(eventRepositoryProvider).getParticipants(eventId);
    });

final eventAttendanceProvider = FutureProvider.autoDispose
    .family<List<Attendance>, int>((ref, eventId) {
      return ref.read(eventRepositoryProvider).getAttendance(eventId);
    });

final eventAttendanceControllerProvider = Provider<EventAttendanceController>((
  ref,
) {
  return EventAttendanceController(ref);
});

class EventAttendanceController {
  final Ref _ref;

  EventAttendanceController(this._ref);

  Future<void> save({
    required int eventId,
    required int athleteId,
    required String status,
    String? notes,
  }) async {
    await _ref
        .read(eventRepositoryProvider)
        .upsertAttendance(
          eventId: eventId,
          athleteId: athleteId,
          status: status,
          notes: notes,
        );

    _ref.invalidate(eventAttendanceProvider(eventId));
  }
}
