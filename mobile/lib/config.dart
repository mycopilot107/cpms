/// App-wide configuration.
///
/// The app always talks to the Best CPMS server. To point a build somewhere
/// else (for example a staging server), pass it at build time:
///   flutter build apk --release --dart-define=API_BASE_URL=https://staging.example.com/api
class AppConfig {
  static const String apiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'https://bestcpms.com/api',
  );

  /// Sent as the token's device label so users can tell sessions apart.
  static const String deviceName = 'Best CPMS mobile';
}
