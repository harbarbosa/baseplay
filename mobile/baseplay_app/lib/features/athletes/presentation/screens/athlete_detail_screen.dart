import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../domain/models/attendance_history_model.dart';
import '../../domain/models/athlete_model.dart';
import '../../domain/models/athlete_summary_model.dart';
import '../../../../presentation/state/providers.dart';
import '../../../../presentation/widgets/team_selector_action.dart';
import '../state/athletes_providers.dart';
import '../../../documents/presentation/state/documents_providers.dart';
import '../../../documents/domain/models/document_model.dart';
import '../../../../core/auth/permissions.dart';

class AthleteDetailScreen extends ConsumerWidget {
  final int athleteId;

  const AthleteDetailScreen({super.key, required this.athleteId});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final athleteAsync = ref.watch(athleteDetailProvider(athleteId));
    final summaryAsync = ref.watch(athleteSummaryProvider(athleteId));
    final canViewDocuments = ref
            .watch(authUserProvider)
            ?.hasPermission(Permissions.documentsViewTeam) ??
        false;
    final canUploadDocuments = ref
            .watch(authUserProvider)
            ?.hasPermission(Permissions.documentsUpload) ??
        false;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Perfil do atleta'),
        actions: const [TeamSelectorAction()],
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(athleteDetailProvider(athleteId));
          ref.invalidate(athleteSummaryProvider(athleteId));
          ref.invalidate(documentsByAthleteProvider(athleteId));
        },
        child: DefaultTabController(
          length: canViewDocuments ? 2 : 1,
          child: Column(
            children: [
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 12, 16, 0),
                child: athleteAsync.when(
                  loading: () => const _LoadingCard(),
                  error: (error, stackTrace) =>
                      _ErrorCard(message: error.toString()),
                  data: (athlete) => _AthleteHeader(athlete: athlete),
                ),
              ),
              const SizedBox(height: 8),
              if (canViewDocuments)
                const TabBar(
                  tabs: [
                    Tab(text: 'Resumo'),
                    Tab(text: 'Documentos'),
                  ],
                )
              else
                const SizedBox(height: 8),
              Expanded(
                child: TabBarView(
                  children: [
                    ListView(
                      physics: const AlwaysScrollableScrollPhysics(),
                      padding: const EdgeInsets.all(16),
                      children: [
                        summaryAsync.when(
                          loading: () => const _LoadingCard(),
                          error: (error, stackTrace) =>
                              _ErrorCard(message: error.toString()),
                          data: (summary) => _SummaryContent(summary: summary),
                        ),
                      ],
                    ),
                    if (canViewDocuments)
                      _DocumentsTab(
                        athleteId: athleteId,
                        canUpload: canUploadDocuments,
                      ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _AthleteHeader extends StatelessWidget {
  final AthleteModel athlete;

  const _AthleteHeader({required this.athlete});

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            CircleAvatar(
              radius: 28,
              child: Text(
                athlete.firstName.isNotEmpty
                    ? athlete.firstName.characters.first.toUpperCase()
                    : 'A',
                style: const TextStyle(
                  fontSize: 22,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    athlete.fullName,
                    style: const TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    '${athlete.teamName ?? '-'} | ${athlete.categoryName ?? '-'}',
                  ),
                  const SizedBox(height: 4),
                  Text('Status: ${athlete.status}'),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _SummaryContent extends StatelessWidget {
  final AthleteSummaryModel summary;

  const _SummaryContent({required this.summary});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        _CardsGrid(summary: summary),
        const SizedBox(height: 16),
        Card(
          child: Padding(
            padding: const EdgeInsets.all(14),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Histórico de Presença',
                  style: TextStyle(fontWeight: FontWeight.w700, fontSize: 16),
                ),
                const SizedBox(height: 10),
                if (summary.attendanceHistory.isEmpty)
                  const Text('Sem registros recentes.')
                else
                  ...summary.attendanceHistory.map(
                    (item) => _HistoryRow(item: item),
                  ),
              ],
            ),
          ),
        ),
      ],
    );
  }
}

class _CardsGrid extends StatelessWidget {
  final AthleteSummaryModel summary;

  const _CardsGrid({required this.summary});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Row(
          children: [
            Expanded(
              child: _MiniCard(
                title: 'Presença',
                value: '${summary.presencePercentage.toStringAsFixed(1)}%',
                subtitle:
                    '${summary.totalSessions} sessões | ${summary.absences} faltas',
                icon: Icons.fact_check_outlined,
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _MiniCard(
                title: 'Última atividade',
                value: summary.lastTrainingDate != null
                    ? _formatDate(summary.lastTrainingDate)
                    : '--',
                subtitle: summary.lastMatchDate != null
                    ? 'Jogo: ${_formatDate(summary.lastMatchDate)}'
                    : 'Jogo: --',
                icon: Icons.history_toggle_off,
              ),
            ),
          ],
        ),
        const SizedBox(height: 10),
        Row(
          children: [
            Expanded(
              child: _MiniCard(
                title: 'Documentos',
                value: '${summary.documentsActive} ativos',
                subtitle:
                    '${summary.documentsExpired} vencidos | ${summary.documentsExpiringSoon} a vencer',
                icon: Icons.folder_copy_outlined,
                warning:
                    summary.documentsExpired > 0 ||
                    summary.documentsExpiringSoon > 0,
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _MiniCard(
                title: 'Próximo evento',
                value: summary.nextEventType == null
                    ? '--'
                    : _eventType(summary.nextEventType!),
                subtitle: summary.nextEventDate != null
                    ? _formatDateTime(summary.nextEventDate!)
                    : 'Sem agenda',
                icon: Icons.event_available_outlined,
              ),
            ),
          ],
        ),
      ],
    );
  }

  static String _eventType(String type) {
    final normalized = type.toUpperCase();
    if (normalized == 'TRAINING') {
      return 'Treino';
    }
    if (normalized == 'MATCH') {
      return 'Jogo';
    }
    return normalized;
  }

  static String _formatDate(DateTime? date) {
    if (date == null) {
      return '--';
    }
    final d = date.day.toString().padLeft(2, '0');
    final m = date.month.toString().padLeft(2, '0');
    final y = date.year.toString();
    return '$d/$m/$y';
  }

  static String _formatDateTime(DateTime date) {
    final d = _formatDate(date);
    final hh = date.hour.toString().padLeft(2, '0');
    final mm = date.minute.toString().padLeft(2, '0');
    return '$d $hh:$mm';
  }
}

class _MiniCard extends StatelessWidget {
  final String title;
  final String value;
  final String subtitle;
  final IconData icon;
  final bool warning;

  const _MiniCard({
    required this.title,
    required this.value,
    required this.subtitle,
    required this.icon,
    this.warning = false,
  });

  @override
  Widget build(BuildContext context) {
    final color = warning
        ? Colors.orange.shade800
        : Theme.of(context).colorScheme.primary;

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(icon, size: 18, color: color),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(
                    title,
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                      color: color,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text(
              value,
              style: const TextStyle(fontSize: 21, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 4),
            Text(
              subtitle,
              style: const TextStyle(fontSize: 12, color: Colors.black54),
            ),
          ],
        ),
      ),
    );
  }
}

