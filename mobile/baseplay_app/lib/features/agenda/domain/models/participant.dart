class Participant {
  final int id;
  final int eventId;
  final int athleteId;
  final String firstName;
  final String lastName;
  final String invitationStatus;

  const Participant({
    required this.id,
    required this.eventId,
    required this.athleteId,
    required this.firstName,
    required this.lastName,
    required this.invitationStatus,
  });

  String get fullName => ('$firstName $lastName').trim();

  factory Participant.fromJson(Map<String, dynamic> json) {
    return Participant(
      id: int.tryParse('${json['id'] ?? 0}') ?? 0,
      eventId: int.tryParse('${json['event_id'] ?? 0}') ?? 0,
      athleteId: int.tryParse('${json['athlete_id'] ?? 0}') ?? 0,
      firstName: '${json['first_name'] ?? ''}',
      lastName: '${json['last_name'] ?? ''}',
      invitationStatus: '${json['invitation_status'] ?? 'pending'}',
    );
  }
}
