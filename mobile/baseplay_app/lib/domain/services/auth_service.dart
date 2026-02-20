import '../models/login_request.dart';
import '../models/login_response.dart';
import '../models/user.dart';
import '../repositories/auth_repository.dart';

class AuthService {
  final AuthRepository _repo;

  AuthService(this._repo);

  Future<LoginResponse> login(String email, String password) {
    return _repo.login(LoginRequest(email: email, password: password));
  }

  Future<User> me() => _repo.me();

  Future<void> logout() => _repo.logout();

  Future<String?> getToken() => _repo.getToken();
}