class _HistoryRow extends StatelessWidget {
  final AttendanceHistoryModel item;

  const _HistoryRow({required this.item});

  @override
  Widget build(BuildContext context) {
    return ListTile(
      dense: true,
      contentPadding: EdgeInsets.zero,
      title: Text(item.eventTitle),
      subtitle: Text(
        '${_typeLabel(item.eventType)} • ${_formatDateTime(item.eventDate)}',
      ),
      trailing: _StatusBadge(status: item.status),
    );
  }

  static String _typeLabel(String type) {
    final up = type.toUpperCase();
    if (up == 'TRAINING') {
      return 'Treino';
    }
    if (up == 'MATCH') {
      return 'Jogo';
    }
    return up;
  }

  static String _formatDateTime(DateTime? date) {
    if (date == null) {
      return '--';
    }
    final d = date.day.toString().padLeft(2, '0');
    final m = date.month.toString().padLeft(2, '0');
    final y = date.year.toString();
    return '$d/$m/$y';
  }
}

class _StatusBadge extends StatelessWidget {
  final String status;

  const _StatusBadge({required this.status});

  @override
  Widget build(BuildContext context) {
    final normalized = status.toLowerCase();
    late final Color background;
    late final Color foreground;
    late final String label;

    switch (normalized) {
      case 'present':
        background = Colors.green.shade100;
        foreground = Colors.green.shade900;
        label = 'Presente';
        break;
      case 'late':
        background = Colors.orange.shade100;
        foreground = Colors.orange.shade900;
        label = 'Atraso';
        break;
      case 'justified':
        background = Colors.blue.shade100;
        foreground = Colors.blue.shade900;
        label = 'Justificada';
        break;
      default:
        background = Colors.red.shade100;
        foreground = Colors.red.shade900;
        label = 'Falta';
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: background,
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        label,
        style: TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.w700,
          color: foreground,
        ),
      ),
    );
  }
}

