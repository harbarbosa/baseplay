import 'attendance_history_model.dart';

class AthleteSummaryModel {
  final double presencePercentage;
  final int totalSessions;
  final int absences;
  final String? lastTrainingTitle;
  final DateTime? lastTrainingDate;
  final String? lastMatchTitle;
  final DateTime? lastMatchDate;
  final int documentsActive;
  final int documentsExpired;
  final int documentsExpiringSoon;
  final String? nextEventType;
  final DateTime? nextEventDate;
  final String? nextEventTitle;
  final List<AttendanceHistoryModel> attendanceHistory;

  const AthleteSummaryModel({
    required this.presencePercentage,
    required this.totalSessions,
    required this.absences,
    required this.lastTrainingTitle,
    required this.lastTrainingDate,
    required this.lastMatchTitle,
    required this.lastMatchDate,
    required this.documentsActive,
    required this.documentsExpired,
    required this.documentsExpiringSoon,
    required this.nextEventType,
    required this.nextEventDate,
    required this.nextEventTitle,
    required this.attendanceHistory,
  });
}
