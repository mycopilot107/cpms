/// App-wide configuration.
///
/// The only thing you normally need to change is [apiBaseUrl] so the app can
/// reach your Laravel server.
///
/// Pick the value that matches how you run the app:
///   • Android emulator  -> http://10.0.2.2:8000/api   (10.0.2.2 = your PC's localhost)
///   • iOS simulator     -> http://127.0.0.1:8000/api
///   • Real phone        -> http://<your-computer-LAN-IP>:8000/api  (e.g. 192.168.1.20)
///
/// Start the API first with:  php artisan serve --host 0.0.0.0 --port 8000
///
/// You can also override this at build/run time without editing the file:
///   flutter run --dart-define=API_BASE_URL=http://192.168.1.20:8000/api
class AppConfig {
  static const String apiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://10.0.2.2:8000/api',
  );

  /// Sent as the token's device label so users can tell sessions apart.
  static const String deviceName = 'Best CPMS mobile';
}
