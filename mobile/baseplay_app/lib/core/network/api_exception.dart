class ApiException implements Exception {
  final String message;
  final int? statusCode;
  final bool isUnauthorized;

  const ApiException(
    this.message, {
    this.statusCode,
    this.isUnauthorized = false,
  });

  @override
  String toString() => message;
}
