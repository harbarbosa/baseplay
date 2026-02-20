class AttendanceHistoryModel {
  final DateTime? eventDate;
  final String eventType;
  final String eventTitle;
  final String status;

  const AttendanceHistoryModel({
    required this.eventDate,
    required this.eventType,
    required this.eventTitle,
    required this.status,
  });
}
