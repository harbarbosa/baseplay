class AthleteModel {
  final int id;
  final int? categoryId;
  final String firstName;
  final String lastName;
  final String fullName;
  final String? categoryName;
  final String? teamName;
  final String status;

  const AthleteModel({
    required this.id,
    required this.categoryId,
    required this.firstName,
    required this.lastName,
    required this.fullName,
    required this.categoryName,
    required this.teamName,
    required this.status,
  });

  factory AthleteModel.fromJson(Map<String, dynamic> json) {
    final first = '${json['first_name'] ?? ''}'.trim();
    final last = '${json['last_name'] ?? ''}'.trim();
    final merged = ('$first $last').trim();

    return AthleteModel(
      id: int.tryParse('${json['id'] ?? 0}') ?? 0,
      categoryId: int.tryParse('${json['category_id'] ?? ''}'),
      firstName: first,
      lastName: last,
      fullName: merged.isEmpty ? 'Atleta' : merged,
      categoryName: json['category_name']?.toString(),
      teamName: json['team_name']?.toString(),
      status: '${json['status'] ?? 'active'}',
    );
  }
}
