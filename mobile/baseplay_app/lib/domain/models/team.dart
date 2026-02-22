class TeamCategory {
  final int id;
  final String name;

  const TeamCategory({required this.id, required this.name});

  factory TeamCategory.fromJson(Map<String, dynamic> json) {
    final idRaw = json['id'] ?? json['category_id'];
    final nameRaw = json['name'] ?? json['category_name'];

    return TeamCategory(
      id: idRaw is int ? idRaw : int.tryParse('$idRaw') ?? 0,
      name: (nameRaw ?? '').toString(),
    );
  }
}

class UserTeam {
  final int id;
  final String name;
  final List<TeamCategory> categories;

  const UserTeam({
    required this.id,
    required this.name,
    this.categories = const [],
  });

  factory UserTeam.fromJson(Map<String, dynamic> json) {
    final idRaw = json['id'] ?? json['team_id'];
    final nameRaw = json['name'] ?? json['team_name'];
    final categoriesRaw = json['categories'] ?? json['team_categories'];

    return UserTeam(
      id: idRaw is int ? idRaw : int.tryParse('$idRaw') ?? 0,
      name: (nameRaw ?? '').toString(),
      categories: categoriesRaw is List
          ? categoriesRaw
              .whereType<Map>()
              .map((item) => TeamCategory.fromJson(item.cast<String, dynamic>()))
              .toList(growable: false)
          : const [],
    );
  }
}
