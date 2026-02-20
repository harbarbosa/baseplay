import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../../presentation/state/providers.dart';
import '../../data/dashboard_repository.dart';
import '../../domain/models/dashboard_summary.dart';

final dashboardRepositoryProvider = Provider<DashboardRepository>((ref) {
  return DashboardRepository(ref.read(apiClientProvider));
});

final dashboardSummaryProvider = FutureProvider.autoDispose<DashboardSummary>((ref) {
  return ref.read(dashboardRepositoryProvider).getDashboardByProfile();
});

final pendingCenterSummaryProvider = FutureProvider.autoDispose<PendingCenterSummary>((ref) {
  return ref.read(dashboardRepositoryProvider).getPendingCenterSummary();
});
