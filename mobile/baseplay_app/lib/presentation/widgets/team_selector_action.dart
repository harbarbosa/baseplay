import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/context/team_context_provider.dart';
import '../../domain/models/team.dart';
import '../state/providers.dart';

class TeamSelectorAction extends ConsumerWidget {
  const TeamSelectorAction({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final user = ref.watch(authUserProvider);
    final teamContext = ref.watch(teamContextProvider);
    final teams = user?.teams ?? const <UserTeam>[];

    if (user == null || teams.isEmpty) {
      return const SizedBox.shrink();
    }

    final isStaff = user.roles.any(
      (role) => ['admin', 'trainer', 'assistant']
          .contains(role.toLowerCase().trim()),
    );
    final hasMultipleTeams = teams.length > 1;
    if (!isStaff && !hasMultipleTeams) {
      return const SizedBox.shrink();
    }

    final activeTeam = teamContext.activeTeamId == null
        ? null
        : teams.firstWhere(
            (team) => team.id == teamContext.activeTeamId,
            orElse: () => teams.first,
          );

    return TextButton.icon(
      onPressed: () => _openTeamSelector(context, ref, teams),
      icon: const Icon(Icons.group, size: 18),
      label: Text(
        activeTeam == null || activeTeam.name.isEmpty
            ? 'Equipe'
            : activeTeam.name,
        overflow: TextOverflow.ellipsis,
      ),
    );
  }

  Future<void> _openTeamSelector(
    BuildContext context,
    WidgetRef ref,
    List<UserTeam> teams,
  ) async {
    final activeTeamId = ref.read(teamContextProvider).activeTeamId;
    final selected = await showModalBottomSheet<UserTeam>(
      context: context,
      showDragHandle: true,
      builder: (context) {
        return SafeArea(
          child: ListView(
            shrinkWrap: true,
            children: [
              const ListTile(
                title: Text('Selecione a equipe'),
              ),
              ...teams.map(
                (team) => RadioListTile<int>(
                  value: team.id,
                  groupValue: activeTeamId,
                  title: Text(team.name.isEmpty ? 'Equipe ${team.id}' : team.name),
                  onChanged: (_) => Navigator.of(context).pop(team),
                ),
              ),
              const SizedBox(height: 12),
            ],
          ),
        );
      },
    );

    if (selected != null) {
      await ref.read(teamContextProvider.notifier).setActiveTeam(selected.id);
    }
  }
}
