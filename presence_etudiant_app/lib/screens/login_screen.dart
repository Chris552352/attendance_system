import 'package:flutter/material.dart';

import 'package:presence_etudiant_app/l10n/app_localizations.dart';
import 'package:presence_etudiant_app/models/student_session.dart';
import 'package:presence_etudiant_app/screens/configure_server_screen.dart';
import 'package:presence_etudiant_app/services/auth_service.dart';
import 'package:presence_etudiant_app/services/session_storage.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key, required this.onSuccess});

  final void Function(StudentSession session) onSuccess;

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _emailCtrl = TextEditingController();
  final _passwordCtrl = TextEditingController();
  final _auth = AuthService();

  bool _loading = false;
  String? _serverBaseUrl;

  @override
  void dispose() {
    _emailCtrl.dispose();
    _passwordCtrl.dispose();
    _auth.dispose();
    super.dispose();
  }

  @override
  void initState() {
    super.initState();
    _restoreServer();
  }

  Future<void> _restoreServer() async {
    final base = await SessionStorage.loadServerBaseUrl();
    if (!mounted) return;
    setState(() => _serverBaseUrl = base);
  }

  Future<void> _configureServer() async {
    final base = await Navigator.of(context).push<String?>(
      MaterialPageRoute(builder: (_) => const ConfigureServerScreen()),
    );
    if (!mounted) return;
    if (base != null && base.trim().isNotEmpty) {
      setState(() => _serverBaseUrl = base.trim());
    }
  }

  Future<void> _submit() async {
    final l10n = AppLocalizations.of(context)!;
    if (!_formKey.currentState!.validate()) return;
    if (_serverBaseUrl == null || _serverBaseUrl!.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(l10n.serverNotConfiguredSnackbar),
          backgroundColor: Colors.orange.shade900,
        ),
      );
      return;
    }

    setState(() => _loading = true);
    FocusScope.of(context).unfocus();

    final base = _serverBaseUrl!.trim();
    final email = _emailCtrl.text.trim();
    final password = _passwordCtrl.text;

    try {
      final session = await _auth.login(
        baseUrl: base,
        email: email,
        motDePasse: password,
      );
      await SessionStorage.save(session);
      if (!mounted) return;
      widget.onSuccess(session);
    } on AuthException catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.message), backgroundColor: Colors.red.shade800),
      );
    } catch (_) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(l10n.unexpectedError),
          backgroundColor: Colors.red,
        ),
      );
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;

    return Scaffold(
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  Icon(Icons.school, size: 72, color: Theme.of(context).colorScheme.primary),
                  const SizedBox(height: 16),
                  Text(
                    l10n.studentLoginTitle,
                    textAlign: TextAlign.center,
                    style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                          fontWeight: FontWeight.bold,
                        ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    l10n.sameWifiHint,
                    textAlign: TextAlign.center,
                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                          color: Colors.grey.shade700,
                        ),
                  ),
                  const SizedBox(height: 32),
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(14),
                      child: Row(
                        children: [
                          const Icon(Icons.dns),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  l10n.serverLabel,
                                  style: Theme.of(context).textTheme.labelLarge?.copyWith(
                                        fontWeight: FontWeight.w600,
                                      ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  _serverBaseUrl ?? l10n.notConfigured,
                                  style: TextStyle(color: Colors.grey.shade800, fontSize: 13),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(width: 8),
                          TextButton(
                            onPressed: _loading ? null : _configureServer,
                            child: Text(_serverBaseUrl == null ? l10n.configureServer : l10n.changeServer),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: _emailCtrl,
                    decoration: InputDecoration(
                      labelText: l10n.emailLabel,
                      border: const OutlineInputBorder(),
                      prefixIcon: const Icon(Icons.alternate_email),
                    ),
                    textInputAction: TextInputAction.next,
                    validator: (v) {
                      final s = v?.trim() ?? '';
                      if (s.isEmpty) return l10n.emailRequired;
                      if (!s.contains('@')) return l10n.emailInvalid;
                      return null;
                    },
                    enabled: !_loading,
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: _passwordCtrl,
                    decoration: InputDecoration(
                      labelText: l10n.passwordLabel,
                      border: const OutlineInputBorder(),
                      prefixIcon: const Icon(Icons.lock_outline),
                    ),
                    obscureText: true,
                    validator: (v) {
                      if (v == null || v.isEmpty) return l10n.passwordRequired;
                      return null;
                    },
                    enabled: !_loading,
                    onFieldSubmitted: (_) => _submit(),
                  ),
                  const SizedBox(height: 28),
                  FilledButton(
                    onPressed: _loading ? null : _submit,
                    style: FilledButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 16)),
                    child: _loading
                        ? const SizedBox(
                            height: 22,
                            width: 22,
                            child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                          )
                        : Text(l10n.actionConnect),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
