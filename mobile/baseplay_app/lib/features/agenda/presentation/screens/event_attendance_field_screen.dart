import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../domain/models/attendance.dart';
import '../../domain/models/participant.dart';
import '../state/agenda_providers.dart';
import '../../../../presentation/widgets/team_selector_action.dart';
import '../../../../core/offline/outbox_provider.dart';
import '../../../../core/offline/outbox_item.dart';

class EventAttendanceFieldScreen extends ConsumerStatefulWidget {
  final int eventId;

  const EventAttendanceFieldScreen({super.key, required this.eventId});

  @override
  ConsumerState<EventAttendanceFieldScreen> createState() =>
      _EventAttendanceFieldScreenState();
}

class _EventAttendanceFieldScreenState
    extends ConsumerState<EventAttendanceFieldScreen> {
  final Map<int, String> _statusByAthlete = <int, String>{};
  final Map<int, AttendanceSaveStatus> _syncByAthlete =
      <int, AttendanceSaveStatus>{};
  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    Future.microtask(
      () => ref.read(outboxControllerProvider.notifier).processQueue(),
    );
  }

  @override
  Widget build(BuildContext context) {
    final participantsAsync = ref.watch(
      eventParticipantsProvider(widget.eventId),
    );
    final attendanceAsync = ref.watch(eventAttendanceProvider(widget.eventId));
    final outboxItems = ref.watch(outboxControllerProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Modo campo'),
        actions: [
          const TeamSelectorAction(),
          IconButton(
            icon: const Icon(Icons.sync),
            tooltip: 'Sincronizar',
            onPressed: () async {
              await ref.read(outboxControllerProvider.notifier).processQueue();
              if (!mounted) return;
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Sincronização concluída.')),
              );
            },
          ),
          IconButton(
            icon: const Icon(Icons.refresh),
            tooltip: 'Recarregar',
            onPressed: () {
              ref.invalidate(eventParticipantsProvider(widget.eventId));
              ref.invalidate(eventAttendanceProvider(widget.eventId));
            },
          ),
        ],
      ),
      body: participantsAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (error, _) => Center(child: Text(error.toString())),
        data: (participants) {
          if (participants.isEmpty) {
            return const Center(child: Text('Sem atletas convocados.'));
          }

          final attendance =
              attendanceAsync.valueOrNull ?? const <Attendance>[];
          _hydrateFromAttendance(attendance);
          _hydrateFromOutbox(outboxItems);

          return ListView.builder(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
            itemCount: participants.length,
            itemBuilder: (context, index) {
              final participant = participants[index];
              final status =
                  _statusByAthlete[participant.athleteId] ?? 'absent';
              final syncStatus = _syncByAthlete[participant.athleteId];
              return _AthleteAttendanceCard(
                participant: participant,
                status: status,
                syncStatus: syncStatus,
                disabled: _isSubmitting,
                onStatusTap: (value) => _saveAttendance(participant, value),
              );
            },
          );
        },
      ),
      bottomNavigationBar: SafeArea(
        minimum: const EdgeInsets.all(16),
        child: FilledButton.tonal(
          onPressed: _isSubmitting
              ? null
              : () => Navigator.of(context).maybePop(),
          child: const Text('Concluir'),
        ),
      ),
    );
  }

  void _hydrateFromAttendance(List<Attendance> attendance) {
    for (final item in attendance) {
      _statusByAthlete[item.athleteId] = item.status;
    }
  }

  void _hydrateFromOutbox(List<OutboxItem> items) {
    final pendingForEvent = <int>{};
    for (final item in items) {
      if (item.type != 'attendance') {
        continue;
      }
      final eventId = int.tryParse('${item.payload['eventId'] ?? ''}') ?? 0;
      final athleteId =
          int.tryParse('${item.payload['athleteId'] ?? ''}') ?? 0;
      if (eventId != widget.eventId || athleteId <= 0) {
        continue;
      }
      pendingForEvent.add(athleteId);
      if (item.status == OutboxStatus.error) {
        _syncByAthlete[athleteId] = AttendanceSaveStatus.error;
      } else {
        _syncByAthlete[athleteId] = AttendanceSaveStatus.pending;
      }
    }

    final keys = _syncByAthlete.keys.toList();
    for (final athleteId in keys) {
      final status = _syncByAthlete[athleteId];
      if ((status == AttendanceSaveStatus.pending ||
              status == AttendanceSaveStatus.error) &&
          !pendingForEvent.contains(athleteId)) {
        _syncByAthlete[athleteId] = AttendanceSaveStatus.synced;
      }
    }
  }

  Future<void> _saveAttendance(Participant participant, String status) async {
    final previous = _statusByAthlete[participant.athleteId];

    setState(() {
      _statusByAthlete[participant.athleteId] = status;
      _isSubmitting = true;
    });

    try {
      final result =
          await ref.read(eventAttendanceControllerProvider).save(
                eventId: widget.eventId,
                athleteId: participant.athleteId,
                status: status,
              );
      _syncByAthlete[participant.athleteId] = result;

      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            result == AttendanceSaveStatus.pending
                ? '${participant.fullName} salvo como pendente.'
                : '${participant.fullName} marcado como ${_statusLabel(status)}.',
          ),
          duration: const Duration(milliseconds: 900),
        ),
      );
    } catch (error) {
      setState(() {
        if (previous != null) {
          _statusByAthlete[participant.athleteId] = previous;
        } else {
          _statusByAthlete.remove(participant.athleteId);
        }
      });

      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(error.toString().replaceAll('Exception: ', '')),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      if (mounted) {
        setState(() => _isSubmitting = false);
      }
    }
  }

  String _statusLabel(String status) {
    switch (status) {
      case 'present':
        return 'Presente';
      case 'late':
        return 'Atraso';
      case 'justified':
        return 'Justificou';
      default:
        return 'Faltou';
    }
  }
}

