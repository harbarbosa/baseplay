class User {
  final int id;
  final String name;
  final String email;
  final List<String> roles;
  final List<String> permissions;

  const User({
    required this.id,
    required this.name,
    required this.email,
    this.roles = const [],
    this.permissions = const [],
  });

  factory User.fromJson(Map<String, dynamic> json) {
    final rolesRaw = json['roles'];
    final permissionsRaw = json['permissions'];

    return User(
      id: json['id'] is int
          ? json['id'] as int
          : int.parse(json['id'].toString()),
      name: (json['name'] ?? '').toString(),
      email: (json['email'] ?? '').toString(),
      roles: rolesRaw is List
          ? rolesRaw.map((e) => e.toString()).toList(growable: false)
          : const [],
      permissions: permissionsRaw is List
          ? permissionsRaw.map((e) => e.toString()).toList(growable: false)
          : const [],
    );
  }

  bool hasPermission(String permission) => permissions.contains(permission);
}
