import 'package:dio/dio.dart';
import '../../core/api/endpoints.dart';
import '../../core/network/api_client.dart';
import '../../core/network/api_exception.dart';
import '../../core/network/api_response_parser.dart';
import '../../core/storage/token_storage.dart';
import '../../domain/models/login_request.dart';
import '../../domain/models/login_response.dart';
import '../../domain/models/user.dart';
import '../../domain/repositories/auth_repository.dart';

class AuthRepositoryImpl implements AuthRepository {
  final ApiClient _api;
  final TokenStorage _tokenStorage;

  AuthRepositoryImpl(this._api, this._tokenStorage);

  @override
  Future<LoginResponse> login(LoginRequest request) async {
    try {
      final response = await _api.dio.post(
        Endpoints.authLogin,
        data: request.toJson(),
      );
      final payload = ApiResponseParser.extractData(response.data);
      final data = ApiResponseParser.asMap(payload);
      final result = LoginResponse.fromJson(data);
      await _tokenStorage.save(result.token);
      return result;
    } on DioException catch (e) {
      final message = ApiResponseParser.extractMessage(
        e.response?.data,
        fallback: 'Erro ao autenticar.',
      );
      throw ApiException(
        message,
        statusCode: e.response?.statusCode,
        isUnauthorized: e.response?.statusCode == 401,
      );
    }
  }

  @override
  Future<User> me() async {
    try {
      final response = await _api.dio.get(Endpoints.authMe);
      final payload = ApiResponseParser.extractData(response.data);
      final data = ApiResponseParser.asMap(payload);
      return User.fromJson(data);
    } on DioException catch (e) {
      final message = ApiResponseParser.extractMessage(
        e.response?.data,
        fallback: 'Erro ao carregar usuário.',
      );
      throw ApiException(
        message,
        statusCode: e.response?.statusCode,
        isUnauthorized: e.response?.statusCode == 401,
      );
    }
  }

  @override
  Future<void> logout() async {
    await _tokenStorage.clear();
  }

  @override
  Future<String?> getToken() => _tokenStorage.read();
}
