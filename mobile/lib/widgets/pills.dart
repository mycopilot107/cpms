import 'package:flutter/material.dart';

import '../theme.dart';

/// A small rounded label (status / priority chip).
class Pill extends StatelessWidget {
  const Pill({super.key, required this.text, required this.bg, required this.fg});

  final String text;
  final Color bg;
  final Color fg;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(999),
      ),
      child: Text(
        text,
        style: TextStyle(color: fg, fontSize: 12, fontWeight: FontWeight.w600),
      ),
    );
  }
}

class StatusPill extends StatelessWidget {
  const StatusPill(this.status, {super.key});

  final String status;

  @override
  Widget build(BuildContext context) {
    final (bg, fg) = AppTheme.statusColors(status);
    return Pill(text: AppTheme.label(status), bg: bg, fg: fg);
  }
}

class PriorityPill extends StatelessWidget {
  const PriorityPill(this.priority, {super.key});

  final String priority;

  @override
  Widget build(BuildContext context) {
    final (bg, fg) = AppTheme.priorityColors(priority);
    return Pill(text: AppTheme.label(priority), bg: bg, fg: fg);
  }
}
