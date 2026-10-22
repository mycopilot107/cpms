/// Mirrors App\Http\Resources\TaskResource on the server.
///
/// JSON shape:
/// {
///   "id": 12, "title": "...", "description": "...",
///   "status": "in_progress", "priority": "high", "progress": 40,
///   "start_date": "2026-09-01", "due_date": "2026-09-30",
///   "estimated_hours": 12.0, "actual_hours": 4.5, "is_overdue": false,
///   "project": { "id": 3, "name": "...", "code": "RT-001" },
///   "assignee": { "id": 5, "name": "..." }
/// }
class Task {
  final int id;
  final String title;
  final String? description;
  final String status;
  final String priority;
  final int progress;
  final DateTime? startDate;
  final DateTime? dueDate;
  final double? estimatedHours;
  final double? actualHours;
  final bool isOverdue;
  final int? projectId;
  final String? projectName;
  final String? projectCode;
  final String? assigneeName;

  const Task({
    required this.id,
    required this.title,
    this.description,
    required this.status,
    required this.priority,
    required this.progress,
    this.startDate,
    this.dueDate,
    this.estimatedHours,
    this.actualHours,
    required this.isOverdue,
    this.projectId,
    this.projectName,
    this.projectCode,
    this.assigneeName,
  });

  factory Task.fromJson(Map<String, dynamic> json) {
    final project = json['project'] as Map<String, dynamic>?;
    final assignee = json['assignee'] as Map<String, dynamic>?;

    return Task(
      id: json['id'] as int,
      title: json['title'] as String? ?? '',
      description: json['description'] as String?,
      status: json['status'] as String? ?? 'pending',
      priority: json['priority'] as String? ?? 'medium',
      progress: _toInt(json['progress']),
      startDate: _toDate(json['start_date']),
      dueDate: _toDate(json['due_date']),
      estimatedHours: _toDouble(json['estimated_hours']),
      actualHours: _toDouble(json['actual_hours']),
      isOverdue: json['is_overdue'] as bool? ?? false,
      projectId: project?['id'] as int?,
      projectName: project?['name'] as String?,
      projectCode: project?['code'] as String?,
      assigneeName: assignee?['name'] as String?,
    );
  }

  /// Local copy used for optimistic UI updates before the server responds.
  Task copyWith({
    String? status,
    int? progress,
    double? actualHours,
    bool clearActualHours = false,
  }) {
    return Task(
      id: id,
      title: title,
      description: description,
      status: status ?? this.status,
      priority: priority,
      progress: progress ?? this.progress,
      startDate: startDate,
      dueDate: dueDate,
      estimatedHours: estimatedHours,
      actualHours: clearActualHours ? null : (actualHours ?? this.actualHours),
      isOverdue: isOverdue,
      projectId: projectId,
      projectName: projectName,
      projectCode: projectCode,
      assigneeName: assigneeName,
    );
  }

  static int _toInt(dynamic v) {
    if (v is int) return v;
    if (v is num) return v.round();
    if (v is String) return int.tryParse(v) ?? 0;
    return 0;
  }

  static double? _toDouble(dynamic v) {
    if (v == null) return null;
    if (v is num) return v.toDouble();
    if (v is String) return double.tryParse(v);
    return null;
  }

  static DateTime? _toDate(dynamic v) {
    if (v == null) return null;
    if (v is String && v.isNotEmpty) return DateTime.tryParse(v);
    return null;
  }
}
