import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../athletes/presentation/state/athletes_providers.dart';
import '../../../../presentation/state/providers.dart';
import '../../domain/models/document_model.dart';
import '../state/documents_providers.dart';

class AthleteDocumentsScreen extends ConsumerWidget {
  final int athleteId;

  const AthleteDocumentsScreen({super.key, required this.athleteId});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final athleteAsync = ref.watch(athleteDetailProvider(athleteId));
    final docsAsync = ref.watch(documentsByAthleteProvider(athleteId));
    final canUpload =
        ref.watch(authUserProvider)?.hasPermission('documents.upload') ?? false;

    return Scaffold(
      appBar: AppBar(title: const Text('Documentos do atleta')),
      body: RefreshIndicator(
        onRefresh: () async {
          ref.invalidate(documentsByAthleteProvider(athleteId));
          ref.invalidate(athleteDetailProvider(athleteId));
        },
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          children: [
            athleteAsync.when(
              loading: () => const SizedBox.shrink(),
              error: (error, stackTrace) => Text(error.toString()),
              data: (athlete) => Text(
                athlete.fullName,
                style: Theme.of(context).textTheme.titleMedium,
              ),
            ),
            const SizedBox(height: 8),
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
                  children: sorted
                      .map((doc) => _DocumentTile(document: doc))
                      .toList(),
                );
              },
            ),
          ],
        ),
      ),
      floatingActionButton: canUpload
          ? FloatingActionButton.extended(
              onPressed: () =>
                  context.push('/home/documents/athlete/$athleteId/upload'),
              icon: const Icon(Icons.upload_file_outlined),
              label: const Text('Upload'),
            )
          : null,
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
