import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../athletes/domain/models/athlete_model.dart';
import '../state/documents_providers.dart';

class DocumentsOverviewScreen extends ConsumerWidget {
  const DocumentsOverviewScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final overviewAsync = ref.watch(documentsOverviewProvider);
    final athletesAsync = ref.watch(athletesForDocumentsProvider);
    final selectedCategory = ref.watch(documentsCategoryFilterProvider);

    final categoryOptions = athletesAsync.valueOrNull == null
        ? const <({int id, String label})>[]
        : _categoriesFromAthletes(athletesAsync.valueOrNull!);

    return Scaffold(
      appBar: AppBar(title: const Text('Documentos')),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(documentsOverviewProvider);
          ref.invalidate(athletesForDocumentsProvider);
        },
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          children: [
            DropdownButtonFormField<int?>(
              key: ValueKey(selectedCategory),
              initialValue: selectedCategory,
              isExpanded: true,
              decoration: const InputDecoration(labelText: 'Categoria'),
              items: [
                const DropdownMenuItem<int?>(value: null, child: Text('Todas')),
                ...categoryOptions.map(
                  (option) => DropdownMenuItem<int?>(
                    value: option.id,
                    child: Text(option.label),
                  ),
                ),
              ],
              onChanged: (value) {
                ref.read(documentsCategoryFilterProvider.notifier).state =
                    value;
              },
            ),
            const SizedBox(height: 14),
            if (athletesAsync.hasValue && overviewAsync.hasValue)
              _ComplianceCard(
                athletesTotal: athletesAsync.value!.length,
                missing: overviewAsync.value!.missing,
              ),
            if (athletesAsync.hasValue && overviewAsync.hasValue)
              const SizedBox(height: 12),
            overviewAsync.when(
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
              data: (overview) => Row(
                children: [
                  Expanded(
                    child: _OverviewCard(
                      title: 'Vencidos',
                      value: '${overview.expired}',
                      color: Colors.red,
                      icon: Icons.warning_amber_rounded,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _OverviewCard(
                      title: 'A vencer',
                      value: '${overview.expiring}',
                      color: Colors.orange,
                      icon: Icons.schedule,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _OverviewCard(
                      title: 'Faltando',
                      value: '${overview.missing}',
                      color: Colors.blueGrey,
                      icon: Icons.folder_off_outlined,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
            Text('Atletas', style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 8),
            athletesAsync.when(
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (error, stackTrace) => Text(error.toString()),
              data: (athletes) {
                if (athletes.isEmpty) {
                  return const Card(
                    child: Padding(
                      padding: EdgeInsets.all(16),
                      child: Text('Nenhum atleta encontrado para os filtros.'),
                    ),
                  );
                }

                return Column(
                  children: athletes
                      .map(
                        (athlete) => Card(
                          margin: const EdgeInsets.only(bottom: 8),
                          child: ListTile(
                            title: Text(athlete.fullName),
                            subtitle: Text(athlete.categoryName ?? '-'),
                            trailing: const Icon(Icons.chevron_right),
                            onTap: () => context.push(
                              '/home/documents/athlete/${athlete.id}',
                            ),
                          ),
                        ),
                      )
                      .toList(),
                );
              },
            ),
          ],
        ),
      ),
    );
  }

  List<({int id, String label})> _categoriesFromAthletes(
    List<AthleteModel> athletes,
  ) {
    final map = <int, String>{};
    for (final athlete in athletes) {
      final id = athlete.categoryId;
      if (id == null || id <= 0) {
        continue;
      }
      map[id] = athlete.categoryName ?? 'Categoria $id';
    }

    final list = map.entries.map((e) => (id: e.key, label: e.value)).toList();
    list.sort((a, b) => a.label.compareTo(b.label));
    return list;
  }
}

class _OverviewCard extends StatelessWidget {
  final String title;
  final String value;
  final Color color;
  final IconData icon;

  const _OverviewCard({
    required this.title,
    required this.value,
    required this.color,
    required this.icon,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(10),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Icon(icon, color: color, size: 18),
            const SizedBox(height: 8),
            Text(
              value,
              style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 2),
            Text(
              title,
              style: const TextStyle(fontSize: 12, color: Colors.black54),
            ),
          ],
        ),
      ),
    );
  }
}

class _ComplianceCard extends StatelessWidget {
  final int athletesTotal;
  final int missing;

  const _ComplianceCard({required this.athletesTotal, required this.missing});

  @override
  Widget build(BuildContext context) {
    final compliant = (athletesTotal - missing).clamp(0, athletesTotal);
    final percentage = athletesTotal == 0
        ? 0.0
        : (compliant / athletesTotal) * 100;

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Conformidade de documentos',
              style: TextStyle(fontWeight: FontWeight.w600),
            ),
            const SizedBox(height: 8),
            LinearProgressIndicator(value: athletesTotal == 0 ? 0 : compliant / athletesTotal),
            const SizedBox(height: 8),
            Text('${percentage.toStringAsFixed(1)}% ($compliant/$athletesTotal atletas)'),
          ],
        ),
      ),
    );
  }
}
