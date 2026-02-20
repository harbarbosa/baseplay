import 'package:dio/dio.dart';
import '../config/app_config.dart';
import '../storage/token_storage.dart';

class ApiClient {
  final Dio dio;

  ApiClient(TokenStorage tokenStorage, {void Function()? onUnauthorized})
    : dio = Dio(
        BaseOptions(
          baseUrl: AppConfig.baseUrl,
          connectTimeout: Duration(milliseconds: AppConfig.connectTimeoutMs),
          receiveTimeout: Duration(milliseconds: AppConfig.receiveTimeoutMs),
          sendTimeout: Duration(milliseconds: AppConfig.sendTimeoutMs),
          headers: {'Accept': 'application/json'},
        ),
      ) {
    dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          final token = await tokenStorage.read();
          if (token != null && token.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          handler.next(options);
        },
        onError: (error, handler) async {
          if (error.response?.statusCode == 401) {
            await tokenStorage.clear();
            onUnauthorized?.call();
          }
          handler.next(error);
        },
      ),
    );

    if (AppConfig.enableHttpLogs) {
      dio.interceptors.add(
        LogInterceptor(requestBody: true, responseBody: true),
      );
    }
  }
}
