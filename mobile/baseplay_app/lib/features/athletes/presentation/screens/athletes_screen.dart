import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../data/athlete_repository.dart';
import '../../domain/models/athlete_model.dart';
import '../state/athletes_providers.dart';
import '../../../../presentation/widgets/team_selector_action.dart';
import '../../../../core/auth/permissions.dart';
import '../../../../presentation/state/providers.dart';

class AthletesScreen extends ConsumerStatefulWidget {
  const AthletesScreen({super.key});

  @override
  ConsumerState<AthletesScreen> createState() => _AthletesScreenState();
}

class _AthletesScreenState extends ConsumerState<AthletesScreen> {
  late final TextEditingController _searchController;

  @override
  void initState() {
    super.initState();
    _searchController = TextEditingController();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final search = ref.watch(athleteSearchProvider);
    final page = ref.watch(athletesPageProvider);
    final athletesAsync = ref.watch(
      athletesProvider((search: search, page: page)),
    );
    final canViewTeamDocuments =
        ref
            .watch(authUserProvider)
            ?.hasPermission(Permissions.documentsViewTeam) ??
        false;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Atletas'),
        actions: [
          const TeamSelectorAction(),
          if (canViewTeamDocuments)
            IconButton(
              tooltip: 'Documentos',
              onPressed: () => context.push('/home/documents'),
              icon: const Icon(Icons.folder_copy_outlined),
            ),
        ],
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
            child: TextField(
              controller: _searchController,
              decoration: InputDecoration(
                hintText: 'Buscar por nome',
                prefixIcon: const Icon(Icons.search),
                suffixIcon: search.isEmpty
                    ? null
                    : IconButton(
                        onPressed: () {
                          _searchController.clear();
                          _submitSearch('');
                        },
                        icon: const Icon(Icons.close),
                      ),
              ),
              textInputAction: TextInputAction.search,
              onSubmitted: _submitSearch,
            ),
          ),
          Expanded(
            child: RefreshIndicator(
              onRefresh: () async {
                ref.invalidate(athletesProvider((search: search, page: page)));
              },
              child: athletesAsync.when(
                loading: () => const Center(child: CircularProgressIndicator()),
                error: (error, stackTrace) => ListView(
                  physics: const AlwaysScrollableScrollPhysics(),
                  padding: const EdgeInsets.all(24),
                  children: [
                    Text(error.toString(), textAlign: TextAlign.center),
                    const SizedBox(height: 12),
                    ElevatedButton(
                      onPressed: () => ref.invalidate(
                        athletesProvider((search: search, page: page)),
                      ),
                      child: const Text('Tentar novamente'),
                    ),
                  ],
                ),
                data: (result) {
                  if (result.items.isEmpty) {
                    return ListView(
                      physics: const AlwaysScrollableScrollPhysics(),
                      children: const [
                        SizedBox(height: 80),
                        Icon(
                          Icons.groups_2_outlined,
                          size: 40,
                          color: Colors.black45,
                        ),
                        SizedBox(height: 10),
                        Center(child: Text('Nenhum atleta encontrado.')),
                      ],
                    );
                  }

                  final docsAsync = ref.watch(
                    athleteDocumentIndicatorsProvider(result.items),
                  );

                  return ListView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.fromLTRB(16, 8, 16, 16),
                    children: [
                      ...result.items.map((athlete) {
                        final indicator = docsAsync.valueOrNull?[athlete.id];
                        return _AthleteTile(
                          athlete: athlete,
                          indicator: indicator,
                          onTap: () =>
                              context.push('/home/athletes/${athlete.id}'),
                        );
                      }),
                      const SizedBox(height: 10),
                      _Pagination(
                        page: result.currentPage,
                        pageCount: result.pageCount,
                        onPrevious: result.currentPage > 1
                            ? () =>
                                  ref
                                          .read(athletesPageProvider.notifier)
                                          .state =
                                      result.currentPage - 1
                            : null,
                        onNext: result.currentPage < result.pageCount
                            ? () =>
                                  ref
                                          .read(athletesPageProvider.notifier)
                                          .state =
                                      result.currentPage + 1
                            : null,
                      ),
                    ],
                  );
                },
              ),
            ),
          ),
        ],
      ),
    );
  }

  void _submitSearch(String value) {
    ref.read(athleteSearchProvider.notifier).state = value.trim();
    ref.read(athletesPageProvider.notifier).state = 1;
  }
}

class _AthleteTile extends StatelessWidget {
  final AthleteModel athlete;
  final DocumentIndicator? indicator;
  final VoidCallback onTap;

  const _AthleteTile({
    required this.athlete,
    required this.indicator,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    final hasPending = indicator?.hasPending ?? false;

    return Card(
      margin: const EdgeInsets.only(bottom: 10),
      child: ListTile(
        onTap: onTap,
        title: Text(
          athlete.fullName,
          style: const TextStyle(fontWeight: FontWeight.w700),
        ),
        subtitle: Padding(
          padding: const EdgeInsets.only(top: 4),
          child: Row(
            children: [
              _CategoryBadge(label: athlete.categoryName ?? 'Sem categoria'),
              const SizedBox(width: 8),
              _DocumentBadge(hasPending: hasPending),
            ],
          ),
        ),
        trailing: const Icon(Icons.chevron_right),
      ),
    );
  }
}

class _CategoryBadge extends StatelessWidget {
  final String label;

  const _CategoryBadge({required this.label});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: Colors.blueGrey.shade100,
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        label,
        style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
      ),
    );
  }
}

class _DocumentBadge extends StatelessWidget {
  final bool hasPending;

  const _DocumentBadge({required this.hasPending});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: hasPending ? Colors.orange.shade100 : Colors.green.shade100,
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        hasPending ? 'Pendência' : 'Documentos OK',
        style: TextStyle(
          fontSize: 12,
          fontWeight: FontWeight.w600,
          color: hasPending ? Colors.orange.shade900 : Colors.green.shade900,
        ),
      ),
    );
  }
}

class _Pagination extends StatelessWidget {
  final int page;
  final int pageCount;
  final VoidCallback? onPrevious;
  final VoidCallback? onNext;

  const _Pagination({
    required this.page,
    required this.pageCount,
    required this.onPrevious,
    required this.onNext,
  });

  @override
  Widget build(BuildContext context) {
    if (pageCount <= 1) {
      return const SizedBox.shrink();
    }

    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        OutlinedButton(onPressed: onPrevious, child: const Text('Anterior')),
        const SizedBox(width: 12),
        Text(
          '$page / $pageCount',
          style: const TextStyle(fontWeight: FontWeight.w600),
        ),
        const SizedBox(width: 12),
        OutlinedButton(onPressed: onNext, child: const Text('Próxima')),
      ],
    );
  }
}
