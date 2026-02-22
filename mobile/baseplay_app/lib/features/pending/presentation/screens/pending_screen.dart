import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../domain/pending_item.dart';
import '../state/pending_providers.dart';

class PendingScreen extends ConsumerWidget {
  const PendingScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final pendingAsync = ref.watch(pendingItemsProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Central de Pendências')),
      body: RefreshIndicator(
        onRefresh: () async => ref.invalidate(pendingItemsProvider),
        child: pendingAsync.when(
          loading: () => const Center(child: CircularProgressIndicator()),
          error: (error, _) => ListView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.all(24),
            children: [
              Text(error.toString(), textAlign: TextAlign.center),
              const SizedBox(height: 12),
              ElevatedButton(
                onPressed: () => ref.invalidate(pendingItemsProvider),
                child: const Text('Tentar novamente'),
              ),
            ],
          ),
          data: (items) {
            if (items.isEmpty) {
              return ListView(
                physics: const AlwaysScrollableScrollPhysics(),
                children: const [
                  SizedBox(height: 80),
                  Icon(Icons.check_circle_outline,
                      size: 42, color: Colors.black45),
                  SizedBox(height: 10),
                  Center(child: Text('Sem pendências no momento.')),
                ],
              );
            }

            final grouped = _groupByType(items);

            return ListView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.all(16),
              children: [
                if (grouped[PendingType.documents]?.isNotEmpty == true)
                  _Section(
                    title: 'Documentos',
                    items: grouped[PendingType.documents]!,
                  ),
                if (grouped[PendingType.events]?.isNotEmpty == true)
                  _Section(
                    title: 'Eventos',
                    items: grouped[PendingType.events]!,
                  ),
                if (grouped[PendingType.notices]?.isNotEmpty == true)
                  _Section(
                    title: 'Avisos',
                    items: grouped[PendingType.notices]!,
                  ),
              ],
            );
          },
        ),
      ),
    );
  }

  Map<PendingType, List<PendingItem>> _groupByType(List<PendingItem> items) {
    final map = <PendingType, List<PendingItem>>{};
    for (final item in items) {
      map.putIfAbsent(item.type, () => []).add(item);
    }
    return map;
  }
}

class _Section extends ConsumerWidget {
  final String title;
  final List<PendingItem> items;

  const _Section({required this.title, required this.items});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(title, style: Theme.of(context).textTheme.titleMedium),
        const SizedBox(height: 8),
        ...items.map((item) => _PendingTile(item: item)),
        const SizedBox(height: 16),
      ],
    );
  }
}

class _PendingTile extends ConsumerWidget {
  final PendingItem item;

  const _PendingTile({required this.item});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final color = _priorityColor(item.priority);
    return Card(
      margin: const EdgeInsets.only(bottom: 10),
      child: ListTile(
        title: Text(item.title),
        subtitle: Text(item.description),
        leading: CircleAvatar(
          backgroundColor: color.withValues(alpha: 0.12),
          child: Icon(_iconForType(item.type), color: color),
        ),
        trailing: TextButton(
          onPressed: () => _handleAction(context, ref),
          child: Text(item.ctaLabel),
        ),
      ),
    );
  }

  Future<void> _handleAction(BuildContext context, WidgetRef ref) async {
    switch (item.actionType) {
      case PendingActionType.openRoute:
        final route = item.route;
        if (route != null) {
          context.push(route);
        }
        return;
      case PendingActionType.confirmEvent:
        final eventId =
            int.tryParse('${item.actionPayload?['eventId'] ?? ''}') ?? 0;
        if (eventId <= 0) return;
        await ref
            .read(pendingActionControllerProvider)
            .confirmEvent(eventId);
        if (!context.mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Evento confirmado.')),
        );
        return;
    }
  }

  IconData _iconForType(PendingType type) {
    switch (type) {
      case PendingType.documents:
        return Icons.folder_copy_outlined;
      case PendingType.events:
        return Icons.event_available_outlined;
      case PendingType.notices:
        return Icons.campaign_outlined;
    }
  }

  Color _priorityColor(PendingPriority priority) {
    switch (priority) {
      case PendingPriority.high:
        return Colors.red;
      case PendingPriority.medium:
        return Colors.orange;
      case PendingPriority.low:
        return Colors.blueGrey;
    }
  }
}
