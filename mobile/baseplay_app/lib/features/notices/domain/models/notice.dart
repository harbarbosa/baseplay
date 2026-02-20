class Notice {
  final int id;
  final String title;
  final String message;
  final String priority;
  final String status;
  final DateTime? publishAt;
  final String? teamName;
  final String? categoryName;
  final bool isRead;

  const Notice({
    required this.id,
    required this.title,
    required this.message,
    required this.priority,
    required this.status,
    this.publishAt,
    this.teamName,
    this.categoryName,
    required this.isRead,
  });

  Notice copyWith({bool? isRead}) {
    return Notice(
      id: id,
      title: title,
      message: message,
      priority: priority,
      status: status,
      publishAt: publishAt,
      teamName: teamName,
      categoryName: categoryName,
      isRead: isRead ?? this.isRead,
    );
  }

  factory Notice.fromJson(Map<String, dynamic> json, {required bool isRead}) {
    final rawDate = json['publish_at']?.toString();
    return Notice(
      id: int.tryParse('${json['id'] ?? 0}') ?? 0,
      title: '${json['title'] ?? ''}',
      message: '${json['message'] ?? ''}',
      priority: '${json['priority'] ?? 'normal'}',
      status: '${json['status'] ?? 'published'}',
      publishAt: rawDate == null || rawDate.isEmpty
          ? null
          : DateTime.tryParse(rawDate),
      teamName: json['team_name']?.toString(),
      categoryName: json['category_name']?.toString(),
      isRead: isRead,
    );
  }
}
