import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../models/task_photo.dart';
import '../services/api_client.dart';
import '../services/task_service.dart';
import '../theme.dart';

/// Site photos on a task: take one with the camera or pick from the gallery,
/// and see what has already been added (by you or the office).
class TaskPhotosCard extends StatefulWidget {
  const TaskPhotosCard({super.key, required this.taskId});

  final int taskId;

  @override
  State<TaskPhotosCard> createState() => _TaskPhotosCardState();
}

class _TaskPhotosCardState extends State<TaskPhotosCard> {
  final _picker = ImagePicker();

  List<TaskPhoto> _photos = const [];
  bool _loading = true;
  bool _uploading = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    try {
      final photos = await context.read<TaskService>().photos(widget.taskId);
      if (mounted) setState(() => _photos = photos);
    } on ApiException catch (e) {
      if (mounted) setState(() => _error = e.displayMessage);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _add(ImageSource source) async {
    final XFile? picked;
    try {
      // The phone shrinks the photo before upload, which keeps it quick on mobile data.
      picked = await _picker.pickImage(source: source, maxWidth: 1920, maxHeight: 1920, imageQuality: 80);
    } catch (_) {
      _snack(source == ImageSource.camera ? 'Could not open the camera.' : 'Could not open your photos.', error: true);
      return;
    }
    if (picked == null || !mounted) return;

    final caption = await _askCaption();
    if (caption == null || !mounted) return;

    setState(() => _uploading = true);
    try {
      final photo = await context.read<TaskService>().uploadPhoto(widget.taskId, picked.path, caption: caption);
      if (!mounted) return;
      setState(() => _photos = [photo, ..._photos]);
      _snack('Photo added. The office can see it on this task.');
    } on ApiException catch (e) {
      if (mounted) _snack(e.displayMessage, error: true);
    } catch (_) {
      if (mounted) _snack('The photo could not be uploaded. Please try again.', error: true);
    } finally {
      if (mounted) setState(() => _uploading = false);
    }
  }

  /// Returns the caption ('' for none), or null if the user cancelled.
  Future<String?> _askCaption() {
    final controller = TextEditingController();
    return showDialog<String>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: const Text('Add a note?'),
        content: TextField(
          controller: controller,
          autofocus: true,
          maxLength: 255,
          textCapitalization: TextCapitalization.sentences,
          decoration: const InputDecoration(hintText: 'e.g. Shuttering done, ready for slab'),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(dialogContext), child: const Text('Cancel')),
          TextButton(onPressed: () => Navigator.pop(dialogContext, ''), child: const Text('Skip')),
          FilledButton(
            style: FilledButton.styleFrom(minimumSize: const Size(88, 40)),
            onPressed: () => Navigator.pop(dialogContext, controller.text),
            child: const Text('Upload'),
          ),
        ],
      ),
    ).whenComplete(controller.dispose);
  }

  Future<void> _delete(TaskPhoto photo) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: const Text('Delete this photo?'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(dialogContext, false), child: const Text('Cancel')),
          TextButton(
            onPressed: () => Navigator.pop(dialogContext, true),
            child: const Text('Delete', style: TextStyle(color: Color(0xFFB91C1C))),
          ),
        ],
      ),
    );
    if (confirmed != true || !mounted) return;

    try {
      await context.read<TaskService>().deletePhoto(photo.id);
      if (mounted) setState(() => _photos = _photos.where((p) => p.id != photo.id).toList());
    } on ApiException catch (e) {
      if (mounted) _snack(e.displayMessage, error: true);
    }
  }

  void _open(TaskPhoto photo) {
    final headers = context.read<TaskService>().imageHeaders;
    Navigator.of(context).push(MaterialPageRoute(
      builder: (_) => _PhotoViewer(
        photo: photo,
        headers: headers,
        onDelete: photo.canDelete
            ? () {
                Navigator.of(context).pop();
                _delete(photo);
              }
            : null,
      ),
    ));
  }

  void _snack(String message, {bool error = false}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), backgroundColor: error ? const Color(0xFFB91C1C) : null),
    );
  }

  @override
  Widget build(BuildContext context) {
    final headers = context.read<TaskService>().imageHeaders;

    return Container(
      decoration: AppTheme.cardDecoration(),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Expanded(
                child: Text(
                  'Site photos',
                  style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppTheme.slate900),
                ),
              ),
              if (_photos.isNotEmpty)
                Text('${_photos.length}', style: const TextStyle(color: AppTheme.slate500)),
            ],
          ),
          const SizedBox(height: 4),
          const Text(
            'Photos you add here show on this task for the office.',
            style: TextStyle(color: AppTheme.slate500, fontSize: 13),
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: _uploading ? null : () => _add(ImageSource.camera),
                  icon: const Icon(Icons.photo_camera_outlined),
                  label: const Text('Take photo'),
                  style: OutlinedButton.styleFrom(minimumSize: const Size.fromHeight(46)),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: _uploading ? null : () => _add(ImageSource.gallery),
                  icon: const Icon(Icons.photo_library_outlined),
                  label: const Text('Gallery'),
                  style: OutlinedButton.styleFrom(minimumSize: const Size.fromHeight(46)),
                ),
              ),
            ],
          ),
          if (_uploading) ...[
            const SizedBox(height: 12),
            const LinearProgressIndicator(),
            const SizedBox(height: 4),
            const Text('Uploading photo…', style: TextStyle(color: AppTheme.slate500, fontSize: 13)),
          ],
          const SizedBox(height: 12),
          if (_loading)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 16),
              child: Center(child: CircularProgressIndicator()),
            )
          else if (_error != null)
            Row(
              children: [
                Expanded(child: Text(_error!, style: const TextStyle(color: Color(0xFFB91C1C)))),
                TextButton(onPressed: _load, child: const Text('Retry')),
              ],
            )
          else if (_photos.isEmpty)
            const Text('No photos yet.', style: TextStyle(color: AppTheme.slate500))
          else
            GridView.builder(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: _photos.length,
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 3,
                mainAxisSpacing: 8,
                crossAxisSpacing: 8,
              ),
              itemBuilder: (_, index) {
                final photo = _photos[index];
                return GestureDetector(
                  onTap: () => _open(photo),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(8),
                    child: Image.network(
                      photo.thumbUrl,
                      headers: headers,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => Container(
                        color: AppTheme.slate100,
                        child: const Icon(Icons.broken_image_outlined, color: AppTheme.slate500),
                      ),
                    ),
                  ),
                );
              },
            ),
        ],
      ),
    );
  }
}

