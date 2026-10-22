import 'package:flutter/material.dart';

/// Visual theme for the app. Indigo primary to match the Best CPMS web UI.
class AppTheme {
  static const Color indigo = Color(0xFF4F46E5); // indigo-600
  static const Color slate900 = Color(0xFF0F172A);
  static const Color slate500 = Color(0xFF64748B);
  static const Color slate100 = Color(0xFFF1F5F9);

  /// Reusable "card" surface — white with a slate border and rounded corners.
  /// Used instead of themed [Card] to stay compatible across Flutter versions.
  static BoxDecoration cardDecoration() {
    return BoxDecoration(
      color: Colors.white,
      borderRadius: BorderRadius.circular(14),
      border: Border.all(color: const Color(0xFFE2E8F0)),
    );
  }

  static ThemeData light() {
    final scheme = ColorScheme.fromSeed(
      seedColor: indigo,
      primary: indigo,
    );

    return ThemeData(
      useMaterial3: true,
      colorScheme: scheme,
      scaffoldBackgroundColor: slate100,
      appBarTheme: const AppBarTheme(
        backgroundColor: slate900,
        foregroundColor: Colors.white,
        elevation: 0,
        centerTitle: false,
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: Colors.white,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(10),
          borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
        ),
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          backgroundColor: indigo,
          foregroundColor: Colors.white,
          minimumSize: const Size.fromHeight(50),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(10),
          ),
        ),
      ),
    );
  }

  /// Background / foreground colors for a task status pill.
  static (Color, Color) statusColors(String status) {
    switch (status) {
      case 'completed':
        return (const Color(0xFFDCFCE7), const Color(0xFF15803D)); // emerald
      case 'in_progress':
        return (const Color(0xFFDBEAFE), const Color(0xFF1D4ED8)); // blue
      case 'cancelled':
        return (const Color(0xFFE2E8F0), const Color(0xFF475569)); // slate
      case 'pending':
      default:
        return (const Color(0xFFFEF9C3), const Color(0xFF854D0E)); // amber
    }
  }

  /// Background / foreground colors for a priority pill.
  static (Color, Color) priorityColors(String priority) {
    switch (priority) {
      case 'urgent':
        return (const Color(0xFFFEE2E2), const Color(0xFFB91C1C)); // red
      case 'high':
        return (const Color(0xFFFFEDD5), const Color(0xFFC2410C)); // orange
      case 'medium':
        return (const Color(0xFFDBEAFE), const Color(0xFF1D4ED8)); // blue
      case 'low':
      default:
        return (const Color(0xFFE2E8F0), const Color(0xFF475569)); // slate
    }
  }

  /// Human-friendly label for a status/priority code.
  static String label(String raw) {
    return raw
        .split('_')
        .map((w) => w.isEmpty ? w : '${w[0].toUpperCase()}${w.substring(1)}')
        .join(' ');
  }
}
