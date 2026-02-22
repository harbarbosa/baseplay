enum PendingType { documents, notices, events }

enum PendingPriority { high, medium, low }

enum PendingActionType { openRoute, confirmEvent }

class PendingItem {
  final String id;
  final PendingType type;
  final String title;
  final String description;
  final PendingPriority priority;
  final String ctaLabel;
  final PendingActionType actionType;
  final String? route;
  final Map<String, dynamic>? actionPayload;

  const PendingItem({
    required this.id,
    required this.type,
    required this.title,
    required this.description,
    required this.priority,
    required this.ctaLabel,
    required this.actionType,
    this.route,
    this.actionPayload,
  });
}
