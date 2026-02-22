class Event {
  final int id;
  final int? teamId;
  final int? categoryId;
  final String type;
  final String title;
  final String? description;
  final DateTime? startDateTime;
  final DateTime? endDateTime;
  final String? location;
  final String status;
  final String? teamName;
  final String? categoryName;
  final String? invitationStatus;

  const Event({
    required this.id,
    this.teamId,
    this.categoryId,
    required this.type,
    required this.title,
    this.description,
    this.startDateTime,
    this.endDateTime,
    this.location,
    required this.status,
    this.teamName,
    this.categoryName,
    this.invitationStatus,
  });

  factory Event.fromJson(Map<String, dynamic> json) {
    DateTime? parseDate(dynamic value) {
      final raw = value?.toString();
      if (raw == null || raw.isEmpty) {
        return null;
      }
      return DateTime.tryParse(raw);
    }

    return Event(
      id: int.tryParse('${json['id'] ?? 0}') ?? 0,
      teamId: int.tryParse('${json['team_id'] ?? ''}'),
      categoryId: int.tryParse('${json['category_id'] ?? ''}'),
      type: '${json['type'] ?? ''}',
      title: '${json['title'] ?? ''}',
      description: json['description']?.toString(),
      startDateTime: parseDate(json['start_datetime']),
      endDateTime: parseDate(json['end_datetime']),
      location: json['location']?.toString(),
      status: '${json['status'] ?? ''}',
      teamName: json['team_name']?.toString(),
      categoryName: json['category_name']?.toString(),
      invitationStatus: json['invitation_status']?.toString(),
    );
  }
}
