import 'dart:convert';

import 'package:http/http.dart' as http;

import 'package:presence_etudiant_app/config/app_config.dart';
import 'package:presence_etudiant_app/models/qr_session_params.dart';
import 'package:presence_etudiant_app/models/student_session.dart';
import 'package:presence_etudiant_app/services/device_identity.dart';

class PresenceException implements Exception {
  PresenceException(this.message, {this.code});
  final String message;
  final String? code;
}

class PresenceService {
  PresenceService({http.Client? client}) : _client = client ?? http.Client();

  final http.Client _client;

  Future<String> markPresence({
    required StudentSession session,
    required QrSessionParams qr,
  }) async {
    final deviceId = await DeviceIdentity.getOrCreateDeviceId();

    final uri = Uri.parse('${session.serverBaseUrl}/api/mark_presence_mobile.php');
    http.Response res;
    try {
      res = await _client
          .post(
            uri,
            headers: {'Content-Type': 'application/json; charset=UTF-8'},
            body: jsonEncode({
              'seance_id': qr.seanceId,
              'token': qr.token,
              'auth_token': session.authToken,
              'device_id': deviceId,
            }),
          )
          .timeout(AppConfig.httpTimeout);
    } catch (_) {
      throw PresenceException(
        'Réseau indisponible ou délai dépassé. Réessayez.',
      );
    }

    Map<String, dynamic> json;
    try {
      json = jsonDecode(utf8.decode(res.bodyBytes)) as Map<String, dynamic>;
    } catch (_) {
      throw PresenceException('Réponse serveur invalide.');
    }

    final ok = json['success'] == true;
    final message = json['message']?.toString() ?? 'Erreur inconnue.';

    if (ok) {
      return message;
    }

    final code = json['code']?.toString();
    if (res.statusCode == 401) {
      throw PresenceException(message, code: 'auth');
    }
    if (code == 'teacher_only') {
      throw PresenceException(message, code: 'teacher_only');
    }
    if (code == 'device_mismatch' ||
        code == 'device_login_required' ||
        code == 'device_required') {
      throw PresenceException(message, code: code);
    }
    if (res.statusCode == 409 || code == 'already_marked') {
      throw PresenceException(message, code: 'already_marked');
    }
    throw PresenceException(message, code: code);
  }

  void dispose() {
    _client.close();
  }
}
