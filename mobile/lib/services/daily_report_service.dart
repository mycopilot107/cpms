import 'package:intl/intl.dart';

import '../models/daily_report.dart';
import '../models/task_photo.dart';
import 'api_client.dart';

/// Daily site reports: open projects, the caller's reports, saving, photos.
/// Every method throws [ApiException] on failure.
class DailyReportService {
  DailyReportService(this._api);

  final ApiClient _api;

  static final _day = DateFormat('yyyy-MM-dd');

  /// Open projects plus the trade names offered in the labour counter.
  Future<({List<ReportProject> projects, List<String> trades})> options() async {
    final body = await _api.get('/daily-reports/projects') as Map<String, dynamic>;
    final projects = (body['data'] as List)
        .map((e) => e as Map<String, dynamic>)
        .map((e) => ReportProject(id: e['id'] as int, name: e['name'] as String, code: e['code'] as String?))
        .toList();
    final trades = (body['trades'] as List? ?? const []).map((e) => e.toString()).toList();

    return (projects: projects, trades: trades);
  }

  Future<List<DailyReport>> recent() async {
    final body = await _api.get('/daily-reports');
    return _list(body);
  }

  /// The caller's report for that project and day, if one was saved.
  Future<DailyReport?> find(int projectId, DateTime date) async {
    final body = await _api.get('/daily-reports', query: {
      'project_id': '$projectId',
      'date': _day.format(date),
    });
    final reports = _list(body);
    return reports.isEmpty ? null : reports.first;
  }

  Future<DailyReport> save({
    required int projectId,
    required DateTime date,
    required String workDone,
    required Map<String, int> labour,
    String? notes,
  }) async {
    final rows = labour.entries.where((e) => e.value > 0).map((e) => {'trade': e.key, 'count': e.value}).toList();

    final body = await _api.post('/daily-reports', body: {
      'project_id': projectId,
      'report_date': _day.format(date),
      'work_done': workDone,
      if (rows.isNotEmpty) 'labour': rows else 'labour_count': 0,
      'notes': notes,
    });

    return DailyReport.fromJson((body as Map<String, dynamic>)['data'] as Map<String, dynamic>);
  }

  Future<TaskPhoto> uploadPhoto(int reportId, String filePath) async {
    final body = await _api.upload('/daily-reports/$reportId/photos', field: 'photo', filePath: filePath);
    return TaskPhoto.fromJson((body as Map<String, dynamic>)['data'] as Map<String, dynamic>);
  }

  Future<void> deletePhoto(int photoId) async {
    await _api.delete('/daily-report-photos/$photoId');
  }

  Map<String, String> get imageHeaders => _api.imageHeaders;

  List<DailyReport> _list(dynamic body) {
    final data = (body is Map<String, dynamic>) ? body['data'] : body;
    return (data as List).map((e) => DailyReport.fromJson(e as Map<String, dynamic>)).toList();
  }
}
