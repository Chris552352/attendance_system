import 'package:flutter/material.dart';

import 'package:presence_etudiant_app/models/presence_row.dart';
import 'package:presence_etudiant_app/models/student_session.dart';
import 'package:presence_etudiant_app/services/student_portal_service.dart';

class JustificationChatScreen extends StatefulWidget {
  const JustificationChatScreen({super.key, required this.session, required this.row});

  final StudentSession session;
  final PresenceRow row;

  @override
  State<JustificationChatScreen> createState() => _JustificationChatScreenState();
}

class _JustificationChatScreenState extends State<JustificationChatScreen> {
  final _portal = StudentPortalService();
  final _ctrl = TextEditingController();
  bool _loading = true;
  bool _sending = false;
  List<Map<String, dynamic>> _messages = const [];
  Map<String, dynamic>? _justification;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _portal.dispose();
    _ctrl.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final data = await _portal.fetchJustificationThread(
        session: widget.session,
        presenceId: widget.row.presenceId,
      );
      final msgs = data['messages'];
      setState(() {
        _justification = data['justification'] is Map<String, dynamic> ? data['justification'] as Map<String, dynamic> : null;
        _messages = msgs is List ? msgs.whereType<Map<String, dynamic>>().toList() : const [];
        _loading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() => _loading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(e is PortalException ? e.message : 'Erreur.'),
          backgroundColor: Colors.red.shade800,
        ),
      );
    }
  }

  Future<void> _send() async {
    final text = _ctrl.text.trim();
    if (text.isEmpty || _sending) return;
    setState(() => _sending = true);
    try {
      final data = await _portal.fetchJustificationThread(
        session: widget.session,
        presenceId: widget.row.presenceId,
        sendMessage: text,
      );
      final msgs = data['messages'];
      _ctrl.clear();
      setState(() {
        _justification = data['justification'] is Map<String, dynamic> ? data['justification'] as Map<String, dynamic> : null;
        _messages = msgs is List ? msgs.whereType<Map<String, dynamic>>().toList() : const [];
      });
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(e is PortalException ? e.message : 'Erreur.'),
          backgroundColor: Colors.red.shade800,
        ),
      );
    } finally {
      if (mounted) setState(() => _sending = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final title = widget.row.coursLabel;
    return Scaffold(
      appBar: AppBar(
        title: Text('Justification • $title', overflow: TextOverflow.ellipsis),
        actions: [
          IconButton(onPressed: _load, icon: const Icon(Icons.refresh), tooltip: 'Actualiser'),
        ],
      ),
      body: Column(
        children: [
          if (_justification != null)
            Padding(
              padding: const EdgeInsets.fromLTRB(12, 12, 12, 0),
              child: Row(
                children: [
                  const Icon(Icons.forum_outlined, size: 18),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Statut: ${_justification!['statut'] ?? '—'}',
                      style: TextStyle(color: Colors.grey.shade800),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
            ),
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator())
                : ListView.builder(
                    padding: const EdgeInsets.all(12),
                    itemCount: _messages.length,
                    itemBuilder: (context, i) {
                      final m = _messages[i];
                      final sender = (m['sender_type']?.toString() ?? 'etudiant');
                      final isMe = sender == 'etudiant';
                      final text = m['message']?.toString() ?? '';
                      final dt = m['created_at']?.toString();
                      final bubbleColor = isMe ? Theme.of(context).colorScheme.primary : Colors.grey.shade300;
                      final textColor = isMe ? Colors.white : Colors.black87;
                      return Align(
                        alignment: isMe ? Alignment.centerRight : Alignment.centerLeft,
                        child: Container(
                          constraints: const BoxConstraints(maxWidth: 520),
                          margin: const EdgeInsets.symmetric(vertical: 4),
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                          decoration: BoxDecoration(
                            color: bubbleColor,
                            borderRadius: BorderRadius.circular(14),
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(text, style: TextStyle(color: textColor)),
                              if (dt != null && dt.isNotEmpty) ...[
                                const SizedBox(height: 6),
                                Text(dt, style: TextStyle(color: textColor.withValues(alpha: 0.75), fontSize: 11)),
                              ],
                            ],
                          ),
                        ),
                      );
                    },
                  ),
          ),
          SafeArea(
            top: false,
            child: Padding(
              padding: const EdgeInsets.fromLTRB(12, 8, 12, 12),
              child: Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: _ctrl,
                      textInputAction: TextInputAction.send,
                      onSubmitted: (_) => _send(),
                      decoration: const InputDecoration(
                        hintText: 'Écrire un message…',
                        border: OutlineInputBorder(),
                      ),
                      enabled: !_sending,
                      maxLines: 3,
                      minLines: 1,
                    ),
                  ),
                  const SizedBox(width: 8),
                  IconButton.filled(
                    onPressed: _sending ? null : _send,
                    icon: _sending ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2)) : const Icon(Icons.send),
                    tooltip: 'Envoyer',
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

