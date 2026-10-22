import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../config.dart';
import '../models/user.dart';
import 'api_client.dart';

enum AuthStatus { unknown, authenticated, unauthenticated }

/// Owns the authenticated session: the token, the current [User], and the
/// login/logout flow. Persists the token so the app stays logged in across
/// restarts, and reacts to a server 401 by dropping the session.
class AuthService extends ChangeNotifier {
  AuthService(this._api) {
    // If any request anywhere gets a 401, tear the session down.
    _api.onUnauthorized = _handleUnauthorized;
  }

  static const _tokenKey = 'auth_token';

  final ApiClient _api;

  AuthStatus _status = AuthStatus.unknown;
  User? _user;

  AuthStatus get status => _status;
  User? get user => _user;
  bool get isAuthenticated => _status == AuthStatus.authenticated;

  /// The shared client, handed to the other services so they reuse the token.
  ApiClient get api => _api;

  /// Restore a saved token on launch and confirm it is still valid.
  Future<void> bootstrap() async {
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString(_tokenKey);

    if (token == null || token.isEmpty) {
      _setUnauthenticated();
      return;
    }

    _api.token = token;
    try {
      final body = await _api.get('/me');
      _user = User.fromJson(_unwrap(body));
      _status = AuthStatus.authenticated;
      notifyListeners();
    } on ApiException {
      // Token invalid/expired — clear it and show the login screen.
      await _clearToken();
      _setUnauthenticated();
    }
  }

  /// Exchange credentials for a token. Throws [ApiException] on failure so the
  /// login screen can show the message (bad credentials, disabled account…).
  Future<void> login(String email, String password) async {
    final body = await _api.post('/login', body: {
      'email': email.trim(),
      'password': password,
      'device_name': AppConfig.deviceName,
    });

    final map = body as Map<String, dynamic>;
    final token = map['token'] as String;

    _api.token = token;
    _user = User.fromJson(map['user'] as Map<String, dynamic>);

    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_tokenKey, token);

    _status = AuthStatus.authenticated;
    notifyListeners();
  }

  /// Revoke the token server-side (best effort) and clear it locally.
  Future<void> logout() async {
    try {
      await _api.post('/logout');
    } on ApiException {
      // Even if the call fails we still drop the local session.
    }
    await _clearToken();
    _setUnauthenticated();
  }

  void _handleUnauthorized() {
    // Fired from ApiClient on a 401 during any request.
    _clearToken();
    _setUnauthenticated();
  }

  Future<void> _clearToken() async {
    _api.token = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
  }

  void _setUnauthenticated() {
    _user = null;
    _status = AuthStatus.unauthenticated;
    notifyListeners();
  }

  /// GET /me returns the user wrapped in a "data" envelope.
  Map<String, dynamic> _unwrap(dynamic body) {
    if (body is Map<String, dynamic> && body['data'] is Map<String, dynamic>) {
      return body['data'] as Map<String, dynamic>;
    }
    return body as Map<String, dynamic>;
  }
}
