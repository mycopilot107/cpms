import 'dart:io';

import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../models/daily_report.dart';
import '../models/task_photo.dart';
import '../services/api_client.dart';
import '../services/daily_report_service.dart';
import '../theme.dart';

/// The daily site report: pick the project and day, write what happened,
/// count labour by trade, add a note and photos, then submit. Saving again
/// the same day updates the same report.
class DailyReportScreen extends StatefulWidget {
  const DailyReportScreen({super.key});

  @override
  State<DailyReportScreen> createState() => _DailyReportScreenState();
}

class _DailyReportScreenState extends State<DailyReportScreen> {
  static const _editWindowDays = 3;

  final _picker = ImagePicker();
  final _workCtrl = TextEditingController();
  final _notesCtrl = TextEditingController();

  List<ReportProject> _projects = const [];
  List<String> _trades = const [];
  List<DailyReport> _recent = const [];

  ReportProject? _project;
  DateTime _date = _today();
  Map<String, int> _labour = {};
  DailyReport? _existing;
  final List<XFile> _pendingPhotos = [];

  bool _loading = true;
  bool _loadingReport = false;
  bool _saving = false;
  String? _progressText;
  String? _error;

  static DateTime _today() {
    final now = DateTime.now();
    return DateTime(now.year, now.month, now.day);
  }

  DailyReportService get _service => context.read<DailyReportService>();

