class DashboardSummary {
  final String profile;
  final Map<String, dynamic> kpis;

  const DashboardSummary({required this.profile, required this.kpis});
}

class PendingCenterSummary {
  final int expiredDocuments;
  final int expiringDocuments;
  final int missingRequiredDocuments;
  final int upcomingEventsWithoutCallups;

  const PendingCenterSummary({
    required this.expiredDocuments,
    required this.expiringDocuments,
    required this.missingRequiredDocuments,
    required this.upcomingEventsWithoutCallups,
  });
}
