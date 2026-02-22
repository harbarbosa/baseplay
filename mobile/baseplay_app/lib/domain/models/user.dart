import 'team.dart';

class User {
  final int id;
  final String name;
  final String email;
  final List<String> roles;
  final List<String> permissions;
  final List<String> capabilities;
  final List<UserTeam> teams;

  const User({
    required this.id,
    required this.name,
    required this.email,
    this.roles = const [],
    this.permissions = const [],
    this.capabilities = const [],
    this.teams = const [],
  });

  factory User.fromJson(Map<String, dynamic> json) {
    final rolesRaw = json['roles'];
    final permissionsRaw = json['permissions'];
    final capabilitiesRaw = json['capabilities'];
    final teamsRaw = json['teams'];

    final permissions = permissionsRaw is List
        ? permissionsRaw.map((e) => e.toString()).toList(growable: false)
        : const <String>[];
    final capabilities = capabilitiesRaw is List
        ? capabilitiesRaw.map((e) => e.toString()).toList(growable: false)
        : const <String>[];
    final combined = <String>{...permissions, ...capabilities}
        .where((e) => e.trim().isNotEmpty)
        .toSet();
    if (combined.contains('documents.view') &&
        !combined.contains('documents.view.team') &&
        !combined.contains('documents.view.self')) {
      combined.add('documents.view.team');
    }
    final merged = combined.toList(growable: false);

    return User(
      id: json['id'] is int
          ? json['id'] as int
          : int.parse(json['id'].toString()),
      name: (json['name'] ?? '').toString(),
      email: (json['email'] ?? '').toString(),
      roles: rolesRaw is List
          ? rolesRaw.map((e) => e.toString()).toList(growable: false)
          : const [],
      permissions: permissions,
      capabilities: merged,
      teams: teamsRaw is List
          ? teamsRaw
              .whereType<Map>()
              .map((item) => UserTeam.fromJson(item.cast<String, dynamic>()))
              .where((team) => team.id > 0)
              .toList(growable: false)
          : const [],
    );
  }

  bool hasPermission(String permission) => capabilities.isNotEmpty
      ? capabilities.contains(permission)
      : permissions.contains(permission);
}
