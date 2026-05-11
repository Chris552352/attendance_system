import 'package:flutter/material.dart';

import 'package:presence_etudiant_app/l10n/app_localizations.dart';
import 'package:presence_etudiant_app/models/student_session.dart';
import 'package:presence_etudiant_app/screens/mes_cours_screen.dart';
import 'package:presence_etudiant_app/screens/mes_presences_screen.dart';
import 'package:presence_etudiant_app/screens/profil_screen.dart';
import 'package:presence_etudiant_app/screens/scan_screen.dart';

class HomeScreen extends StatelessWidget {
  const HomeScreen({
    super.key,
    required this.session,
    required this.onLogout,
    required this.onLocaleChanged,
  });

  final StudentSession session;
  final VoidCallback onLogout;
  final void Function(Locale? locale) onLocaleChanged;

  Future<void> _openScan(BuildContext context) async {
    final result = await Navigator.of(context).push<(bool, String)?>(
      MaterialPageRoute(builder: (_) => ScanScreen(session: session)),
    );
    if (!context.mounted || result == null) return;
    final (ok, message) = result;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: ok ? Colors.green.shade800 : Colors.red.shade800,
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;

    return Scaffold(
      appBar: AppBar(
        title: Text(l10n.navPresence),
        actions: [
          PopupMenuButton<String>(
            icon: const Icon(Icons.language_outlined),
            tooltip: l10n.languageTitle,
            onSelected: (value) {
              switch (value) {
                case 'fr':
                  onLocaleChanged(const Locale('fr'));
                  break;
                case 'en':
                  onLocaleChanged(const Locale('en'));
                  break;
                default:
                  onLocaleChanged(null);
              }
            },
            itemBuilder: (ctx) => [
              PopupMenuItem(value: 'fr', child: Text(l10n.languageFrench)),
              PopupMenuItem(value: 'en', child: Text(l10n.languageEnglish)),
              PopupMenuItem(value: 'system', child: Text(l10n.languageSystem)),
            ],
          ),
          TextButton(
            onPressed: onLogout,
            child: Text(l10n.actionLogout, style: const TextStyle(color: Colors.white)),
          ),
        ],
      ),
      body: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Card(
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      session.displayName,
                      style: Theme.of(context).textTheme.titleLarge?.copyWith(
                            fontWeight: FontWeight.bold,
                          ),
                    ),
                    const SizedBox(height: 8),
                    Text(l10n.matriculeWithValue(session.matricule)),
                    const SizedBox(height: 4),
                    Text(
                      session.classe != null
                          ? '${l10n.profileClassLabel}: ${session.classe!.displayLine}'
                          : '${l10n.profileClassLabel}: ${l10n.profileClassNotSet}',
                      style: TextStyle(color: Colors.grey.shade800, fontSize: 14),
                    ),
                    if (session.email.isNotEmpty) ...[
                      const SizedBox(height: 4),
                      Text(
                        l10n.emailWithLabel(session.email),
                        style: TextStyle(color: Colors.grey.shade800, fontSize: 14),
                      ),
                    ],
                    const SizedBox(height: 4),
                    Text(
                      session.serverBaseUrl,
                      style: TextStyle(color: Colors.grey.shade700, fontSize: 13),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),
            FilledButton.icon(
              onPressed: () => _openScan(context),
              icon: const Icon(Icons.qr_code_scanner),
              label: Text(l10n.scanQrButton),
              style: FilledButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 18)),
            ),
            const SizedBox(height: 8),
            Text(
              l10n.afterScanBiometricHint,
              style: TextStyle(fontSize: 13, color: Colors.grey.shade800, height: 1.35),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 12),
            OutlinedButton.icon(
              onPressed: () {
                Navigator.of(context).push(
                  MaterialPageRoute<void>(builder: (_) => MesPresencesScreen(session: session)),
                );
              },
              icon: const Icon(Icons.event_note_outlined),
              label: Text(l10n.myPresencesJustifications),
              style: OutlinedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 16)),
            ),
            const SizedBox(height: 12),
            OutlinedButton.icon(
              onPressed: () {
                Navigator.of(context).push(
                  MaterialPageRoute<void>(builder: (_) => ProfilScreen(session: session)),
                );
              },
              icon: const Icon(Icons.person_outline),
              label: Text(l10n.myProfile),
              style: OutlinedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 16)),
            ),
            const SizedBox(height: 12),
            OutlinedButton.icon(
              onPressed: () {
                Navigator.of(context).push(
                  MaterialPageRoute<void>(builder: (_) => MesCoursScreen(session: session)),
                );
              },
              icon: const Icon(Icons.menu_book_outlined),
              label: Text(l10n.myCourses),
              style: OutlinedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 16)),
            ),
            const SizedBox(height: 16),
            OutlinedButton.icon(
              onPressed: onLogout,
              icon: const Icon(Icons.logout),
              label: Text(l10n.disconnect),
            ),
          ],
        ),
      ),
    );
  }
}
