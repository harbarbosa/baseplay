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
  final MatchDetails? match;
  final TrainingDetails? training;

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
    this.match,
    this.training,
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
      match: MatchDetails.fromJson(json['match']),
      training: TrainingDetails.fromJson(json['training']),
    );
  }
}

class MatchDetails {
  final int id;
  final String? opponentName;
  final String? competitionName;
  final String? roundName;
  final String? location;
  final String? status;
  final int? scoreFor;
  final int? scoreAgainst;
  final String? reportSummary;
  final String? reportStrengths;
  final String? coachNotes;
  final List<TacticalBoardInfo> tacticalBoards;

  const MatchDetails({
    required this.id,
    this.opponentName,
    this.competitionName,
    this.roundName,
    this.location,
    this.status,
    this.scoreFor,
    this.scoreAgainst,
    this.reportSummary,
    this.reportStrengths,
    this.coachNotes,
    this.tacticalBoards = const [],
  });

  static MatchDetails? fromJson(dynamic raw) {
    if (raw is! Map) return null;
    final data = Map<String, dynamic>.from(raw);
    final report = data['report'] is Map ? Map<String, dynamic>.from(data['report']) : null;
    final boardsRaw = (data['tactical_boards'] as List?) ?? const [];
    return MatchDetails(
      id: int.tryParse('${data['id'] ?? 0}') ?? 0,
      opponentName: data['opponent_name']?.toString(),
      competitionName: data['competition_name']?.toString(),
      roundName: data['round_name']?.toString(),
      location: data['location']?.toString(),
      status: data['status']?.toString(),
      scoreFor: int.tryParse('${data['score_for'] ?? ''}'),
      scoreAgainst: int.tryParse('${data['score_against'] ?? ''}'),
      reportSummary: report?['summary']?.toString(),
      reportStrengths: report?['strengths']?.toString(),
      coachNotes: report?['coach_notes']?.toString(),
      tacticalBoards: boardsRaw
          .whereType<Map>()
          .map((item) => TacticalBoardInfo.fromJson(item))
          .toList(),
    );
  }
}

class TrainingDetails {
  final int id;
  final String? title;
  final String? sessionDate;
  final DateTime? startDateTime;
  final DateTime? endDateTime;
  final String? location;
  final String? notes;
  final String? planTitle;
  final List<TrainingBlockInfo> blocks;
  final List<TacticalBoardInfo> tacticalBoards;

  const TrainingDetails({
    required this.id,
    this.title,
    this.sessionDate,
    this.startDateTime,
    this.endDateTime,
    this.location,
    this.notes,
    this.planTitle,
    this.blocks = const [],
    this.tacticalBoards = const [],
  });

  static TrainingDetails? fromJson(dynamic raw) {
    if (raw is! Map) return null;
    final data = Map<String, dynamic>.from(raw);
    DateTime? parseDate(dynamic value) {
      final rawValue = value?.toString();
      if (rawValue == null || rawValue.isEmpty) {
        return null;
      }
      return DateTime.tryParse(rawValue);
    }

    final blocksRaw = (data['blocks'] as List?) ?? const [];
    final boardsRaw = (data['tactical_boards'] as List?) ?? const [];

    return TrainingDetails(
      id: int.tryParse('${data['id'] ?? 0}') ?? 0,
      title: data['title']?.toString(),
      sessionDate: data['session_date']?.toString(),
      startDateTime: parseDate(data['start_datetime']),
      endDateTime: parseDate(data['end_datetime']),
      location: data['location']?.toString(),
      notes: data['notes']?.toString(),
      planTitle: data['plan_title']?.toString(),
      blocks: blocksRaw
          .whereType<Map>()
          .map((item) => TrainingBlockInfo.fromJson(item))
          .toList(),
      tacticalBoards: boardsRaw
          .whereType<Map>()
          .map((item) => TacticalBoardInfo.fromJson(item))
          .toList(),
    );
  }
}

class TrainingBlockInfo {
  final int id;
  final String? blockType;
  final String? title;
  final int? durationMin;
  final int? exerciseId;
  final String? exerciseTitle;
  final String? instructions;
  final int? orderIndex;

  const TrainingBlockInfo({
    required this.id,
    this.blockType,
    this.title,
    this.durationMin,
    this.exerciseId,
    this.exerciseTitle,
    this.instructions,
    this.orderIndex,
  });

  factory TrainingBlockInfo.fromJson(Map<dynamic, dynamic> raw) {
    final data = Map<String, dynamic>.from(raw);
    return TrainingBlockInfo(
      id: int.tryParse('${data['id'] ?? 0}') ?? 0,
      blockType: data['block_type']?.toString(),
      title: data['title']?.toString(),
      durationMin: int.tryParse('${data['duration_min'] ?? ''}'),
      exerciseId: int.tryParse('${data['exercise_id'] ?? ''}'),
      exerciseTitle: data['exercise_title']?.toString(),
      instructions: data['instructions']?.toString(),
      orderIndex: int.tryParse('${data['order_index'] ?? ''}'),
    );
  }
}

class TacticalBoardInfo {
  final int id;
  final String title;

  const TacticalBoardInfo({
    required this.id,
    required this.title,
  });

  factory TacticalBoardInfo.fromJson(Map<dynamic, dynamic> raw) {
    final data = Map<String, dynamic>.from(raw);
    return TacticalBoardInfo(
      id: int.tryParse('${data['id'] ?? 0}') ?? 0,
      title: data['title']?.toString() ?? '',
    );
  }
}
