import 'dart:io';

import 'package:flutter/material.dart';

import 'package:file_picker/file_picker.dart';
import 'package:presence_etudiant_app/l10n/app_localizations.dart';
import 'package:presence_etudiant_app/models/presence_row.dart';
import 'package:presence_etudiant_app/models/student_session.dart';
import 'package:presence_etudiant_app/screens/justification_chat_screen.dart';
import 'package:presence_etudiant_app/services/student_portal_service.dart';

class MesPresencesScreen extends StatefulWidget {
  const MesPresencesScreen({super.key, required this.session});

  final StudentSession session;

  @override
  State<MesPresencesScreen> createState() => _MesPresencesScreenState();
}

class _MesPresencesScreenState extends State<MesPresencesScreen> {
  final _portal = StudentPortalService();
  Future<List<PresenceRow>>? _future;

  @override
  void initState() {
    super.initState();
    _reload();
  }

  @override
  void dispose() {
    _portal.dispose();
    super.dispose();
  }

  void _reload() {
    setState(() {
      _future = _portal.fetchPresences(widget.session);
    });
  }

  String _statutLabel(PresenceRow p, AppLocalizations l10n) {
    if (p.hasPendingJustification) return l10n.justificationPending;
    if (p.justifStatut == 'validee') return l10n.justificationAccepted;
    if (p.justifStatut == 'rejetee') return l10n.justificationRejected;
    if (p.justifie) return l10n.justified;
    final s = p.statut.toLowerCase();
    if (s == 'present') return l10n.present;
    if (s == 'absent') return l10n.absent;
    return p.statut;
  }

  Color _statutColor(PresenceRow p) {
    if (p.hasPendingJustification) return Colors.orange.shade800;
    if (p.justifStatut == 'validee' || p.justifie) return Colors.green.shade800;
    if (p.justifStatut == 'rejetee') return Colors.red.shade800;
    if (p.statut.toLowerCase() == 'present') return Colors.green.shade700;
    return Colors.grey.shade800;
  }

