import 'task_photo.dart';

/// Mirrors App\Http\Resources\DailyReportResource on the server.
class DailyReport {
  final int id;
  final DateTime reportDate;
  final int? projectId;
  final String? projectName;
  final String workDone;
  final int labourCount;
  final Map<String, int> labour;
  final String? notes;
  final bool editable;
  final List<TaskPhoto> photos;
  final DateTime? updatedAt;

  const DailyReport({
    required this.id,
    required this.reportDate,
    this.projectId,
    this.projectName,
    required this.workDone,
    required this.labourCount,
    required this.labour,
    this.notes,
    required this.editable,
    required this.photos,
    this.updatedAt,
  });

  factory DailyReport.fromJson(Map<String, dynamic> json) {
    final project = json['project'] as Map<String, dynamic>?;
    final labour = <String, int>{};
    for (final row in (json['labour'] as List? ?? const [])) {
      final map = row as Map<String, dynamic>;
      labour[map['trade'] as String] = (map['count'] as num).toInt();
    }

    return DailyReport(
      id: json['id'] as int,
      reportDate: DateTime.parse(json['report_date'] as String),
      projectId: project?['id'] as int?,
      projectName: project?['name'] as String?,
      workDone: json['work_done'] as String? ?? '',
      labourCount: (json['labour_count'] as num?)?.toInt() ?? 0,
      labour: labour,
      notes: json['notes'] as String?,
      editable: json['editable'] as bool? ?? false,
      photos: (json['photos'] as List? ?? const [])
          .map((e) => TaskPhoto.fromJson(e as Map<String, dynamic>))
          .toList(),
      updatedAt: json['updated_at'] is String ? DateTime.tryParse(json['updated_at'] as String)?.toLocal() : null,
    );
  }
}

/// An open project the user can report on.
class ReportProject {
  final int id;
  final String name;
  final String? code;

  const ReportProject({required this.id, required this.name, this.code});

  String get label => code != null && code!.isNotEmpty ? '$name · $code' : name;
}
