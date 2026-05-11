import 'package:flutter/material.dart';
import 'package:presence_etudiant_app/l10n/app_localizations.dart';

import 'package:presence_etudiant_app/models/student_session.dart';
import 'package:presence_etudiant_app/screens/home_screen.dart';
import 'package:presence_etudiant_app/screens/login_screen.dart';
import 'package:presence_etudiant_app/services/locale_storage.dart';
import 'package:presence_etudiant_app/services/session_storage.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(const PresenceEtudiantApp());
}

class PresenceEtudiantApp extends StatefulWidget {
  const PresenceEtudiantApp({super.key});

  @override
  State<PresenceEtudiantApp> createState() => _PresenceEtudiantAppState();
}

class _PresenceEtudiantAppState extends State<PresenceEtudiantApp> {
  Locale? _locale;
  bool _localeReady = false;

  @override
  void initState() {
    super.initState();
    _loadLocale();
  }

  Future<void> _loadLocale() async {
    final l = await LocaleStorage.loadLocale();
    if (!mounted) return;
    setState(() {
      _locale = l;
      _localeReady = true;
    });
  }

  void setLocale(Locale? locale) {
    setState(() => _locale = locale);
    LocaleStorage.saveLocale(locale);
  }

  @override
  Widget build(BuildContext context) {
    if (!_localeReady) {
      return const MaterialApp(
        home: Scaffold(body: Center(child: CircularProgressIndicator())),
        debugShowCheckedModeBanner: false,
      );
    }

    return MaterialApp(
      onGenerateTitle: (ctx) => AppLocalizations.of(ctx)!.appTitle,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: const Color(0xFF1565C0)),
        useMaterial3: true,
      ),
      locale: _locale,
      localizationsDelegates: AppLocalizations.localizationsDelegates,
      supportedLocales: AppLocalizations.supportedLocales,
      localeListResolutionCallback: (deviceLocales, supported) {
        if (_locale != null) {
          return _locale;
        }
        for (final device in deviceLocales ?? const <Locale>[]) {
          for (final s in supported) {
            if (s.languageCode == device.languageCode) {
              return s;
            }
          }
        }
        return supported.first;
      },
      home: AuthGate(
        onLocaleChanged: setLocale,
      ),
      debugShowCheckedModeBanner: false,
    );
  }
}

class AuthGate extends StatefulWidget {
  const AuthGate({super.key, required this.onLocaleChanged});

  final void Function(Locale? locale) onLocaleChanged;

  @override
  State<AuthGate> createState() => _AuthGateState();
}

class _AuthGateState extends State<AuthGate> {
  bool _ready = false;
  StudentSession? _session;

  @override
  void initState() {
    super.initState();
    _restore();
  }

  Future<void> _restore() async {
    final s = await SessionStorage.load();
    if (!mounted) return;
    setState(() {
      _session = s;
      _ready = true;
    });
  }

  void _onLoggedIn(StudentSession s) {
    setState(() => _session = s);
  }

  Future<void> _onLogout() async {
    await SessionStorage.clear();
    if (!mounted) return;
    setState(() => _session = null);
  }

  @override
  Widget build(BuildContext context) {
    if (!_ready) {
      return const Scaffold(
        body: Center(child: CircularProgressIndicator()),
      );
    }
    if (_session == null) {
      return LoginScreen(onSuccess: _onLoggedIn);
    }
    return HomeScreen(
      session: _session!,
      onLogout: _onLogout,
      onLocaleChanged: widget.onLocaleChanged,
    );
  }
}
