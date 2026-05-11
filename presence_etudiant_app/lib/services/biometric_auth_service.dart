import 'package:flutter/services.dart';
import 'package:local_auth/local_auth.dart';

/// Résultat de la demande biométrique avant le pointage.
enum BiometricPresenceResult {
  /// L’utilisateur s’est authentifié (empreinte, visage ou code appareil selon les réglages).
  confirmed,

  /// Annulation ou échec utilisateur.
  userCancelled,

  /// Aucun capteur / biométrie utilisable : le pointage peut continuer (ex. émulateur).
  hardwareUnavailable,
}

/// Authentification locale : empreinte, reconnaissance faciale, ou code de l’appareil en secours.
class BiometricAuthService {
  BiometricAuthService._();

  static final LocalAuthentication _auth = LocalAuthentication();

  /// Demande une confirmation avant d’enregistrer la présence.
  ///
  /// [localizedReason] : texte du dialogue système (fourni par l’app en fonction de la langue).
  static Future<BiometricPresenceResult> confirmBeforePresence({
    required String localizedReason,
  }) async {
    try {
      final supported = await _auth.isDeviceSupported();
      if (!supported) {
        return BiometricPresenceResult.hardwareUnavailable;
      }

      final authed = await _auth.authenticate(
        localizedReason: localizedReason,
        options: const AuthenticationOptions(
          stickyAuth: true,
          // false : sur Android, permet aussi le code PIN / schéma si la biométrie échoue.
          biometricOnly: false,
        ),
      );

      if (authed) {
        return BiometricPresenceResult.confirmed;
      }
      return BiometricPresenceResult.userCancelled;
    } on PlatformException catch (e) {
      final code = e.code;
      if (code == 'NotAvailable' || code == 'NoBiometricHardware') {
        return BiometricPresenceResult.hardwareUnavailable;
      }
      if (code == 'UserCancelled' || code == 'Canceled' || code == 'SystemCancel') {
        return BiometricPresenceResult.userCancelled;
      }
      // Pas d’empreinte/visage enregistré, ou pas de code : on laisse passer le pointage (ex. émulateur).
      if (code == 'NotEnrolled' || code == 'PasscodeNotSet') {
        return BiometricPresenceResult.hardwareUnavailable;
      }
      return BiometricPresenceResult.userCancelled;
    } catch (_) {
      return BiometricPresenceResult.hardwareUnavailable;
    }
  }
}
