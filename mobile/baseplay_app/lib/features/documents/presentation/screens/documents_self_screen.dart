import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../athletes/domain/models/athlete_model.dart';
import '../../../../presentation/state/providers.dart';
import '../../../../presentation/widgets/team_selector_action.dart';
import '../../../../core/auth/permissions.dart';
import '../../domain/models/document_model.dart';
import '../state/documents_providers.dart';

class DocumentsSelfScreen extends ConsumerWidget {
  const DocumentsSelfScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final athletesAsync = ref.watch(selfAthletesProvider);
    final selectedAthleteId = ref.watch(selfDocumentsAthleteIdProvider);
    final canUpload = ref
            .watch(authUserProvider)
            ?.hasPermission(Permissions.documentsUpload) ??
        false;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Documentos'),
        actions: const [TeamSelectorAction()],
      ),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(selfAthletesProvider);
          final id = ref.read(selfDocumentsAthleteIdProvider);
          if (id != null) {
            ref.invalidate(documentsByAthleteProvider(id));
          }
        },
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          children: [
            athletesAsync.when(
              loading: () => const LinearProgressIndicator(),
              error: (error, _) => Text(error.toString()),
              data: (athletes) {
                if (athletes.isEmpty) {
                  return const Text('Nenhum atleta vinculado.');
                }

                final current =
                    selectedAthleteId ?? athletes.first.id;
                if (selectedAthleteId == null) {
                  WidgetsBinding.instance.addPostFrameCallback((_) {
                    ref
                        .read(selfDocumentsAthleteIdProvider.notifier)
                        .state = current;
                  });
                }

                return Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    DropdownButtonFormField<int>(
                      value: current,
                      isExpanded: true,
                      decoration:
                          const InputDecoration(labelText: 'Atleta'),
                      items: athletes
                          .map(
                            (athlete) => DropdownMenuItem<int>(
                              value: athlete.id,
                              child: Text(athlete.fullName),
                            ),
                          )
                          .toList(),
                      onChanged: (value) {
                        ref
                            .read(selfDocumentsAthleteIdProvider.notifier)
                            .state = value;
                      },
                    ),
                    const SizedBox(height: 12),
                    if (canUpload)
                      SizedBox(
                        width: double.infinity,
                        child: ElevatedButton.icon(
                          onPressed: () => context.push(
                            '/home/documents/athlete/$current/upload',
                          ),
                          icon: const Icon(Icons.upload_file_outlined),
                          label: const Text('Upload de documento'),
                        ),
                      ),
                    if (canUpload) const SizedBox(height: 12),
                    _DocumentsList(athleteId: current),
                  ],
                );
              },
            ),
          ],
        ),
      ),
    );
  }
}

class _DocumentsList extends ConsumerWidget {
  final int athleteId;

  const _DocumentsList({required this.athleteId});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final docsAsync = ref.watch(documentsByAthleteProvider(athleteId));

    return docsAsync.when(
      loading: () => const Card(
        child: Padding(
          padding: EdgeInsets.all(20),
          child: Center(child: CircularProgressIndicator()),
        ),
      ),
      error: (error, _) => Card(
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
          children: sorted
              .map((doc) => _DocumentTile(document: doc))
              .toList(),
        );
      },
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
