import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../domain/models/event.dart';
import '../state/agenda_providers.dart';

class AgendaScreen extends ConsumerWidget {
  const AgendaScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final range = ref.watch(agendaRangeProvider);
    final eventsAsync = ref.watch(agendaEventsProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Agenda')),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
            child: SegmentedButton<AgendaRange>(
              segments: const [
                ButtonSegment(
                  value: AgendaRange.today,
                  icon: Icon(Icons.today_outlined),
                  label: Text('Hoje'),
                ),
                ButtonSegment(
                  value: AgendaRange.week,
                  icon: Icon(Icons.view_week_outlined),
                  label: Text('Semana'),
                ),
              ],
              selected: {range},
              onSelectionChanged: (selected) {
                ref.read(agendaRangeProvider.notifier).state = selected.first;
              },
            ),
          ),
          Expanded(
            child: RefreshIndicator(
              onRefresh: () async {
                ref.invalidate(agendaEventsProvider);
              },
              child: eventsAsync.when(
                loading: () => const Center(child: CircularProgressIndicator()),
                error: (error, _) => _ErrorState(
                  message: error.toString().replaceAll('Exception: ', ''),
                  onRetry: () => ref.invalidate(agendaEventsProvider),
                ),
                data: (events) {
                  if (events.isEmpty) {
                    return const _EmptyAgenda();
                  }

                  return ListView.separated(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                    itemBuilder: (context, index) {
                      final event = events[index];
                      return _EventCard(
                        event: event,
                        onTap: () =>
                            context.push('/home/agenda/event/${event.id}'),
                      );
                    },
                    separatorBuilder: (_, index) => const SizedBox(height: 12),
                    itemCount: events.length,
                  );
                },
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _EventCard extends ConsumerWidget {
  final Event event;
  final VoidCallback onTap;

  const _EventCard({required this.event, required this.onTap});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final participantsAsync = ref.watch(eventParticipantsProvider(event.id));

    return Card(
      child: InkWell(
        borderRadius: BorderRadius.circular(16),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  _TypeBadge(type: event.type),
                  const Spacer(),
                  Text(
                    _formatDateTime(event.startDateTime),
                    style: Theme.of(
                      context,
                    ).textTheme.labelMedium?.copyWith(color: Colors.black54),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Text(event.title, style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 6),
              Text(
                event.location?.isNotEmpty == true
                    ? event.location!
                    : 'Local não informado',
                style: Theme.of(
                  context,
                ).textTheme.bodyMedium?.copyWith(color: Colors.black54),
              ),
              const SizedBox(height: 8),
              participantsAsync.when(
                loading: () => const SizedBox(
                  height: 16,
                  child: LinearProgressIndicator(minHeight: 2),
                ),
                error: (_, stackTrace) => Text(
                  'Convocados indisponíveis',
                  style: Theme.of(
                    context,
                  ).textTheme.labelSmall?.copyWith(color: Colors.black54),
                ),
                data: (participants) {
                  final total = participants.length;
                  final confirmed = participants
                      .where((p) => p.invitationStatus == 'confirmed')
                      .length;
                  return Text(
                    '$total convocados / $confirmed confirmados',
                    style: Theme.of(context).textTheme.labelMedium?.copyWith(
                      fontWeight: FontWeight.w600,
                    ),
                  );
                },
              ),
            ],
          ),
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
}

class _TypeBadge extends StatelessWidget {
  final String type;

  const _TypeBadge({required this.type});

  @override
  Widget build(BuildContext context) {
    final normalized = type.toUpperCase();
    final isMatch = normalized == 'MATCH';
    final color = isMatch ? Colors.orange.shade100 : Colors.green.shade100;
    final textColor = isMatch ? Colors.orange.shade900 : Colors.green.shade900;
    final label = isMatch
        ? 'JOGO'
        : normalized == 'TRAINING'
        ? 'TREINO'
        : normalized;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(99),
      ),
      child: Text(
        label,
        style: TextStyle(
          color: textColor,
          fontWeight: FontWeight.w700,
          fontSize: 12,
        ),
      ),
    );
  }
}

class _EmptyAgenda extends StatelessWidget {
  const _EmptyAgenda();

  @override
  Widget build(BuildContext context) {
    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      children: const [
        SizedBox(height: 80),
        Icon(Icons.event_busy_outlined, size: 42, color: Colors.black45),
        SizedBox(height: 10),
        Center(child: Text('Sem eventos no período.')),
      ],
    );
  }
}

class _ErrorState extends StatelessWidget {
  final String message;
  final VoidCallback onRetry;

  const _ErrorState({required this.message, required this.onRetry});

  @override
  Widget build(BuildContext context) {
    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.all(24),
      children: [
        const Icon(Icons.error_outline, size: 36, color: Colors.redAccent),
        const SizedBox(height: 8),
        Text(message, textAlign: TextAlign.center),
        const SizedBox(height: 16),
        ElevatedButton(
          onPressed: onRetry,
          child: const Text('Tentar novamente'),
        ),
      ],
    );
  }
}
