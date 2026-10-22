import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../models/task.dart';
import '../services/api_client.dart';
import '../services/task_service.dart';
import '../theme.dart';
import '../widgets/pills.dart';

/// The four task statuses, mirroring Task::STATUSES on the server.
const List<String> kStatuses = ['pending', 'in_progress', 'completed', 'cancelled'];

class TaskDetailScreen extends StatefulWidget {
  const TaskDetailScreen({super.key, required this.task});

  final Task task;

  @override
  State<TaskDetailScreen> createState() => _TaskDetailScreenState();
}

class _TaskDetailScreenState extends State<TaskDetailScreen> {
  late String _status;
  late double _progress;
  late final TextEditingController _hoursCtrl;
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    _status = widget.task.status;
    _progress = widget.task.progress.clamp(0, 100).toDouble();
    _hoursCtrl = TextEditingController(text: _formatHours(widget.task.actualHours));
  }

  @override
  void dispose() {
    _hoursCtrl.dispose();
    super.dispose();
  }

  static String _formatHours(double? h) {
    if (h == null) return '';
    return h == h.roundToDouble() ? h.toInt().toString() : h.toString();
  }

  Future<void> _save() async {
    final raw = _hoursCtrl.text.trim();
    double? hours;
    if (raw.isNotEmpty) {
      hours = double.tryParse(raw);
      if (hours == null || hours < 0) {
        _snack('Enter a valid number of hours (0 or more).', error: true);
        return;
      }
    }

    setState(() => _saving = true);
    try {
      await context.read<TaskService>().updateTask(
            widget.task.id,
            status: _status,
            progress: _progress.round(),
            actualHours: hours,
            includeActualHours: true,
          );
      if (!mounted) return;
      _snack('Work updated.');
      Navigator.of(context).pop();
    } on ApiException catch (e) {
      if (mounted) _snack(e.displayMessage, error: true);
    } catch (_) {
      if (mounted) _snack('Unexpected error. Please try again.', error: true);
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  void _snack(String message, {bool error = false}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: error ? const Color(0xFFB91C1C) : null,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final task = widget.task;

    return Scaffold(
      appBar: AppBar(title: const Text('Task')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          _detailsCard(task),
          const SizedBox(height: 16),
          _updateCard(),
        ],
      ),
    );
  }

  Widget _detailsCard(Task task) {
    return Container(
      decoration: AppTheme.cardDecoration(),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            task.title,
            style: const TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.bold,
              color: AppTheme.slate900,
            ),
          ),
          const SizedBox(height: 10),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              StatusPill(_status),
              PriorityPill(task.priority),
            ],
          ),
          const SizedBox(height: 16),
          if (task.projectName != null)
            _row('Project', task.projectCode != null
                ? '${task.projectName} · ${task.projectCode}'
                : task.projectName!),
          if (task.assigneeName != null) _row('Assignee', task.assigneeName!),
          _row('Due', task.dueDate != null
              ? DateFormat('d MMM yyyy').format(task.dueDate!)
              : '—', highlight: task.isOverdue),
          if (task.estimatedHours != null)
            _row('Estimated hours', _formatHours(task.estimatedHours)),
          if (task.description != null && task.description!.trim().isNotEmpty) ...[
            const Divider(height: 24),
            Text(
              task.description!,
              style: const TextStyle(color: Color(0xFF475569), height: 1.4),
            ),
          ],
        ],
      ),
    );
  }

  Widget _row(String label, String value, {bool highlight = false}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 120,
            child: Text(
              label,
              style: const TextStyle(color: AppTheme.slate500, fontSize: 13),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: TextStyle(
                color: highlight ? const Color(0xFFB91C1C) : AppTheme.slate900,
                fontWeight: highlight ? FontWeight.w600 : FontWeight.normal,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _updateCard() {
    return Container(
      decoration: AppTheme.cardDecoration(),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Update your work',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: AppTheme.slate900,
            ),
          ),
          const SizedBox(height: 16),

          // Status
          const Text('Status',
              style: TextStyle(color: AppTheme.slate500, fontSize: 13)),
          const SizedBox(height: 6),
          DropdownButtonFormField<String>(
            value: _status,
            items: [
              for (final s in kStatuses)
                DropdownMenuItem(value: s, child: Text(AppTheme.label(s))),
            ],
            onChanged: _saving
                ? null
                : (v) {
                    if (v == null) return;
                    setState(() {
                      _status = v;
                      // Sensible default: completing a task implies 100%.
                      if (v == 'completed') _progress = 100;
                    });
                  },
          ),
          const SizedBox(height: 20),

          // Progress
          Row(
            children: [
              const Text('Progress',
                  style: TextStyle(color: AppTheme.slate500, fontSize: 13)),
              const Spacer(),
              Text(
                '${_progress.round()}%',
                style: const TextStyle(
                  fontWeight: FontWeight.w700,
                  color: AppTheme.slate900,
                ),
              ),
            ],
          ),
          Slider(
            value: _progress,
            min: 0,
            max: 100,
            divisions: 20,
            label: '${_progress.round()}%',
            activeColor: AppTheme.indigo,
            onChanged: _saving
                ? null
                : (v) => setState(() => _progress = v),
          ),
          const SizedBox(height: 12),

          // Actual hours
          const Text('Hours worked (actual)',
              style: TextStyle(color: AppTheme.slate500, fontSize: 13)),
          const SizedBox(height: 6),
          TextField(
            controller: _hoursCtrl,
            enabled: !_saving,
            keyboardType: const TextInputType.numberWithOptions(decimal: true),
            inputFormatters: [
              FilteringTextInputFormatter.allow(RegExp(r'[0-9.]')),
            ],
            decoration: const InputDecoration(
              hintText: 'e.g. 6.5 — leave blank to clear',
              prefixIcon: Icon(Icons.timer_outlined),
            ),
          ),
          const SizedBox(height: 24),

          FilledButton(
            onPressed: _saving ? null : _save,
            child: _saving
                ? const SizedBox(
                    height: 22,
                    width: 22,
                    child: CircularProgressIndicator(
                      strokeWidth: 2.5,
                      color: Colors.white,
                    ),
                  )
                : const Text('Save update'),
          ),
        ],
      ),
    );
  }
}
