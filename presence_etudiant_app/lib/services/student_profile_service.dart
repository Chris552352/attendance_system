import 'dart:convert';

import 'package:http/http.dart' as http;

import 'package:presence_etudiant_app/config/app_config.dart';
import 'package:presence_etudiant_app/models/course_row.dart';
import 'package:presence_etudiant_app/models/student_profile.dart';
import 'package:presence_etudiant_app/models/student_session.dart';

class ProfileException implements Exception {
  ProfileException(this.message);
  final String message;
}

class StudentProfileService {
  StudentProfileService({http.Client? client}) : _client = client ?? http.Client();

  final http.Client _client;

  Future<StudentProfile> fetchProfile(StudentSession session) async {
    final uri = Uri.parse('${session.serverBaseUrl}/api/student_profile.php');
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
      throw ProfileException('Réseau indisponible ou délai dépassé.');
    }

    final json = _decode(res);
    if (json['success'] != true) {
      throw ProfileException(json['message']?.toString() ?? 'Erreur.');
    }
    final data = json['data'];
    if (data is! Map<String, dynamic>) throw ProfileException('Données manquantes.');
    final e = data['etudiant'];
    if (e is! Map<String, dynamic>) throw ProfileException('Profil manquant.');
    return StudentProfile.fromJson(e);
  }

  Future<String> updateProfile({
    required StudentSession session,
    required String nom,
    required String prenom,
    required String email,
  }) async {
    final uri = Uri.parse('${session.serverBaseUrl}/api/student_profile_update.php');
    http.Response res;
    try {
      res = await _client
          .post(
            uri,
            headers: {'Content-Type': 'application/json; charset=UTF-8'},
            body: jsonEncode({
              'auth_token': session.authToken,
              'nom': nom,
              'prenom': prenom,
              'email': email,
            }),
          )
          .timeout(AppConfig.httpTimeout);
    } catch (_) {
      throw ProfileException('Réseau indisponible ou délai dépassé.');
    }

    final json = _decode(res);
    if (json['success'] == true) {
      return json['message']?.toString() ?? 'Profil mis à jour.';
    }
    throw ProfileException(json['message']?.toString() ?? 'Erreur.');
  }

  Future<List<CourseRow>> fetchCourses(StudentSession session) async {
    final uri = Uri.parse('${session.serverBaseUrl}/api/student_courses.php');
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
      throw ProfileException('Réseau indisponible ou délai dépassé.');
    }

    final json = _decode(res);
    if (json['success'] != true) {
      throw ProfileException(json['message']?.toString() ?? 'Erreur.');
    }
    final data = json['data'];
    if (data is! Map<String, dynamic>) return [];
    final list = data['cours'];
    if (list is! List) return [];
    return list.whereType<Map<String, dynamic>>().map(CourseRow.fromJson).toList();
  }

  Map<String, dynamic> _decode(http.Response res) {
    try {
      return jsonDecode(utf8.decode(res.bodyBytes)) as Map<String, dynamic>;
    } catch (_) {
      throw ProfileException('Réponse serveur invalide.');
    }
  }

  void dispose() {
    _client.close();
  }
}

