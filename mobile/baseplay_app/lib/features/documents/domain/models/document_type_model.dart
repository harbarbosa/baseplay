class DocumentTypeModel {
  final int id;
  final String name;
  final bool requiresExpiration;
  final int? defaultValidDays;

  const DocumentTypeModel({
    required this.id,
    required this.name,
    required this.requiresExpiration,
    required this.defaultValidDays,
  });

  factory DocumentTypeModel.fromJson(Map<String, dynamic> json) {
    return DocumentTypeModel(
      id: int.tryParse('${json['id'] ?? 0}') ?? 0,
      name: '${json['name'] ?? ''}',
      requiresExpiration: '${json['requires_expiration'] ?? '0'}' == '1',
      defaultValidDays: int.tryParse('${json['default_valid_days'] ?? ''}'),
    );
  }
}
