import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../presentation/state/providers.dart';
import '../../domain/models/event.dart';
import '../state/agenda_providers.dart';

class EventDetailScreen extends ConsumerWidget {
  final int eventId;

  const EventDetailScreen({super.key, required this.eventId});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final eventAsync = ref.watch(eventDetailProvider(eventId));
    final participantsAsync = ref.watch(eventParticipantsProvider(eventId));
    final canManageAttendance =
        ref.watch(authUserProvider)?.hasPermission('attendance.manage') ?? false;

    return Scaffold(
      appBar: AppBar(title: const Text('Detalhe do evento')),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(eventDetailProvider(eventId));
          ref.invalidate(eventParticipantsProvider(eventId));
        },
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          children: [
            eventAsync.when(
              loading: () => const Card(
                child: Padding(
                  padding: EdgeInsets.all(20),
                  child: Center(child: CircularProgressIndicator()),
                ),
              ),
              error: (error, _) => Text(error.toString()),
              data: _buildSummary,
            ),
            const SizedBox(height: 16),
            Text('Convocados', style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 8),
            participantsAsync.when(
              loading: () => const Card(
                child: Padding(
                  padding: EdgeInsets.all(16),
                  child: Center(child: CircularProgressIndicator()),
                ),
              ),
              error: (error, _) => Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Text(error.toString()),
                ),
              ),
              data: (participants) {
                if (participants.isEmpty) {
                  return const Card(
                    child: Padding(
                      padding: EdgeInsets.all(16),
                      child: Text('Sem convocados para este evento.'),
                    ),
                  );
                }

                return Card(
                  child: Column(
                    children: participants
                        .map(
                          (participant) => ListTile(
                            title: Text(
                              participant.fullName.isEmpty
                                  ? 'Atleta #${participant.athleteId}'
                                  : participant.fullName,
                            ),
                            trailing: _StatusBadge(
                              status: participant.invitationStatus,
                            ),
                          ),
                        )
                        .toList(),
                  ),
                );
              },
            ),
            const SizedBox(height: 24),
            if (canManageAttendance)
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: () =>
                      context.push('/home/agenda/event/$eventId/attendance'),
                  icon: const Icon(Icons.sports_soccer_outlined),
                  label: const Text('Modo campo (Presença)'),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildSummary(Event event) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              event.title,
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w700),
            ),
            const SizedBox(height: 8),
            Text('Tipo: ${_eventTypeLabel(event.type)}'),
            Text('Data/Hora: ${_formatDateTime(event.startDateTime)}'),
            Text(
              'Local: ${event.location?.isNotEmpty == true ? event.location : '-'}',
            ),
            Text('Status: ${event.status}'),
          ],
        ),
      ),
    );
  }

  String _formatDateTime(DateTime? dateTime) {
    if (dateTime == null) {
      return '--';
    }

    final dd = dateTime.day.toString().padLeft(2, '0');
    final mm = dateTime.month.toString().padLeft(2, '0');
    final yyyy = dateTime.year.toString();
    final hh = dateTime.hour.toString().padLeft(2, '0');
    final min = dateTime.minute.toString().padLeft(2, '0');
    return '$dd/$mm/$yyyy $hh:$min';
  }

  String _eventTypeLabel(String type) {
    switch (type.toUpperCase()) {
      case 'TRAINING':
        return 'Treino';
      case 'MATCH':
        return 'Jogo';
      default:
        return type;
    }
  }
}

class _StatusBadge extends StatelessWidget {
  final String status;

  const _StatusBadge({required this.status});

  @override
  Widget build(BuildContext context) {
    final normalized = status.toLowerCase();
    Color background;
    Color foreground;
    String label;

    switch (normalized) {
      case 'confirmed':
        background = Colors.green.shade100;
        foreground = Colors.green.shade900;
        label = 'Confirmado';
        break;
      case 'declined':
        background = Colors.red.shade100;
        foreground = Colors.red.shade900;
        label = 'Recusado';
        break;
      case 'pending':
        background = Colors.orange.shade100;
        foreground = Colors.orange.shade900;
        label = 'Pendente';
        break;
      default:
        background = Colors.blueGrey.shade100;
        foreground = Colors.blueGrey.shade900;
        label = 'Convidado';
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: background,
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        label,
        style: TextStyle(
          color: foreground,
          fontSize: 12,
          fontWeight: FontWeight.w700,
        ),
      ),
    );
  }
}
