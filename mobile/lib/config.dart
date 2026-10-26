/// App-wide configuration.
///
/// [apiBaseUrl] is the server address the app starts with. Users can change it
/// on the sign-in screen ("Server address"), and the choice is saved on the
/// phone, so a new build is only needed to change the default.
///
/// Set the default at build time:
///   flutter build apk --release --dart-define=API_BASE_URL=https://your-domain/api
///
/// A phone on mobile data can only reach a public address (a real domain or a
/// tunnel). A LAN address such as http://192.168.x.x only works on the same
/// Wi-Fi as the computer running the server.
class AppConfig {
  static const String apiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://10.0.2.2:8000/api',
  );

  /// Sent as the token's device label so users can tell sessions apart.
  static const String deviceName = 'Best CPMS mobile';

  /// Turns what a user types into an API base URL:
  /// "example.com" -> "https://example.com/api",
  /// "https://example.com/cpms/" -> "https://example.com/cpms/api".
  static String normalizeBaseUrl(String input) {
    var url = input.trim();
    while (url.endsWith('/')) {
      url = url.substring(0, url.length - 1);
    }
    if (url.isEmpty) return apiBaseUrl;
    if (!url.startsWith('http://') && !url.startsWith('https://')) {
      url = 'https://$url';
    }
    if (!url.endsWith('/api')) {
      url = '$url/api';
    }
    return url;
  }
}
