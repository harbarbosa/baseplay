class DocumentsOverviewModel {
  final int expired;
  final int expiring;
  final int missing;

  const DocumentsOverviewModel({
    required this.expired,
    required this.expiring,
    required this.missing,
  });
}
