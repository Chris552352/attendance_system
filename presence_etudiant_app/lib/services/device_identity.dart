import 'dart:io';

import 'package:device_info_plus/device_info_plus.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:uuid/uuid.dart';

/// Identifiant d’installation (UUID) + libellé modèle — envoyés à la connexion et au pointage.
class DeviceIdentity {
  DeviceIdentity._();

  static const _kInstallId = 'install_device_uuid';

  static Future<String> getOrCreateDeviceId() async {
    final p = await SharedPreferences.getInstance();
    var id = p.getString(_kInstallId);
    if (id == null || id.isEmpty) {
      id = const Uuid().v4();
      await p.setString(_kInstallId, id);
    }
    return id;
  }

  /// Ex. "samsung SM-A525F" — stocké côté serveur à titre informatif.
  static Future<String> getDeviceModelLabel() async {
    try {
      final plugin = DeviceInfoPlugin();
      if (Platform.isAndroid) {
        final a = await plugin.androidInfo;
        return '${a.manufacturer} ${a.model}'.trim();
      }
      if (Platform.isIOS) {
        final i = await plugin.iosInfo;
        return '${i.model} ${i.name}'.trim();
      }
    } catch (_) {}
    return Platform.operatingSystem;
  }
}
