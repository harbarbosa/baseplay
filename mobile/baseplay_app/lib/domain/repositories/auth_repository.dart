import '../models/login_request.dart';
import '../models/login_response.dart';
import '../models/user.dart';

abstract class AuthRepository {
  Future<LoginResponse> login(LoginRequest request);
  Future<User> me();
  Future<void> logout();
  Future<String?> getToken();
}
