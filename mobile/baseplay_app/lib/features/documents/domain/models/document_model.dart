class DocumentModel {
  final int id;
  final int? athleteId;
  final int? teamId;
  final int? documentTypeId;
  final String? typeName;
  final String? originalName;
  final String status;
  final DateTime? issuedAt;
  final DateTime? expiresAt;
  final String? notes;

  const DocumentModel({
    required this.id,
    required this.athleteId,
    required this.teamId,
    required this.documentTypeId,
    required this.typeName,
    required this.originalName,
    required this.status,
    required this.issuedAt,
    required this.expiresAt,
    required this.notes,
  });

  factory DocumentModel.fromJson(Map<String, dynamic> json) {
    DateTime? parseDate(dynamic value) {
      final raw = value?.toString();
      if (raw == null || raw.isEmpty) {
        return null;
      }
      return DateTime.tryParse(raw);
    }

    return DocumentModel(
      id: int.tryParse('${json['id'] ?? 0}') ?? 0,
      athleteId: int.tryParse('${json['athlete_id'] ?? ''}'),
      teamId: int.tryParse('${json['team_id'] ?? ''}'),
      documentTypeId: int.tryParse('${json['document_type_id'] ?? ''}'),
      typeName: json['type_name']?.toString(),
      originalName: json['original_name']?.toString(),
      status: '${json['status'] ?? 'active'}',
      issuedAt: parseDate(json['issued_at']),
      expiresAt: parseDate(json['expires_at']),
      notes: json['notes']?.toString(),
    );
  }

  bool get isExpired => status == 'expired';

  bool get isExpiringSoon {
    if (expiresAt == null || isExpired) {
      return false;
    }

    final today = DateTime.now();
    final limit = today.add(const Duration(days: 15));
    return expiresAt!.isAfter(today.subtract(const Duration(days: 1))) &&
        expiresAt!.isBefore(limit);
  }
}
