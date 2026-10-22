/// Mirrors App\Http\Resources\UserResource on the server.
///
/// JSON shape:
/// {
///   "id": 5, "name": "...", "email": "...", "phone": "...",
///   "role": "worker", "status": "active",
///   "company": { "id": 1, "name": "ABC Construction" }
/// }
class User {
  final int id;
  final String name;
  final String email;
  final String? phone;
  final String role;
  final String status;
  final int? companyId;
  final String? companyName;

  const User({
    required this.id,
    required this.name,
    required this.email,
    this.phone,
    required this.role,
    required this.status,
    this.companyId,
    this.companyName,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    final company = json['company'] as Map<String, dynamic>?;
    return User(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      email: json['email'] as String? ?? '',
      phone: json['phone'] as String?,
      role: json['role'] as String? ?? '',
      status: json['status'] as String? ?? '',
      companyId: company?['id'] as int?,
      companyName: company?['name'] as String?,
    );
  }
}
