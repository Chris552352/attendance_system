import 'dart:convert';

import 'package:http/http.dart' as http;

import 'package:presence_etudiant_app/config/app_config.dart';
import 'package:presence_etudiant_app/models/student_class_info.dart';
import 'package:presence_etudiant_app/models/student_session.dart';
import 'package:presence_etudiant_app/services/device_identity.dart';

class AuthException implements Exception {
  AuthException(this.message);
  final String message;
}

class AuthService {
  AuthService({http.Client? client}) : _client = client ?? http.Client();

  final http.Client _client;

  /// [baseUrl] sans slash final, ex. `http://192.168.1.10/attendance_system`
  Future<StudentSession> login({
    required String baseUrl,
    required String email,
    required String motDePasse,
  }) async {
    final deviceId = await DeviceIdentity.getOrCreateDeviceId();
    final deviceModel = await DeviceIdentity.getDeviceModelLabel();

    final uri = Uri.parse('$baseUrl/api/login_mobile.php');
    http.Response res;
    try {
      res = await _client
          .post(
            uri,
            headers: {'Content-Type': 'application/json; charset=UTF-8'},
            body: jsonEncode({
              'email': email,
              'mot_de_passe': motDePasse,
              'device_id': deviceId,
              'device_model': deviceModel,
            }),
          )
          .timeout(AppConfig.httpTimeout);
    } catch (e) {
      throw AuthException(
        'Réseau indisponible ou serveur injoignable. Vérifiez l’URL et le Wi‑Fi.',
      );
    }

    Map<String, dynamic> json;
    try {
      json = jsonDecode(utf8.decode(res.bodyBytes)) as Map<String, dynamic>;
    } catch (_) {
      throw AuthException('Réponse serveur invalide (pas du JSON).');
    }

    final ok = json['success'] == true;
    if (!ok) {
      final msg = json['message']?.toString() ?? 'Échec de la connexion.';
      throw AuthException(msg);
    }

    final data = json['data'];
    if (data is! Map<String, dynamic>) {
      throw AuthException('Données de session manquantes.');
    }

    final id = data['etudiant_id'];
    final token = data['auth_token']?.toString();
    final mat = data['matricule']?.toString();
    final emailFromServer = data['email']?.toString() ?? '';
    final prenom = data['prenom']?.toString() ?? '';
    final nom = data['nom']?.toString() ?? '';
    final classe = StudentClassInfo.maybeFromJson(data['classe']);

    if (id is! int && id is! num) {
      throw AuthException('Identifiant étudiant invalide.');
    }
    if (token == null || token.isEmpty) {
      throw AuthException('Jeton d’authentification manquant.');
    }
    if (mat == null || mat.isEmpty) {
      throw AuthException('Matricule manquant dans la réponse.');
    }

    return StudentSession(
      etudiantId: id is int ? id : (id as num).toInt(),
      matricule: mat,
      email: emailFromServer,
      prenom: prenom,
      nom: nom,
      authToken: token,
      serverBaseUrl: baseUrl,
      classe: classe,
    );
  }

  void dispose() {
    _client.close();
  }
}
