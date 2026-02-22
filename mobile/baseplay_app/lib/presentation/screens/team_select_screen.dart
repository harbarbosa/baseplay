import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/context/team_context_provider.dart';
import '../../domain/models/team.dart';
import '../state/providers.dart';

class TeamSelectScreen extends ConsumerWidget {
  const TeamSelectScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(authUserProvider);
    final teams = user?.teams ?? const <UserTeam>[];

    return WillPopScope(
      onWillPop: () async => false,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('Selecione a equipe'),
          automaticallyImplyLeading: false,
        ),
        body: teams.isEmpty
            ? const Center(child: Text('Nenhuma equipe encontrada.'))
            : ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  const Text(
                    'Você possui acesso a mais de uma equipe. '
                    'Selecione a equipe ativa para continuar.',
                  ),
                  const SizedBox(height: 16),
                  ...teams.map((team) {
                    return Card(
                      child: ListTile(
                        title: Text(
                          team.name.isEmpty ? 'Equipe ${team.id}' : team.name,
                        ),
                        trailing: const Icon(Icons.chevron_right),
                        onTap: () async {
                          await ref
                              .read(teamContextProvider.notifier)
                              .setActiveTeam(team.id);
                        },
                      ),
                    );
                  }),
                ],
              ),
      ),
    );
  }
}