  Future<void> _openJustifyDialog(PresenceRow row) async {
    final l10n = AppLocalizations.of(context)!;
    final ctrl = TextEditingController();
    File? pieceJointe;
    String? pieceLabel;
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setStateDialog) => AlertDialog(
          title: Text(l10n.justifyAbsenceTitle),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Text(row.coursLabel, style: const TextStyle(fontWeight: FontWeight.w600)),
                const SizedBox(height: 8),
                Text(l10n.dateLine(row.datePresence), style: TextStyle(color: Colors.grey.shade700, fontSize: 13)),
                const SizedBox(height: 16),
                TextField(
                  controller: ctrl,
                  maxLines: 5,
                  decoration: InputDecoration(
                    labelText: l10n.motifLabel,
                    border: const OutlineInputBorder(),
                    alignLabelWithHint: true,
                  ),
                ),
                const SizedBox(height: 12),
                OutlinedButton.icon(
                  onPressed: () async {
                    final res = await FilePicker.platform.pickFiles(
                      type: FileType.custom,
                      allowedExtensions: const ['pdf', 'jpg', 'jpeg', 'png'],
                      withData: false,
                    );
                    final f = (res?.files.isNotEmpty ?? false) ? res!.files.first : null;
                    if (f == null) return;
                    if (f.size > 5 * 1024 * 1024) {
                      if (!ctx.mounted) return;
                      ScaffoldMessenger.of(ctx).showSnackBar(
                        SnackBar(content: Text(l10n.fileTooLarge), backgroundColor: Colors.red.shade800),
                      );
                      return;
                    }
                    if (f.path == null) {
                      if (!ctx.mounted) return;
                      ScaffoldMessenger.of(ctx).showSnackBar(
                        SnackBar(content: Text(l10n.cannotReadFile), backgroundColor: Colors.red.shade800),
                      );
                      return;
                    }
                    setStateDialog(() {
                      pieceJointe = File(f.path!);
                      pieceLabel = f.name;
                    });
                  },
                  icon: const Icon(Icons.attach_file),
                  label: Text(
                    pieceLabel == null ? l10n.attachFileButton : l10n.attachmentNamed(pieceLabel!),
                  ),
                ),
                if (pieceJointe != null) ...[
                  const SizedBox(height: 6),
                  TextButton.icon(
                    onPressed: () => setStateDialog(() {
                      pieceJointe = null;
                      pieceLabel = null;
                    }),
                    icon: const Icon(Icons.close),
                    label: Text(l10n.removeAttachment),
                  ),
                ],
              ],
            ),
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(ctx, false), child: Text(l10n.cancel)),
            FilledButton(onPressed: () => Navigator.pop(ctx, true), child: Text(l10n.send)),
          ],
        ),
      ),
    );
    if (ok != true || !mounted) return;
    final texte = ctrl.text.trim();
    if (texte.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(l10n.enterReason), backgroundColor: Colors.red.shade800),
      );
      return;
    }
    try {
      final msg = await _portal.submitJustification(
        session: widget.session,
        presenceId: row.presenceId,
        contenu: texte,
        pieceJointe: pieceJointe,
      );
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(msg)));
      _reload();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(e is PortalException ? e.message : l10n.errorGeneric),
          backgroundColor: Colors.red.shade800,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;

    return Scaffold(
      appBar: AppBar(
        title: Text(l10n.mesPresencesTitle),
        actions: [
          IconButton(onPressed: _reload, icon: const Icon(Icons.refresh), tooltip: l10n.refreshTooltip),
        ],
      ),
      body: FutureBuilder<List<PresenceRow>>(
        future: _future,
        builder: (context, snap) {
          if (snap.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snap.hasError) {
            final err = snap.error;
            final msg = err is PortalException ? err.message : err.toString();
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
          final list = snap.data ?? [];
          if (list.isEmpty) {
            return Center(
              child: Text(
                l10n.noPresencesYet,
                style: TextStyle(color: Colors.grey.shade700),
                textAlign: TextAlign.center,
              ),
            );
          }
          return RefreshIndicator(
            onRefresh: () async {
              final f = _portal.fetchPresences(widget.session);
              if (mounted) setState(() => _future = f);
              await f;
            },
            child: ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: list.length,
              separatorBuilder: (_, __) => const SizedBox(height: 8),
              itemBuilder: (context, i) {
                final p = list[i];
                return Card(
                  child: Padding(
                    padding: const EdgeInsets.all(12),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Expanded(
                              child: Text(
                                p.coursLabel,
                                style: const TextStyle(fontWeight: FontWeight.w600),
                              ),
                            ),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                              decoration: BoxDecoration(
                                color: _statutColor(p).withValues(alpha: 0.12),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Text(
                                _statutLabel(p, l10n),
                                style: TextStyle(fontSize: 12, color: _statutColor(p), fontWeight: FontWeight.w600),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 6),
                        Text(l10n.dateLine(p.datePresence), style: TextStyle(color: Colors.grey.shade700, fontSize: 13)),
                        if (p.justifContenu != null && p.justifContenu!.isNotEmpty) ...[
                          const SizedBox(height: 8),
                          Text(l10n.yourMessage(p.justifContenu!), style: const TextStyle(fontSize: 13)),
                        ],
                        if (p.justifCommentaire != null && p.justifCommentaire!.trim().isNotEmpty) ...[
                          const SizedBox(height: 4),
                          Text(
                            l10n.staffReply(p.justifCommentaire!.trim()),
                            style: TextStyle(fontSize: 13, color: Colors.grey.shade800),
                          ),
                        ],
                        if (p.canSubmitJustification) ...[
                          const SizedBox(height: 12),
                          Align(
                            alignment: Alignment.centerRight,
                            child: TextButton.icon(
                              onPressed: () => _openJustifyDialog(p),
                              icon: const Icon(Icons.edit_note),
                              label: Text(l10n.sendJustification),
                            ),
                          ),
                        ],
                        if (!p.canSubmitJustification && (p.hasPendingJustification || (p.justifStatut != null))) ...[
                          const SizedBox(height: 8),
                          Align(
                            alignment: Alignment.centerRight,
                            child: TextButton.icon(
                              onPressed: () {
                                Navigator.of(context).push(
                                  MaterialPageRoute<void>(
                                    builder: (_) => JustificationChatScreen(session: widget.session, row: p),
                                  ),
                                );
                              },
                              icon: const Icon(Icons.forum_outlined),
                              label: Text(l10n.messaging),
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),
                );
              },
            ),
          );
        },
      ),
    );
  }
}
