class ApiResponseParser {
  static Map<String, dynamic> asMap(dynamic value) {
    if (value is Map<String, dynamic>) {
      return value;
    }
    if (value is Map) {
      return value.map((key, val) => MapEntry(key.toString(), val));
    }
    return <String, dynamic>{};
  }

  static dynamic extractData(dynamic responseData) {
    final map = asMap(responseData);
    if (map.containsKey('data')) {
      return map['data'];
    }
    return responseData;
  }

  static String extractMessage(
    dynamic responseData, {
    String fallback = 'Erro ao processar requisição.',
  }) {
    final map = asMap(responseData);
    final message = map['message']?.toString();
    if (message != null && message.trim().isNotEmpty) {
      return message;
    }
    return fallback;
  }
}

