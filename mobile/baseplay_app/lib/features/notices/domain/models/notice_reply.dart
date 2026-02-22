class NoticeReply {
  final int id;
  final String authorName;
  final String message;
  final DateTime? createdAt;

  const NoticeReply({
    required this.id,
    required this.authorName,
    required this.message,
    required this.createdAt,
  });

  factory NoticeReply.fromJson(Map<String, dynamic> json) {
    final rawDate = json['created_at']?.toString();
    return NoticeReply(
      id: int.tryParse('${json['id'] ?? 0}') ?? 0,
      authorName: (json['author_name'] ?? json['author'] ?? '-').toString(),
      message: (json['message'] ?? json['content'] ?? '').toString(),
      createdAt:
          rawDate == null || rawDate.isEmpty ? null : DateTime.tryParse(rawDate),
    );
  }
}
