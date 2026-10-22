import 'package:flutter/foundation.dart';

import '../models/task.dart';
import 'api_client.dart';

/// Loads and mutates the caller's assigned tasks.
///
/// Holds the list state for the "My tasks" screen (list + loading + error +
/// current scope) and exposes a single [updateTask] used by the detail screen.
class TaskService extends ChangeNotifier {
  TaskService(this._api);

  final ApiClient _api;

  List<Task> _tasks = const [];
  bool _loading = false;
  String? _error;
  String _scope = 'open'; // 'open' | 'all'

  List<Task> get tasks => _tasks;
  bool get loading => _loading;
  String? get error => _error;
  String get scope => _scope;

  Future<void> setScope(String scope) async {
    if (_scope == scope) return;
    _scope = scope;
    await load();
  }

  /// Fetch the current scope's tasks. Never throws — errors land in [error].
  Future<void> load() async {
    _loading = true;
    _error = null;
    notifyListeners();

    try {
      final body = await _api.get('/tasks', query: {'scope': _scope});
      final data = (body is Map<String, dynamic>) ? body['data'] : body;
      final list = (data as List)
          .map((e) => Task.fromJson(e as Map<String, dynamic>))
          .toList();
      _tasks = list;
    } on ApiException catch (e) {
      _error = e.displayMessage;
    } finally {
      _loading = false;
      notifyListeners();
    }
  }

  /// PATCH a task's progress fields. Returns the server's updated Task and
  /// reconciles it into the in-memory list. Throws [ApiException] so the
  /// caller (detail screen) can surface the message.
  Future<Task> updateTask(
    int id, {
    String? status,
    int? progress,
    double? actualHours,
    bool includeActualHours = false,
  }) async {
    final payload = <String, dynamic>{};
    if (status != null) payload['status'] = status;
    if (progress != null) payload['progress'] = progress;
    if (includeActualHours) payload['actual_hours'] = actualHours;

    final body = await _api.patch('/tasks/$id', body: payload);
    final data = (body is Map<String, dynamic>) ? body['data'] : body;
    final updated = Task.fromJson(data as Map<String, dynamic>);

    _reconcile(updated);
    return updated;
  }

  /// Keep the visible list consistent after an update: drop a task that no
  /// longer belongs in the "open" scope, otherwise replace it in place.
  void _reconcile(Task updated) {
    final dropFromOpen = _scope == 'open' &&
        (updated.status == 'completed' || updated.status == 'cancelled');

    final next = List<Task>.from(_tasks);
    final index = next.indexWhere((t) => t.id == updated.id);

    if (dropFromOpen) {
      if (index != -1) next.removeAt(index);
    } else if (index != -1) {
      next[index] = updated;
    }

    _tasks = next;
    notifyListeners();
  }
}
