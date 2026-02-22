import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../presentation/state/providers.dart';
import '../../../../core/context/team_context_provider.dart';
import '../../data/athlete_repository.dart';
import '../../domain/models/athlete_model.dart';
import '../../domain/models/athlete_summary_model.dart';

final athleteRepositoryProvider = Provider<AthleteRepository>((ref) {
  return AthleteRepository(ref.read(apiClientProvider));
});

final athleteSearchProvider = StateProvider<String>((ref) => '');

final athletesPageProvider = StateProvider<int>((ref) => 1);

final athletesProvider = FutureProvider.autoDispose
    .family<AthleteListResponse, ({String search, int page})>((
      ref,
      params,
    ) async {
      ref.watch(teamContextRefreshProvider);
      return ref
          .read(athleteRepositoryProvider)
          .listAthletes(search: params.search, page: params.page, perPage: 15);
    });

final athleteDocumentIndicatorsProvider = FutureProvider.autoDispose
    .family<Map<int, DocumentIndicator>, List<AthleteModel>>((
      ref,
      athletes,
    ) async {
      ref.watch(teamContextRefreshProvider);
      final ids = athletes.map((a) => a.id).toList();
      return ref.read(athleteRepositoryProvider).getDocumentIndicators(ids);
    });

final athleteDetailProvider = FutureProvider.autoDispose
    .family<AthleteModel, int>((ref, athleteId) {
      ref.watch(teamContextRefreshProvider);
      return ref.read(athleteRepositoryProvider).getAthlete(athleteId);
    });

final athleteSummaryProvider = FutureProvider.autoDispose
    .family<AthleteSummaryModel, int>((ref, athleteId) {
      ref.watch(teamContextRefreshProvider);
      return ref.read(athleteRepositoryProvider).getAthleteSummary(athleteId);
    });
