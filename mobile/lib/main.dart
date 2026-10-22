import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'screens/login_screen.dart';
import 'screens/task_list_screen.dart';
import 'services/api_client.dart';
import 'services/auth_service.dart';
import 'services/task_service.dart';
import 'theme.dart';

void main() {
  // One HTTP client, shared by the auth and task services so they use the
  // same bearer token.
  final api = ApiClient();
  runApp(CpmsApp(api: api));
}

class CpmsApp extends StatelessWidget {
  const CpmsApp({super.key, required this.api});

  final ApiClient api;

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        Provider<ApiClient>.value(value: api),
        ChangeNotifierProvider(create: (_) => AuthService(api)..bootstrap()),
        ChangeNotifierProvider(create: (_) => TaskService(api)),
      ],
      child: MaterialApp(
        title: 'Best CPMS',
        debugShowCheckedModeBanner: false,
        theme: AppTheme.light(),
        home: const _AuthGate(),
      ),
    );
  }
}

/// Chooses the first screen based on the session state.
class _AuthGate extends StatelessWidget {
  const _AuthGate();

  @override
  Widget build(BuildContext context) {
    final status = context.watch<AuthService>().status;

    switch (status) {
      case AuthStatus.unknown:
        return const _Splash();
      case AuthStatus.authenticated:
        return const TaskListScreen();
      case AuthStatus.unauthenticated:
        return const LoginScreen();
    }
  }
}

class _Splash extends StatelessWidget {
  const _Splash();

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      body: Center(child: CircularProgressIndicator()),
    );
  }
}
