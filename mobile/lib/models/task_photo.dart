/// Mirrors App\Http\Resources\TaskPhotoResource on the server.
///
/// The image URLs need the API bearer token (see ApiClient.imageHeaders).
class TaskPhoto {
  final int id;
  final String? caption;
  final String url;
  final String thumbUrl;
  final String? uploadedBy;
  final DateTime? takenAt;
  final DateTime? createdAt;
  final bool canDelete;

  const TaskPhoto({
    required this.id,
    this.caption,
    required this.url,
    required this.thumbUrl,
    this.uploadedBy,
    this.takenAt,
    this.createdAt,
    required this.canDelete,
  });

  /// When the photo was taken, or when it was uploaded if the camera time is unknown.
  DateTime? get when => takenAt ?? createdAt;

  factory TaskPhoto.fromJson(Map<String, dynamic> json) {
    DateTime? date(dynamic v) => v is String && v.isNotEmpty ? DateTime.tryParse(v)?.toLocal() : null;

    return TaskPhoto(
      id: json['id'] as int,
      caption: json['caption'] as String?,
      url: json['url'] as String? ?? '',
      thumbUrl: json['thumb_url'] as String? ?? '',
      uploadedBy: json['uploaded_by'] as String?,
      takenAt: date(json['taken_at']),
      createdAt: date(json['created_at']),
      canDelete: json['can_delete'] as bool? ?? false,
    );
  }
}
