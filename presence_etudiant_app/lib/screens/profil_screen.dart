import 'package:flutter/material.dart';

import 'package:presence_etudiant_app/l10n/app_localizations.dart';
import 'package:presence_etudiant_app/models/student_profile.dart';
import 'package:presence_etudiant_app/models/student_session.dart';
import 'package:presence_etudiant_app/services/student_profile_service.dart';

class ProfilScreen extends StatefulWidget {
  const ProfilScreen({super.key, required this.session});

  final StudentSession session;

  @override
  State<ProfilScreen> createState() => _ProfilScreenState();
}

class _ProfilScreenState extends State<ProfilScreen> {
  final _svc = StudentProfileService();
  Future<StudentProfile>? _future;

  final _nomCtrl = TextEditingController();
  final _prenomCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();

  bool _saving = false;
  StudentProfile? _loaded;

  @override
  void initState() {
    super.initState();
    _reload();
  }

  @override
  void dispose() {
    _svc.dispose();
    _nomCtrl.dispose();
    _prenomCtrl.dispose();
    _emailCtrl.dispose();
    super.dispose();
  }

  void _reload() {
    setState(() {
      _future = _svc.fetchProfile(widget.session);
    });
  }

  void _fill(StudentProfile p) {
    _loaded = p;
    _nomCtrl.text = p.nom;
    _prenomCtrl.text = p.prenom;
    _emailCtrl.text = p.email;
  }

  Future<void> _save() async {
    final l10n = AppLocalizations.of(context)!;
    setState(() => _saving = true);
    try {
      final msg = await _svc.updateProfile(
        session: widget.session,
        nom: _nomCtrl.text.trim(),
        prenom: _prenomCtrl.text.trim(),
        email: _emailCtrl.text.trim(),
      );
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
      _reload();
    } catch (e) {
      if (!mounted) return;
      final msg = e is ProfileException ? e.message : l10n.errorGeneric;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(msg), backgroundColor: Colors.red.shade800),
      );
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;

    return Scaffold(
      appBar: AppBar(
        title: Text(l10n.profileTitle),
        actions: [
          IconButton(onPressed: _reload, icon: const Icon(Icons.refresh)),
        ],
      ),
      body: FutureBuilder<StudentProfile>(
        future: _future,
        builder: (context, snap) {
          if (snap.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snap.hasError) {
            final err = snap.error;
            final msg = err is ProfileException ? err.message : err.toString();
            return Center(
              child: Padding(
                padding: const EdgeInsets.all(24),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(msg, textAlign: TextAlign.center),
                    const SizedBox(height: 16),
                    FilledButton(onPressed: _reload, child: Text(l10n.retry)),
                  ],
                ),
              ),
            );
          }
          final p = snap.data!;
          if (_loaded == null || _loaded!.id != p.id) {
            _fill(p);
          }
          return ListView(
            padding: const EdgeInsets.all(16),
            children: [
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(p.displayName, style: Theme.of(context).textTheme.titleLarge),
                      const SizedBox(height: 8),
                      Text(l10n.matriculeWithValue(p.matricule)),
                      const SizedBox(height: 4),
                      Text(
                        p.classe != null
                            ? '${l10n.profileClassLabel}: ${p.classe!.displayLine}'
                            : '${l10n.profileClassLabel}: ${l10n.profileClassNotSet}',
                        style: TextStyle(color: Colors.grey.shade800, fontSize: 14),
                      ),
                      const SizedBox(height: 4),
                      Text(l10n.emailWithLabel(p.email)),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),
              TextField(
                controller: _prenomCtrl,
                decoration: InputDecoration(labelText: l10n.firstName, border: const OutlineInputBorder()),
                enabled: !_saving,
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _nomCtrl,
                decoration: InputDecoration(labelText: l10n.lastName, border: const OutlineInputBorder()),
                enabled: !_saving,
              ),
              const SizedBox(height: 12),
              TextField(
                controller: _emailCtrl,
                decoration: InputDecoration(labelText: l10n.emailLabel, border: const OutlineInputBorder()),
                keyboardType: TextInputType.emailAddress,
                enabled: !_saving,
              ),
              const SizedBox(height: 16),
              FilledButton(
                onPressed: _saving ? null : _save,
                child: _saving
                    ? const SizedBox(height: 18, width: 18, child: CircularProgressIndicator(strokeWidth: 2))
                    : Text(l10n.save),
              ),
              const SizedBox(height: 8),
              Text(
                l10n.passwordChangeHint,
                style: TextStyle(color: Colors.grey.shade700, fontSize: 12),
                textAlign: TextAlign.center,
              ),
            ],
          );
        },
      ),
    );
  }
}