  int get _labourTotal => _labour.values.fold(0, (sum, count) => sum + count);

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _init());
  }

  @override
  void dispose() {
    _workCtrl.dispose();
    _notesCtrl.dispose();
    super.dispose();
  }

  Future<void> _init() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final options = await _service.options();
      final recent = await _service.recent();
      if (!mounted) return;
      setState(() {
        _projects = options.projects;
        _trades = options.trades;
        _recent = recent;
        _project ??= _projects.length == 1 ? _projects.first : _lastUsedProject();
      });
      await _loadReport();
    } on ApiException catch (e) {
      if (mounted) setState(() => _error = e.displayMessage);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  ReportProject? _lastUsedProject() {
    final lastId = _recent.isNotEmpty ? _recent.first.projectId : null;
    for (final project in _projects) {
      if (project.id == lastId) return project;
    }
    return null;
  }

  /// Load what was already saved for this project and day, or start blank.
  Future<void> _loadReport() async {
    final project = _project;
    if (project == null) return;

    setState(() => _loadingReport = true);
    try {
      final report = await _service.find(project.id, _date);
      if (!mounted) return;
      setState(() {
        _existing = report;
        _workCtrl.text = report?.workDone ?? '';
        _notesCtrl.text = report?.notes ?? '';
        _labour = {for (final trade in _trades) trade: 0, ...?report?.labour};
        if (report != null && report.labour.isEmpty && report.labourCount > 0) {
          _labour['Labour'] = report.labourCount;
        }
        _pendingPhotos.clear();
      });
    } on ApiException catch (e) {
      if (mounted) _snack(e.displayMessage, error: true);
    } finally {
      if (mounted) setState(() => _loadingReport = false);
    }
  }

  Future<void> _addPhoto(ImageSource source) async {
    try {
      if (source == ImageSource.gallery) {
        final picked = await _picker.pickMultiImage(maxWidth: 1920, maxHeight: 1920, imageQuality: 80, limit: 10);
        if (picked.isNotEmpty && mounted) setState(() => _pendingPhotos.addAll(picked));
      } else {
        final picked = await _picker.pickImage(source: source, maxWidth: 1920, maxHeight: 1920, imageQuality: 80);
        if (picked != null && mounted) setState(() => _pendingPhotos.add(picked));
      }
    } catch (_) {
      _snack(source == ImageSource.camera ? 'Could not open the camera.' : 'Could not open your photos.', error: true);
    }
  }

  Future<void> _submit() async {
    final project = _project;
    if (project == null) {
      _snack('Choose the project first.', error: true);
      return;
    }
    if (_workCtrl.text.trim().isEmpty) {
      _snack('Write what happened on site today.', error: true);
      return;
    }

    setState(() {
      _saving = true;
      _progressText = 'Saving report…';
    });

    try {
      final saved = await _service.save(
        projectId: project.id,
        date: _date,
        workDone: _workCtrl.text.trim(),
        labour: _labour,
        notes: _notesCtrl.text.trim(),
      );

      final uploaded = <TaskPhoto>[];
      final failed = <XFile>[];
      for (var i = 0; i < _pendingPhotos.length; i++) {
        if (mounted) setState(() => _progressText = 'Uploading photo ${i + 1} of ${_pendingPhotos.length}…');
        try {
          uploaded.add(await _service.uploadPhoto(saved.id, _pendingPhotos[i].path));
        } on ApiException {
          failed.add(_pendingPhotos[i]);
        }
      }

      final recent = await _service.recent();
      if (!mounted) return;
      setState(() {
        _existing = DailyReport(
          id: saved.id,
          reportDate: saved.reportDate,
          projectId: saved.projectId,
          projectName: saved.projectName,
          workDone: saved.workDone,
          labourCount: saved.labourCount,
          labour: saved.labour,
          notes: saved.notes,
          editable: saved.editable,
          photos: [...uploaded.reversed, ...saved.photos],
          updatedAt: saved.updatedAt,
        );
        _pendingPhotos
          ..clear()
          ..addAll(failed);
        _recent = recent;
      });

      _snack(failed.isEmpty
          ? 'Report saved. The office can see it now.'
          : 'Report saved, but ${failed.length} photo(s) did not upload. Tap Submit again to retry them.',
          error: failed.isNotEmpty);
    } on ApiException catch (e) {
      if (mounted) _snack(e.displayMessage, error: true);
    } finally {
      if (mounted) {
        setState(() {
          _saving = false;
          _progressText = null;
        });
      }
    }
  }

  Future<void> _deleteSavedPhoto(TaskPhoto photo) async {
    try {
      await _service.deletePhoto(photo.id);
      final existing = _existing;
      if (!mounted || existing == null) return;
      setState(() {
        _existing = DailyReport(
          id: existing.id,
          reportDate: existing.reportDate,
          projectId: existing.projectId,
          projectName: existing.projectName,
          workDone: existing.workDone,
          labourCount: existing.labourCount,
          labour: existing.labour,
          notes: existing.notes,
          editable: existing.editable,
          photos: existing.photos.where((p) => p.id != photo.id).toList(),
          updatedAt: existing.updatedAt,
        );
      });
    } on ApiException catch (e) {
      if (mounted) _snack(e.displayMessage, error: true);
    }
  }

  Future<void> _addOtherTrade() async {
    final controller = TextEditingController();
    final name = await showDialog<String>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: const Text('Add a trade'),
        content: TextField(
          controller: controller,
          autofocus: true,
          maxLength: 50,
          textCapitalization: TextCapitalization.words,
          decoration: const InputDecoration(hintText: 'e.g. Tile fixer'),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(dialogContext), child: const Text('Cancel')),
          TextButton(onPressed: () => Navigator.pop(dialogContext, controller.text.trim()), child: const Text('Add')),
        ],
      ),
    ).whenComplete(controller.dispose);

    if (name != null && name.isNotEmpty && mounted) {
      setState(() => _labour.putIfAbsent(name, () => 0));
    }
  }

  void _snack(String message, {bool error = false}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), backgroundColor: error ? const Color(0xFFB91C1C) : null),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Daily site report')),
      body: _body(),
      bottomNavigationBar: _project == null || _loading
          ? null
          : SafeArea(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(16, 8, 16, 12),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    if (_progressText != null) ...[
                      const LinearProgressIndicator(),
                      const SizedBox(height: 6),
                      Text(_progressText!, style: const TextStyle(color: AppTheme.slate500, fontSize: 13)),
                      const SizedBox(height: 6),
                    ],
                    FilledButton(
                      onPressed: _saving || _loadingReport ? null : _submit,
                      child: Text(_existing == null ? 'Submit report' : 'Update report'),
                    ),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _body() {
    if (_loading) return const Center(child: CircularProgressIndicator());

    if (_error != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.cloud_off, size: 52, color: Color(0xFF94A3B8)),
              const SizedBox(height: 12),
              Text(_error!, textAlign: TextAlign.center, style: const TextStyle(color: AppTheme.slate500)),
              const SizedBox(height: 16),
              FilledButton(onPressed: _init, child: const Text('Retry')),
            ],
          ),
        ),
      );
    }

    if (_projects.isEmpty) {
      return const Center(
        child: Padding(
          padding: EdgeInsets.all(32),
          child: Text('There are no open projects to report on yet.', textAlign: TextAlign.center, style: TextStyle(color: AppTheme.slate500)),
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _init,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          _whereAndWhenCard(),
          if (_loadingReport)
            const Padding(padding: EdgeInsets.all(24), child: Center(child: CircularProgressIndicator()))
          else if (_project != null) ...[
            const SizedBox(height: 16),
            _card(
              title: 'What happened today',
              child: TextField(
                controller: _workCtrl,
                enabled: !_saving,
                minLines: 4,
                maxLines: 10,
                maxLength: 5000,
                textCapitalization: TextCapitalization.sentences,
                decoration: const InputDecoration(hintText: 'e.g. 3rd floor slab shuttering finished, steel binding started on grid C.'),
              ),
            ),
            const SizedBox(height: 16),
            _labourCard(),
            const SizedBox(height: 16),
            _card(
              title: 'Note',
              subtitle: 'Optional — problems, material shortage, visitors, weather.',
              child: TextField(
                controller: _notesCtrl,
                enabled: !_saving,
                minLines: 2,
                maxLines: 6,
                maxLength: 5000,
                textCapitalization: TextCapitalization.sentences,
                decoration: const InputDecoration(hintText: 'e.g. Cement for 2 days only. Rain stopped work after 3 pm.'),
              ),
            ),
            const SizedBox(height: 16),
            _photosCard(),
          ],
          if (_recent.isNotEmpty) ...[
            const SizedBox(height: 24),
            const Text('Your recent reports', style: TextStyle(fontWeight: FontWeight.bold, color: AppTheme.slate900)),
            const SizedBox(height: 8),
            for (final report in _recent.take(10)) _recentTile(report),
          ],
        ],
      ),
    );
  }

  Widget _whereAndWhenCard() {
    final days = List.generate(_editWindowDays + 1, (i) => _today().subtract(Duration(days: i)));
    String dayLabel(DateTime day) => switch (_today().difference(day).inDays) {
          0 => 'Today',
          1 => 'Yesterday',
          _ => DateFormat('EEE d MMM').format(day),
        };

    return _card(
      title: 'Project and day',
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          DropdownButtonFormField<int>(
            key: ValueKey(_project?.id),
            initialValue: _project?.id,
            isExpanded: true,
            hint: const Text('Choose the project'),
            items: [for (final p in _projects) DropdownMenuItem(value: p.id, child: Text(p.label, overflow: TextOverflow.ellipsis))],
            onChanged: _saving
                ? null
                : (id) {
                    setState(() => _project = _projects.firstWhere((p) => p.id == id));
                    _loadReport();
                  },
          ),
          const SizedBox(height: 12),
          Wrap(
            spacing: 8,
            children: [
              for (final day in days)
                ChoiceChip(
                  label: Text(dayLabel(day)),
                  selected: day == _date,
                  onSelected: _saving
                      ? null
                      : (_) {
                          setState(() => _date = day);
                          _loadReport();
                        },
                ),
            ],
          ),
          if (_existing != null && _existing!.updatedAt != null) ...[
            const SizedBox(height: 8),
            Text(
              'You already saved this report at ${DateFormat('h:mm a').format(_existing!.updatedAt!)}. Changes will update it.',
              style: const TextStyle(color: AppTheme.slate500, fontSize: 13),
            ),
          ],
        ],
      ),
    );
  }

  Widget _labourCard() {
    return _card(
      title: 'Labour on site',
      trailing: Text('$_labourTotal', style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: AppTheme.slate900)),
      child: Column(
        children: [
          for (final trade in _labour.keys) _counterRow(trade),
          Align(
            alignment: Alignment.centerLeft,
            child: TextButton.icon(
              onPressed: _saving ? null : _addOtherTrade,
              icon: const Icon(Icons.add),
              label: const Text('Other trade'),
            ),
          ),
        ],
      ),
    );
  }

  Widget _counterRow(String trade) {
    final count = _labour[trade] ?? 0;

    void change(int delta) => setState(() => _labour[trade] = (count + delta).clamp(0, 2000));

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2),
      child: Row(
        children: [
          Expanded(child: Text(trade, style: TextStyle(color: count > 0 ? AppTheme.slate900 : AppTheme.slate500, fontWeight: count > 0 ? FontWeight.w600 : FontWeight.normal))),
          IconButton(
            visualDensity: VisualDensity.compact,
            onPressed: _saving || count == 0 ? null : () => change(-1),
            icon: const Icon(Icons.remove_circle_outline),
          ),
          SizedBox(
            width: 36,
            child: Text('$count', textAlign: TextAlign.center, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600)),
          ),
          IconButton(
            visualDensity: VisualDensity.compact,
            onPressed: _saving ? null : () => change(1),
            icon: const Icon(Icons.add_circle_outline, color: AppTheme.indigo),
          ),
          IconButton(
            visualDensity: VisualDensity.compact,
            tooltip: '+5',
            onPressed: _saving ? null : () => change(5),
            icon: const Text('+5', style: TextStyle(color: AppTheme.indigo, fontWeight: FontWeight.w600)),
          ),
        ],
      ),
    );
  }

  Widget _photosCard() {
    final saved = _existing?.photos ?? const <TaskPhoto>[];
    final headers = _service.imageHeaders;

    return _card(
      title: 'Photos',
      subtitle: 'Photos are sent when you submit.',
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(
            children: [
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: _saving ? null : () => _addPhoto(ImageSource.camera),
                  icon: const Icon(Icons.photo_camera_outlined),
                  label: const Text('Take photo'),
                  style: OutlinedButton.styleFrom(minimumSize: const Size.fromHeight(46)),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: _saving ? null : () => _addPhoto(ImageSource.gallery),
                  icon: const Icon(Icons.photo_library_outlined),
                  label: const Text('Gallery'),
                  style: OutlinedButton.styleFrom(minimumSize: const Size.fromHeight(46)),
                ),
              ),
            ],
          ),
          if (saved.isNotEmpty || _pendingPhotos.isNotEmpty) ...[
            const SizedBox(height: 12),
            GridView.count(
              crossAxisCount: 3,
              mainAxisSpacing: 8,
              crossAxisSpacing: 8,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              children: [
                for (final file in _pendingPhotos)
                  _thumb(
                    Image.file(File(file.path), fit: BoxFit.cover),
                    badge: 'Not sent',
                    onRemove: _saving ? null : () => setState(() => _pendingPhotos.remove(file)),
                  ),
                for (final photo in saved)
                  _thumb(
                    Image.network(photo.thumbUrl, headers: headers, fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => const Icon(Icons.broken_image_outlined, color: AppTheme.slate500)),
                    onRemove: photo.canDelete && !_saving ? () => _deleteSavedPhoto(photo) : null,
                  ),
              ],
            ),
          ],
        ],
      ),
    );
  }

  Widget _thumb(Widget image, {String? badge, VoidCallback? onRemove}) {
    return ClipRRect(
      borderRadius: BorderRadius.circular(8),
      child: Stack(
        fit: StackFit.expand,
        children: [
          Container(color: AppTheme.slate100, child: image),
          if (badge != null)
            Positioned(
              left: 4,
              bottom: 4,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                decoration: BoxDecoration(color: Colors.black54, borderRadius: BorderRadius.circular(4)),
                child: Text(badge, style: const TextStyle(color: Colors.white, fontSize: 11)),
              ),
            ),
          if (onRemove != null)
            Positioned(
              right: 2,
              top: 2,
              child: InkWell(
                onTap: onRemove,
                child: const CircleAvatar(radius: 12, backgroundColor: Colors.black54, child: Icon(Icons.close, size: 14, color: Colors.white)),
              ),
            ),
        ],
      ),
    );
  }

  Widget _recentTile(DailyReport report) {
    final project = _projects.where((p) => p.id == report.projectId).firstOrNull;

    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      decoration: AppTheme.cardDecoration(),
      child: ListTile(
        title: Text('${DateFormat('EEE d MMM').format(report.reportDate)} · ${report.projectName ?? ''}', overflow: TextOverflow.ellipsis),
        subtitle: Text(report.workDone, maxLines: 1, overflow: TextOverflow.ellipsis),
        trailing: Text('${report.labourCount}\nlabour', textAlign: TextAlign.right, style: const TextStyle(fontSize: 12, color: AppTheme.slate500)),
        onTap: report.editable && project != null && !_saving
            ? () {
                setState(() {
                  _project = project;
                  _date = report.reportDate;
                });
                _loadReport();
              }
            : null,
      ),
    );
  }

  Widget _card({required String title, String? subtitle, Widget? trailing, required Widget child}) {
    return Container(
      decoration: AppTheme.cardDecoration(),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(child: Text(title, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppTheme.slate900))),
              if (trailing != null) trailing,
            ],
          ),
          if (subtitle != null) ...[
            const SizedBox(height: 2),
            Text(subtitle, style: const TextStyle(color: AppTheme.slate500, fontSize: 13)),
          ],
          const SizedBox(height: 12),
          child,
        ],
      ),
    );
  }
}
