class Attendance {
  final int id;
  final int eventId;
  final int athleteId;
  final String status;
  final String? notes;

  const Attendance({
    required this.id,
    required this.eventId,
    required this.athleteId,
    required this.status,
    this.notes,
  });

  factory Attendance.fromJson(Map<String, dynamic> json) {
    return Attendance(
      id: int.tryParse('${json['id'] ?? 0}') ?? 0,
      eventId: int.tryParse('${json['event_id'] ?? 0}') ?? 0,
      athleteId: int.tryParse('${json['athlete_id'] ?? 0}') ?? 0,
      status: '${json['status'] ?? 'absent'}',
      notes: json['notes']?.toString(),
    );
  }
}
