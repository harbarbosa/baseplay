class AppConfig {
  static const baseUrl = String.fromEnvironment(
    'BASE_URL',
    defaultValue: 'http://baseplay.test',
  );

  static const connectTimeoutMs = int.fromEnvironment(
    'CONNECT_TIMEOUT_MS',
    defaultValue: 12000,
  );

  static const receiveTimeoutMs = int.fromEnvironment(
    'RECEIVE_TIMEOUT_MS',
    defaultValue: 20000,
  );

  static const sendTimeoutMs = int.fromEnvironment(
    'SEND_TIMEOUT_MS',
    defaultValue: 15000,
  );

  static const enableHttpLogs = bool.fromEnvironment(
    'ENABLE_HTTP_LOGS',
    defaultValue: false,
  );
}
