import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../../presentation/state/providers.dart';
import '../state/dashboard_providers.dart';

class AppDashboardScreen extends ConsumerWidget {
  const AppDashboardScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final dashboardAsync = ref.watch(dashboardSummaryProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Painel')),
      body: RefreshIndicator(
        onRefresh: () async => ref.invalidate(dashboardSummaryProvider),
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          children: [
            dashboardAsync.when(
              loading: () => const _LoadingCard(),
              error: (error, stackTrace) => _ErrorCard(message: error.toString()),
              data: (summary) {
                final kpis = summary.kpis;
                return Column(
                  children: [
                    _ProfileHeader(profile: summary.profile),
                    const SizedBox(height: 12),
                    GridView.count(
                      crossAxisCount: 2,
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      crossAxisSpacing: 10,
                      mainAxisSpacing: 10,
                      childAspectRatio: 1.45,
                      children: [
                        _KpiCard(title: 'Alertas pendentes', value: '${kpis['systemAlertUnread'] ?? 0}', icon: Icons.warning_amber_rounded),
                        _KpiCard(title: 'Presença média', value: '${kpis['attendancePct'] ?? 0}%', icon: Icons.percent_rounded),
                        _KpiCard(title: 'Documentos pendentes', value: '${kpis['documentsPending'] ?? kpis['docsExpired'] ?? 0}', icon: Icons.description_outlined),
                        _KpiCard(title: 'Baixa presença', value: '${kpis['lowAttendanceCount'] ?? 0}', icon: Icons.trending_down),
                      ],
                    ),
                  ],
                );
              },
            ),
            const SizedBox(height: 12),
            Card(
              child: ListTile(
                leading: const Icon(Icons.rule_folder_outlined),
                title: const Text('Central de Pendências'),
                subtitle: const Text('Documentos vencidos, obrigatórios e eventos sem convocação'),
                trailing: const Icon(Icons.chevron_right),
                onTap: () => context.push('/home/profile/pending-center'),
              ),
            ),
            const SizedBox(height: 8),
            Card(
              child: ListTile(
                leading: const Icon(Icons.logout),
                title: const Text('Sair'),
                onTap: () async {
                  await ref.read(authControllerProvider.notifier).logout();
                  if (context.mounted) {
                    context.go('/login');
                  }
                },
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class PendingCenterScreen extends ConsumerWidget {
  const PendingCenterScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final pendingAsync = ref.watch(pendingCenterSummaryProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Central de Pendências')),
      body: RefreshIndicator(
        onRefresh: () async => ref.invalidate(pendingCenterSummaryProvider),
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          children: [
            pendingAsync.when(
              loading: () => const _LoadingCard(),
              error: (error, stackTrace) => _ErrorCard(message: error.toString()),
              data: (summary) => Column(
                children: [
                  _PendingTile(
                    title: 'Documentos vencidos',
                    value: summary.expiredDocuments,
                    color: Colors.red,
                  ),
                  _PendingTile(
                    title: 'Documentos a vencer (30 dias)',
                    value: summary.expiringDocuments,
                    color: Colors.orange,
                  ),
                  _PendingTile(
                    title: 'Sem documento obrigatório',
                    value: summary.missingRequiredDocuments,
                    color: Colors.blueGrey,
                  ),
                  _PendingTile(
                    title: 'Eventos sem convocação',
                    value: summary.upcomingEventsWithoutCallups,
                    color: Colors.purple,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ProfileHeader extends StatelessWidget {
  final String profile;

  const _ProfileHeader({required this.profile});

  @override
  Widget build(BuildContext context) {
    String text;
    switch (profile) {
      case 'treinador':
        text = 'Painel do treinador';
        break;
      case 'auxiliar':
        text = 'Painel do auxiliar';
        break;
      case 'atleta':
        text = 'Painel do atleta/responsável';
        break;
      default:
        text = 'Painel do coordenador';
    }

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Row(
          children: [
            const Icon(Icons.sports_soccer, size: 28),
            const SizedBox(width: 10),
            Expanded(child: Text(text, style: Theme.of(context).textTheme.titleMedium)),
          ],
        ),
      ),
    );
  }
}

class _KpiCard extends StatelessWidget {
  final String title;
  final String value;
  final IconData icon;

  const _KpiCard({required this.title, required this.value, required this.icon});

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Icon(icon, size: 18),
            const Spacer(),
            Text(value, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 22)),
            const SizedBox(height: 2),
            Text(title, style: const TextStyle(fontSize: 12, color: Colors.black54)),
          ],
        ),
      ),
    );
  }
}

class _PendingTile extends StatelessWidget {
  final String title;
  final int value;
  final Color color;

  const _PendingTile({required this.title, required this.value, required this.color});

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: const EdgeInsets.only(bottom: 10),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: color.withValues(alpha: 0.12),
          child: Text(
            '$value',
            style: TextStyle(color: color, fontWeight: FontWeight.bold, fontSize: 12),
          ),
        ),
        title: Text(title),
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
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Text(message),
      ),
    );
  }
}