class _AthleteAttendanceCard extends StatelessWidget {
  final Participant participant;
  final String status;
  final AttendanceSaveStatus? syncStatus;
  final bool disabled;
  final ValueChanged<String> onStatusTap;

  const _AthleteAttendanceCard({
    required this.participant,
    required this.status,
    required this.syncStatus,
    required this.disabled,
    required this.onStatusTap,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              participant.fullName.isEmpty
                  ? 'Atleta #${participant.athleteId}'
                  : participant.fullName,
              style: const TextStyle(fontWeight: FontWeight.w700),
            ),
            if (syncStatus != null) ...[
              const SizedBox(height: 6),
              _SyncStatusChip(status: syncStatus!),
            ],
            const SizedBox(height: 10),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                _StatusButton(
                  label: 'Presente',
                  statusValue: 'present',
                  currentStatus: status,
                  color: Colors.green,
                  disabled: disabled,
                  onTap: onStatusTap,
                ),
                _StatusButton(
                  label: 'Atraso',
                  statusValue: 'late',
                  currentStatus: status,
                  color: Colors.orange,
                  disabled: disabled,
                  onTap: onStatusTap,
                ),
                _StatusButton(
                  label: 'Faltou',
                  statusValue: 'absent',
                  currentStatus: status,
                  color: Colors.red,
                  disabled: disabled,
                  onTap: onStatusTap,
                ),
                _StatusButton(
                  label: 'Justificou',
                  statusValue: 'justified',
                  currentStatus: status,
                  color: Colors.blue,
                  disabled: disabled,
                  onTap: onStatusTap,
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _StatusButton extends StatelessWidget {
  final String label;
  final String statusValue;
  final String currentStatus;
  final Color color;
  final bool disabled;
  final ValueChanged<String> onTap;

  const _StatusButton({
    required this.label,
    required this.statusValue,
    required this.currentStatus,
    required this.color,
    required this.disabled,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final selected = statusValue == currentStatus;
    return SizedBox(
      width: 145,
      height: 44,
      child: ElevatedButton(
        onPressed: disabled ? null : () => onTap(statusValue),
        style: ElevatedButton.styleFrom(
          backgroundColor: selected ? color : color.withValues(alpha: 0.15),
          foregroundColor: selected ? Colors.white : color,
          textStyle: const TextStyle(fontWeight: FontWeight.w700),
        ),
        child: Text(label),
      ),
    );
  }
}

class _SyncStatusChip extends StatelessWidget {
  final AttendanceSaveStatus status;

  const _SyncStatusChip({required this.status});

  @override
  Widget build(BuildContext context) {
    String label;
    Color color;

    switch (status) {
      case AttendanceSaveStatus.pending:
        label = 'Pendente';
        color = Colors.orange;
        break;
      case AttendanceSaveStatus.error:
        label = 'Erro';
        color = Colors.red;
        break;
      case AttendanceSaveStatus.synced:
        label = 'Sincronizado';
        color = Colors.green;
        break;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.15),
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        label,
        style: TextStyle(
          color: color,
          fontSize: 11,
          fontWeight: FontWeight.w700,
        ),
      ),
    );
  }
}
