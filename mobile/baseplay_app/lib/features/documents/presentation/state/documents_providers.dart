import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../presentation/state/providers.dart';
import '../../../athletes/domain/models/athlete_model.dart';
import '../../../athletes/presentation/state/athletes_providers.dart';
import '../../data/document_repository.dart';
import '../../domain/models/document_model.dart';
import '../../domain/models/document_type_model.dart';
import '../../domain/models/documents_overview_model.dart';

final documentsCategoryFilterProvider = StateProvider<int?>((ref) => null);

final documentRepositoryProvider = Provider<DocumentRepository>((ref) {
  return DocumentRepository(
    ref.read(apiClientProvider),
    ref.read(athleteRepositoryProvider),
  );
});

final documentTypesProvider =
    FutureProvider.autoDispose<List<DocumentTypeModel>>((ref) {
      return ref.read(documentRepositoryProvider).listTypes();
    });

final documentsByAthleteProvider = FutureProvider.autoDispose
    .family<List<DocumentModel>, int>((ref, athleteId) {
      return ref.read(documentRepositoryProvider).listByAthlete(athleteId);
    });

final documentsOverviewProvider =
    FutureProvider.autoDispose<DocumentsOverviewModel>((ref) {
      final categoryId = ref.watch(documentsCategoryFilterProvider);
      return ref
          .read(documentRepositoryProvider)
          .getOverview(categoryId: categoryId);
    });

final athletesForDocumentsProvider =
    FutureProvider.autoDispose<List<AthleteModel>>((ref) async {
      final categoryId = ref.watch(documentsCategoryFilterProvider);
      final all = <AthleteModel>[];
      var page = 1;

      while (true) {
        final response = await ref
            .read(athleteRepositoryProvider)
            .listAthletes(page: page, perPage: 50, categoryId: categoryId);
        all.addAll(response.items);
        if (page >= response.pageCount) {
          break;
        }
        page++;
      }

      return all;
    });

final documentsUploadProgressProvider = StateProvider<double>((ref) => 0);

final documentUploadControllerProvider = Provider<DocumentUploadController>((
  ref,
) {
  return DocumentUploadController(ref);
});

class DocumentUploadController {
  final Ref _ref;

  DocumentUploadController(this._ref);

  Future<void> upload(DocumentUploadRequest request) async {
    _ref.read(documentsUploadProgressProvider.notifier).state = 0;
    await _ref
        .read(documentRepositoryProvider)
        .upload(
          request,
          onProgress: (sent, total) {
            if (total > 0) {
              _ref.read(documentsUploadProgressProvider.notifier).state =
                  sent / total;
            }
          },
        );
    _ref.read(documentsUploadProgressProvider.notifier).state = 1;

    _ref.invalidate(documentsByAthleteProvider(request.athleteId));
    _ref.invalidate(documentsOverviewProvider);
    _ref.invalidate(athletesForDocumentsProvider);
  }
}
