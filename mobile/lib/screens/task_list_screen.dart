import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../models/task.dart';
import '../services/auth_service.dart';
import '../services/task_service.dart';
import '../theme.dart';
import '../widgets/pills.dart';
import 'task_detail_screen.dart';

class TaskListScreen extends StatefulWidget {
  const TaskListScreen({super.key});

  @override
  State<TaskListScreen> createState() => _TaskListScreenState();
}

class _TaskListScreenState extends State<TaskListScreen> {
  @override
  void initState() {
    super.initState();
    // Load once the first frame is in place (context.read is safe here).
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<TaskService>().load();
    });
  }

  Future<void> _confirmLogout() async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Log out?'),
        content: const Text('You will need to sign in again to log work.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Log out'),
          ),
        ],
      ),
    );
    if (ok == true && mounted) {
      await context.read<AuthService>().logout();
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthService>();
    final service = context.watch<TaskService>();
    final user = auth.user;

    return Scaffold(
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text('My tasks', style: TextStyle(fontSize: 18)),
            if (user != null)
              Text(
                '${user.name}${user.companyName != null ? ' · ${user.companyName}' : ''}',
                style: const TextStyle(fontSize: 12, color: Color(0xFF94A3B8)),
              ),
          ],
        ),
        actions: [
          IconButton(
            tooltip: 'Log out',
            icon: const Icon(Icons.logout),
            onPressed: _confirmLogout,
          ),
        ],
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 4),
            child: SegmentedButton<String>(
              segments: const [
                ButtonSegment(value: 'open', label: Text('Open')),
                ButtonSegment(value: 'all', label: Text('All')),
              ],
              selected: {service.scope},
              onSelectionChanged: (s) =>
                  context.read<TaskService>().setScope(s.first),
            ),
          ),
          Expanded(child: _body(service)),
        ],
      ),
    );
  }

  Widget _body(TaskService service) {
    if (service.loading && service.tasks.isEmpty) {
      return const Center(child: CircularProgressIndicator());
    }

    if (service.error != null && service.tasks.isEmpty) {
      return _message(
        icon: Icons.cloud_off,
        title: 'Could not load tasks',
        detail: service.error!,
        onRetry: () => context.read<TaskService>().load(),
      );
    }

    return RefreshIndicator(
      onRefresh: () => context.read<TaskService>().load(),
      child: service.tasks.isEmpty
          ? _emptyState()
          : ListView.separated(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.all(16),
              itemCount: service.tasks.length,
              separatorBuilder: (_, __) => const SizedBox(height: 12),
              itemBuilder: (_, i) => _TaskCard(task: service.tasks[i]),
            ),
    );
  }

  Widget _emptyState() {
    // Must be scrollable so pull-to-refresh still works when empty.
    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      children: [
        SizedBox(height: MediaQuery.of(context).size.height * 0.2),
        const Icon(Icons.check_circle_outline,
            size: 56, color: Color(0xFF94A3B8)),
        const SizedBox(height: 12),
        const Center(
          child: Text(
            'Nothing here.\nPull down to refresh.',
            textAlign: TextAlign.center,
            style: TextStyle(color: AppTheme.slate500, height: 1.4),
          ),
        ),
      ],
    );
  }

  Widget _message({
    required IconData icon,
    required String title,
    required String detail,
    required VoidCallback onRetry,
  }) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 52, color: const Color(0xFF94A3B8)),
            const SizedBox(height: 12),
            Text(
              title,
              style: const TextStyle(
                fontWeight: FontWeight.bold,
                fontSize: 16,
                color: AppTheme.slate900,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              detail,
              textAlign: TextAlign.center,
              style: const TextStyle(color: AppTheme.slate500),
            ),
            const SizedBox(height: 16),
            FilledButton(onPressed: onRetry, child: const Text('Retry')),
          ],
        ),
      ),
    );
  }
}

class _TaskCard extends StatelessWidget {
  const _TaskCard({required this.task});

  final Task task;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      borderRadius: BorderRadius.circular(14),
      onTap: () {
        Navigator.of(context).push(
          MaterialPageRoute(builder: (_) => TaskDetailScreen(task: task)),
        );
      },
      child: Container(
        decoration: AppTheme.cardDecoration(),
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: Text(
                    task.title,
                    style: const TextStyle(
                      fontWeight: FontWeight.w600,
                      fontSize: 16,
                      color: AppTheme.slate900,
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                StatusPill(task.status),
              ],
            ),
            if (task.projectName != null) ...[
              const SizedBox(height: 4),
              Text(
                task.projectCode != null
                    ? '${task.projectName} · ${task.projectCode}'
                    : task.projectName!,
                style: const TextStyle(color: AppTheme.slate500, fontSize: 13),
              ),
            ],
            const SizedBox(height: 14),
            Row(
              children: [
                Expanded(
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(999),
                    child: LinearProgressIndicator(
                      value: (task.progress.clamp(0, 100)) / 100,
                      minHeight: 8,
                      backgroundColor: AppTheme.slate100,
                      color: AppTheme.indigo,
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Text(
                  '${task.progress}%',
                  style: const TextStyle(
                    fontSize: 12,
                    color: AppTheme.slate500,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 14),
            Row(
              children: [
                PriorityPill(task.priority),
                const Spacer(),
                if (task.dueDate != null) _due(task),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _due(Task task) {
    final overdue = task.isOverdue;
    final color = overdue ? const Color(0xFFB91C1C) : AppTheme.slate500;
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(Icons.event_outlined, size: 15, color: color),
        const SizedBox(width: 4),
        Text(
          DateFormat('d MMM yyyy').format(task.dueDate!),
          style: TextStyle(
            fontSize: 12,
            color: color,
            fontWeight: overdue ? FontWeight.w600 : FontWeight.normal,
          ),
        ),
      ],
    );
  }
}
