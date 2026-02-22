class Endpoints {
  static const authLogin = '/api/auth/login';
  static const authMe = '/api/auth/me';

  static const events = '/api/events';
  static String eventById(int id) => '/api/events/$id';
  static String eventParticipants(int eventId) =>
      '/api/events/$eventId/participants';
  static String eventAttendance(int eventId) =>
      '/api/events/$eventId/attendance';
  static String eventConfirm(int eventId) => '/api/events/$eventId/confirm';

  static const notices = '/api/notices';
  static String noticeById(int id) => '/api/notices/$id';
  static String noticeRead(int id) => '/api/notices/$id/read';
  static String noticeReplies(int id) => '/api/notices/$id/replies';
  static String noticeReply(int id) => '/api/notices/$id/reply';

  static const athletes = '/api/athletes';
  static String athleteById(int id) => '/api/athletes/$id';
  static String athleteLastActivity(int id) =>
      '/api/athletes/$id/summary/last-activity';

  static const documents = '/api/documents';
  static const documentTypes = '/api/document-types';
  static const documentAlerts = '/api/documents/alerts';
  static const documentsMissingRequired = '/api/documents/missing-required';
  static String reportAthlete(int athleteId) =>
      '/api/reports/athlete/$athleteId';

  static const dashboardAdmin = '/api/dashboard/admin';
  static const dashboardTrainer = '/api/dashboard/trainer';
  static const dashboardAssistant = '/api/dashboard/assistant';
  static const dashboardAthlete = '/api/dashboard/athlete';

  static const alerts = '/api/alerts';
  static String alertRead(int id) => '/api/alerts/$id/read';
}
