import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../services/api_client.dart';
import '../services/auth_service.dart';
import '../theme.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailCtrl = TextEditingController();
  final _passwordCtrl = TextEditingController();

  bool _obscure = true;
  bool _submitting = false;
  String? _error;

  @override
  void dispose() {
    _emailCtrl.dispose();
    _passwordCtrl.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    setState(() => _error = null);
    if (!_formKey.currentState!.validate()) return;

    setState(() => _submitting = true);
    try {
      await context.read<AuthService>().login(
            _emailCtrl.text,
            _passwordCtrl.text,
          );
      // On success the AuthGate swaps in the task list automatically.
    } on ApiException catch (e) {
      setState(() => _error = e.displayMessage);
    } catch (_) {
      setState(() => _error = 'Unexpected error. Please try again.');
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 420),
              child: Form(
                key: _formKey,
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    _brand(),
                    const SizedBox(height: 28),
                    Text(
                      'Sign in',
                      style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                            fontWeight: FontWeight.bold,
                            color: AppTheme.slate900,
                          ),
                    ),
                    const SizedBox(height: 4),
                    const Text(
                      'Log your work on assigned tasks.',
                      style: TextStyle(color: AppTheme.slate500),
                    ),
                    const SizedBox(height: 24),
                    if (_error != null) _errorBanner(_error!),
                    TextFormField(
                      controller: _emailCtrl,
                      keyboardType: TextInputType.emailAddress,
                      autocorrect: false,
                      textInputAction: TextInputAction.next,
                      decoration: const InputDecoration(
                        labelText: 'Email',
                        prefixIcon: Icon(Icons.email_outlined),
                      ),
                      validator: (v) => (v == null || v.trim().isEmpty)
                          ? 'Enter your email'
                          : null,
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _passwordCtrl,
                      obscureText: _obscure,
                      textInputAction: TextInputAction.done,
                      onFieldSubmitted: (_) => _submit(),
                      decoration: InputDecoration(
                        labelText: 'Password',
                        prefixIcon: const Icon(Icons.lock_outline),
                        suffixIcon: IconButton(
                          icon: Icon(_obscure
                              ? Icons.visibility_outlined
                              : Icons.visibility_off_outlined),
                          onPressed: () =>
                              setState(() => _obscure = !_obscure),
                        ),
                      ),
                      validator: (v) => (v == null || v.isEmpty)
                          ? 'Enter your password'
                          : null,
                    ),
                    const SizedBox(height: 24),
                    FilledButton(
                      onPressed: _submitting ? null : _submit,
                      child: _submitting
                          ? const SizedBox(
                              height: 22,
                              width: 22,
                              child: CircularProgressIndicator(
                                strokeWidth: 2.5,
                                color: Colors.white,
                              ),
                            )
                          : const Text('Sign in'),
                    ),
                    const SizedBox(height: 20),
                    _serverRow(),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _brand() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        Container(
          height: 40,
          width: 40,
          decoration: BoxDecoration(
            color: AppTheme.indigo,
            borderRadius: BorderRadius.circular(10),
          ),
          alignment: Alignment.center,
          child: const Text(
            'C',
            style: TextStyle(
              color: Colors.white,
              fontWeight: FontWeight.bold,
              fontSize: 22,
            ),
          ),
        ),
        const SizedBox(width: 10),
        const Text(
          'Best CPMS',
          style: TextStyle(
            fontSize: 22,
            fontWeight: FontWeight.bold,
            color: AppTheme.slate900,
          ),
        ),
      ],
    );
  }

  Widget _errorBanner(String message) {
    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: const Color(0xFFFEE2E2),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xFFFCA5A5)),
      ),
      child: Row(
        children: [
          const Icon(Icons.error_outline, color: Color(0xFFB91C1C), size: 20),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              message,
              style: const TextStyle(color: Color(0xFFB91C1C)),
            ),
          ),
        ],
      ),
    );
  }

  Widget _serverRow() {
    final api = context.read<ApiClient>();
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
      decoration: BoxDecoration(
        color: const Color(0xFFEEF2FF),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Row(
        children: [
          const Icon(Icons.dns_outlined, size: 18, color: Color(0xFF4338CA)),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              api.serverHost,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(color: Color(0xFF4338CA), fontSize: 13),
            ),
          ),
          TextButton(
            onPressed: _submitting ? null : _editServer,
            child: const Text('Server address'),
          ),
        ],
      ),
    );
  }

  Future<void> _editServer() async {
    final api = context.read<ApiClient>();
    final controller = TextEditingController(text: api.baseUrl);

    final value = await showDialog<String>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: const Text('Server address'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Paste the address your company gave you, for example '
              'https://your-company.com. On mobile data it must be a public '
              'address, not 192.168.x.x.',
              style: TextStyle(fontSize: 13, color: AppTheme.slate500),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: controller,
              autofocus: true,
              keyboardType: TextInputType.url,
              autocorrect: false,
              decoration: const InputDecoration(labelText: 'Address'),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(dialogContext),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(dialogContext, controller.text),
            child: const Text('Save'),
          ),
        ],
      ),
    );
    controller.dispose();

    if (value == null || !mounted) return;

    await api.saveBaseUrl(value);
    setState(() {
      _error = null;
      _submitting = true;
    });
    final reachable = await api.ping();
    if (!mounted) return;
    setState(() {
      _submitting = false;
      if (!reachable) {
        _error = 'Saved, but ${api.serverHost} is not answering. '
            'Check the address and your internet connection.';
      }
    });
    if (reachable) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Connected to ${api.serverHost}')),
      );
    }
  }
}
