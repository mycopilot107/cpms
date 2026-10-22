import 'dart:async';
import 'dart:convert';
import 'dart:io';

import 'package:http/http.dart' as http;

import '../config.dart';

/// Error thrown for any non-2xx API response (or a network failure).
class ApiException implements Exception {
  final int statusCode;
  final String message;

  /// Laravel 422 field errors: { "email": ["The email field is required."] }.
  final Map<String, List<String>> errors;

  ApiException(this.statusCode, this.message, [this.errors = const {}]);

  /// The most useful single line to show the user: the first field error if
  /// there is one, otherwise the top-level message.
  String get displayMessage {
    if (errors.isNotEmpty) {
      final first = errors.values.firstWhere(
        (l) => l.isNotEmpty,
        orElse: () => const [],
      );
      if (first.isNotEmpty) return first.first;
    }
    return message;
  }

  @override
  String toString() => 'ApiException($statusCode): $message';
}

/// Thin JSON wrapper around `package:http`.
///
/// Holds the bearer token, sets the standard headers, decodes JSON, and turns
/// error responses into [ApiException]. A single [onUnauthorized] hook lets the
/// auth layer react to an expired/revoked token from anywhere in the app.
class ApiClient {
  ApiClient({this.onUnauthorized});

  final _http = http.Client();

  /// Called whenever the server returns 401 so the app can drop the session.
  void Function()? onUnauthorized;

  String? _token;
  set token(String? value) => _token = value;

  Future<dynamic> get(String path, {Map<String, String>? query}) {
    return _send('GET', path, query: query);
  }

  Future<dynamic> post(String path, {Object? body}) {
    return _send('POST', path, body: body);
  }

  Future<dynamic> patch(String path, {Object? body}) {
    return _send('PATCH', path, body: body);
  }

  Future<dynamic> _send(
    String method,
    String path, {
    Object? body,
    Map<String, String>? query,
  }) async {
    final uri = Uri.parse('${AppConfig.apiBaseUrl}$path').replace(
      queryParameters: query,
    );

    final headers = <String, String>{
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      if (_token != null) 'Authorization': 'Bearer $_token',
    };

    http.Response res;
    try {
      final request = http.Request(method, uri)..headers.addAll(headers);
      if (body != null) request.body = jsonEncode(body);
      final streamed = await _http
          .send(request)
          .timeout(const Duration(seconds: 20));
      res = await http.Response.fromStream(streamed);
    } on TimeoutException {
      throw ApiException(0, 'The server took too long to respond.');
    } on SocketException {
      throw ApiException(
        0,
        'Cannot reach the server. Check that the API is running and that '
        'the address in config.dart is correct.',
      );
    }

    return _handle(res);
  }

  dynamic _handle(http.Response res) {
    final status = res.statusCode;
    final dynamic decoded = res.body.isEmpty ? null : _tryDecode(res.body);

    if (status >= 200 && status < 300) {
      return decoded;
    }

    if (status == 401) {
      onUnauthorized?.call();
      throw ApiException(401, _messageFrom(decoded, 'Your session has expired.'));
    }

    if (status == 422 && decoded is Map<String, dynamic>) {
      final rawErrors = decoded['errors'];
      final errors = <String, List<String>>{};
      if (rawErrors is Map<String, dynamic>) {
        rawErrors.forEach((key, value) {
          if (value is List) {
            errors[key] = value.map((e) => e.toString()).toList();
          }
        });
      }
      throw ApiException(
        422,
        _messageFrom(decoded, 'The given data was invalid.'),
        errors,
      );
    }

    throw ApiException(status, _messageFrom(decoded, 'Something went wrong.'));
  }

  dynamic _tryDecode(String body) {
    try {
      return jsonDecode(body);
    } catch (_) {
      return null;
    }
  }

  String _messageFrom(dynamic decoded, String fallback) {
    if (decoded is Map<String, dynamic> && decoded['message'] is String) {
      final m = decoded['message'] as String;
      if (m.isNotEmpty) return m;
    }
    return fallback;
  }

  void dispose() => _http.close();
}
