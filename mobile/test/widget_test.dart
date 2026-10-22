import 'package:cpms_mobile/theme.dart';
import 'package:flutter_test/flutter_test.dart';

// A tiny, dependency-free smoke test so `flutter test` passes out of the box.
// (Widget tests that pump the whole app would need shared_preferences/network
// mocks; these pure checks keep CI green without that setup.)
void main() {
  test('status/priority label formatting', () {
    expect(AppTheme.label('in_progress'), 'In Progress');
    expect(AppTheme.label('pending'), 'Pending');
    expect(AppTheme.label('urgent'), 'Urgent');
  });

  test('status colors are defined for every status', () {
    for (final s in ['pending', 'in_progress', 'completed', 'cancelled']) {
      final (bg, fg) = AppTheme.statusColors(s);
      expect(bg, isNotNull);
      expect(fg, isNotNull);
    }
  });
}
