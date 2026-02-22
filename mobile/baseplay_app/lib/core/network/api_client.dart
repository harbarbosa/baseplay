import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import '../config/app_config.dart';
import '../storage/token_storage.dart';
import '../api/endpoints.dart';

class ApiClient {
  final Dio dio;

  ApiClient(
    TokenStorage tokenStorage, {
    void Function()? onUnauthorized,
    Future<int?> Function()? loadTeamId,
  })
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

          final isLoginRequest =
              options.path == Endpoints.authLogin ||
              options.uri.path == Endpoints.authLogin;
          if (!isLoginRequest &&
              token != null &&
              token.isNotEmpty &&
              loadTeamId != null &&
              options.headers['X-Team-Id'] == null) {
            final teamId = await loadTeamId();
            if (teamId != null && teamId > 0) {
              options.headers['X-Team-Id'] = teamId.toString();
              if (AppConfig.enableHttpLogs) {
                debugPrint('X-Team-Id aplicado: $teamId');
              }
            }
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