class _PhotoViewer extends StatelessWidget {
  const _PhotoViewer({required this.photo, required this.headers, this.onDelete});

  final TaskPhoto photo;
  final Map<String, String> headers;
  final VoidCallback? onDelete;

  @override
  Widget build(BuildContext context) {
    final when = photo.when;
    final subtitle = [
      if (photo.uploadedBy != null) photo.uploadedBy!,
      if (when != null) DateFormat('d MMM yyyy, h:mm a').format(when),
    ].join(' · ');

    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        backgroundColor: Colors.black,
        title: Text(subtitle.isEmpty ? 'Photo' : subtitle, style: const TextStyle(fontSize: 14)),
        actions: [
          if (onDelete != null)
            IconButton(icon: const Icon(Icons.delete_outline), tooltip: 'Delete', onPressed: onDelete),
        ],
      ),
      body: Column(
        children: [
          Expanded(
            child: InteractiveViewer(
              maxScale: 5,
              child: Center(
                child: Image.network(
                  photo.url,
                  headers: headers,
                  loadingBuilder: (_, child, progress) =>
                      progress == null ? child : const Center(child: CircularProgressIndicator()),
                  errorBuilder: (_, __, ___) =>
                      const Text('The photo could not be loaded.', style: TextStyle(color: Colors.white70)),
                ),
              ),
            ),
          ),
          if (photo.caption != null && photo.caption!.isNotEmpty)
            SafeArea(
              top: false,
              child: Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                color: Colors.black87,
                child: Text(photo.caption!, style: const TextStyle(color: Colors.white)),
              ),
            ),
        ],
      ),
    );
  }
}
