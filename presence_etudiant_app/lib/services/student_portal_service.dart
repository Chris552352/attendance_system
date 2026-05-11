import 'dart:convert';
import 'dart:io';

import 'package:http/http.dart' as http;

import 'package:presence_etudiant_app/config/app_config.dart';
import 'package:presence_etudiant_app/models/presence_row.dart';
import 'package:presence_etudiant_app/models/student_session.dart';

class PortalException implements Exception {
  PortalException(this.message);
  final String message;
}

class StudentPortalService {
  StudentPortalService({http.Client? client}) : _client = client ?? http.Client();

  final http.Client _client;

  Future<List<PresenceRow>> fetchPresences(StudentSession session) async {
    final uri = Uri.parse('${session.serverBaseUrl}/api/student_presences.php');
    http.Response res;
    try {
      res = await _client
          .post(
            uri,
            headers: {'Content-Type': 'application/json; charset=UTF-8'},
            body: jsonEncode({'auth_token': session.authToken}),
          )
          .timeout(AppConfig.httpTimeout);
    } catch (_) {
      throw PortalException('Réseau indisponible ou délai dépassé.');
    }

    Map<String, dynamic> json;
    try {
      json = jsonDecode(utf8.decode(res.bodyBytes)) as Map<String, dynamic>;
    } catch (_) {
      throw PortalException('Réponse serveur invalide.');
    }

    if (json['success'] != true) {
      final msg = json['message']?.toString() ?? 'Erreur.';
      if (res.statusCode == 401) {
        throw PortalException('$msg Reconnectez-vous.');
      }
      throw PortalException(msg);
    }

    final data = json['data'];
    if (data is! Map<String, dynamic>) {
      throw PortalException('Données manquantes.');
    }
    final list = data['presences'];
    if (list is! List) {
      return [];
    }
    return list
        .whereType<Map<String, dynamic>>()
        .map(PresenceRow.fromJson)
        .toList();
  }

  Future<String> submitJustification({
    required StudentSession session,
    required int presenceId,
    required String contenu,
    File? pieceJointe,
  }) async {
    final uri = Uri.parse('${session.serverBaseUrl}/api/student_justify_absence.php');
    http.Response? resJson;
    http.StreamedResponse? resMultipart;
    try {
      if (pieceJointe == null) {
        resJson = await _client
            .post(
              uri,
              headers: {'Content-Type': 'application/json; charset=UTF-8'},
              body: jsonEncode({
                'auth_token': session.authToken,
                'presence_id': presenceId,
                'contenu': contenu,
              }),
            )
            .timeout(AppConfig.httpTimeout);
      } else {
        final len = await pieceJointe.length();
        if (len > 5 * 1024 * 1024) {
          throw PortalException('Pièce jointe trop volumineuse (max 5 Mo).');
        }
        final req = http.MultipartRequest('POST', uri)
          ..fields['auth_token'] = session.authToken
          ..fields['presence_id'] = presenceId.toString()
          ..fields['contenu'] = contenu;
        req.files.add(await http.MultipartFile.fromPath('piece', pieceJointe.path));
        resMultipart = await _client.send(req).timeout(AppConfig.httpTimeout);
      }
    } catch (e) {
      if (e is PortalException) rethrow;
      throw PortalException('Réseau indisponible ou délai dépassé.');
    }

    Map<String, dynamic> json;
    try {
      if (resJson != null) {
        json = jsonDecode(utf8.decode(resJson.bodyBytes)) as Map<String, dynamic>;
      } else if (resMultipart != null) {
        final bytes = await resMultipart.stream.toBytes();
        json = jsonDecode(utf8.decode(bytes)) as Map<String, dynamic>;
      } else {
        throw PortalException('Réponse serveur invalide.');
      }
    } catch (_) {
      throw PortalException('Réponse serveur invalide.');
    }

    final ok = json['success'] == true;
    final message = json['message']?.toString() ?? 'Erreur.';
    if (ok) return message;
    throw PortalException(message);
  }

  Future<Map<String, dynamic>> fetchJustificationThread({
    required StudentSession session,
    required int presenceId,
    String? sendMessage,
  }) async {
    final uri = Uri.parse('${session.serverBaseUrl}/api/justification_messages.php');
    http.Response res;
    try {
      res = await _client
          .post(
            uri,
            headers: {'Content-Type': 'application/json; charset=UTF-8'},
            body: jsonEncode({
              'auth_token': session.authToken,
              'presence_id': presenceId,
              if (sendMessage != null && sendMessage.trim().isNotEmpty) 'message': sendMessage.trim(),
            }),
          )
          .timeout(AppConfig.httpTimeout);
    } catch (_) {
      throw PortalException('Réseau indisponible ou délai dépassé.');
    }

    Map<String, dynamic> json;
    try {
      json = jsonDecode(utf8.decode(res.bodyBytes)) as Map<String, dynamic>;
    } catch (_) {
      throw PortalException('Réponse serveur invalide.');
    }

    if (json['success'] != true) {
      final msg = json['message']?.toString() ?? 'Erreur.';
      throw PortalException(msg);
    }
    final data = json['data'];
    if (data is! Map<String, dynamic>) {
      throw PortalException('Données manquantes.');
    }
    return data;
  }

  void dispose() {
    _client.close();
  }
}