class _LoadingCard extends StatelessWidget {
  const _LoadingCard();

  @override
  Widget build(BuildContext context) {
    return const Card(
      child: Padding(
        padding: EdgeInsets.all(20),
        child: Center(child: CircularProgressIndicator()),
      ),
    );
  }
}

class _ErrorCard extends StatelessWidget {
  final String message;

  const _ErrorCard({required this.message});

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(padding: const EdgeInsets.all(16), child: Text(message)),
    );
  }
}

class _DocumentsTab extends ConsumerWidget {
  final int athleteId;
  final bool canUpload;

  const _DocumentsTab({
    required this.athleteId,
    required this.canUpload,
  });

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final docsAsync = ref.watch(documentsByAthleteProvider(athleteId));

    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.all(16),
      children: [
        if (canUpload)
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              onPressed: () =>
                  context.push('/home/documents/athlete/$athleteId/upload'),
              icon: const Icon(Icons.upload_file_outlined),
              label: const Text('Upload de documento'),
            ),
          ),
        if (canUpload) const SizedBox(height: 12),
        docsAsync.when(
          loading: () => const Card(
            child: Padding(
              padding: EdgeInsets.all(20),
              child: Center(child: CircularProgressIndicator()),
            ),
          ),
          error: (error, stackTrace) => Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Text(error.toString()),
            ),
          ),
          data: (docs) {
            if (docs.isEmpty) {
              return const Card(
                child: Padding(
                  padding: EdgeInsets.all(16),
                  child: Text('Nenhum documento enviado para este atleta.'),
                ),
              );
            }

            final sorted = [...docs]
              ..sort((a, b) {
                final aTs =
                    a.expiresAt?.millisecondsSinceEpoch ??
                    DateTime(9999).millisecondsSinceEpoch;
                final bTs =
                    b.expiresAt?.millisecondsSinceEpoch ??
                    DateTime(9999).millisecondsSinceEpoch;
                return aTs.compareTo(bTs);
              });

            return Column(
              children:
                  sorted.map((doc) => _DocumentTile(document: doc)).toList(),
            );
          },
        ),
      ],
    );
  }
}

class _DocumentTile extends StatelessWidget {
  final DocumentModel document;

  const _DocumentTile({required this.document});

  @override
  Widget build(BuildContext context) {
    final status = _statusInfo(document);
    return Card(
      margin: const EdgeInsets.only(bottom: 10),
      child: ListTile(
        title: Text(document.typeName ?? 'Documento'),
        subtitle: Text(
          'Arquivo: ${document.originalName ?? '-'}\nVencimento: ${_formatDate(document.expiresAt)}',
        ),
        isThreeLine: true,
        trailing: Container(
          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
          decoration: BoxDecoration(
            color: status.color.withValues(alpha: 0.15),
            borderRadius: BorderRadius.circular(999),
          ),
          child: Text(
            status.label,
            style: TextStyle(
              color: status.color,
              fontSize: 11,
              fontWeight: FontWeight.w700,
            ),
          ),
        ),
      ),
    );
  }

  ({String label, Color color}) _statusInfo(DocumentModel doc) {
    if (doc.isExpired) {
      return (label: 'Vencido', color: Colors.red);
    }
    if (doc.isExpiringSoon) {
      return (label: 'Vencendo', color: Colors.orange);
    }
    return (label: 'OK', color: Colors.green);
  }

  String _formatDate(DateTime? value) {
    if (value == null) {
      return '-';
    }
    final d = value.day.toString().padLeft(2, '0');
    final m = value.month.toString().padLeft(2, '0');
    final y = value.year.toString();
    return '$d/$m/$y';
  }
}

