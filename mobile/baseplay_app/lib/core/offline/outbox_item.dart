enum OutboxStatus { pending, error, done }

class OutboxItem {
  final String id;
  final String type;
  final Map<String, dynamic> payload;
  final DateTime createdAt;
  final int retries;
  final OutboxStatus status;

  const OutboxItem({
    required this.id,
    required this.type,
    required this.payload,
    required this.createdAt,
    required this.retries,
    required this.status,
  });

  OutboxItem copyWith({
    int? retries,
    OutboxStatus? status,
  }) {
    return OutboxItem(
      id: id,
      type: type,
      payload: payload,
      createdAt: createdAt,
      retries: retries ?? this.retries,
      status: status ?? this.status,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'type': type,
      'payload': payload,
      'createdAt': createdAt.millisecondsSinceEpoch,
      'retries': retries,
      'status': status.name,
    };
  }

  factory OutboxItem.fromJson(Map<String, dynamic> json) {
    final statusRaw = (json['status'] ?? 'pending').toString();
    final status = OutboxStatus.values.firstWhere(
      (value) => value.name == statusRaw,
      orElse: () => OutboxStatus.pending,
    );

    return OutboxItem(
      id: (json['id'] ?? '').toString(),
      type: (json['type'] ?? '').toString(),
      payload: (json['payload'] as Map?)?.cast<String, dynamic>() ??
          <String, dynamic>{},
      createdAt: DateTime.fromMillisecondsSinceEpoch(
        (json['createdAt'] ?? 0) as int,
      ),
      retries: int.tryParse('${json['retries'] ?? 0}') ?? 0,
      status: status,
    );
  }
}
