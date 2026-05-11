import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

/// Persistance du choix de langue : `fr`, `en`, ou système (clé absente).
class LocaleStorage {
  LocaleStorage._();

  static const _kCode = 'app_locale_code';

  static Future<Locale?> loadLocale() async {
    final p = await SharedPreferences.getInstance();
    final c = p.getString(_kCode);
    if (c == null || c.isEmpty) return null;
    if (c == 'en') return const Locale('en');
    if (c == 'fr') return const Locale('fr');
    return null;
  }

  /// `null` = suivre la langue du système.
  static Future<void> saveLocale(Locale? locale) async {
    final p = await SharedPreferences.getInstance();
    if (locale == null) {
      await p.remove(_kCode);
      return;
    }
    await p.setString(_kCode, locale.languageCode);
  }
}
