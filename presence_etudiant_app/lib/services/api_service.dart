import 'dart:convert';
import 'package:http/http.dart' as http;

import 'package:presence_etudiant_app/models/seance_info.dart';
import 'package:presence_etudiant_app/services/device_identity.dart';

class ApiService {
  static const String baseUrl = 'http://192.168.167.70:8000/api';

  // Récupérer les informations d'une séance
  static Future<SeanceInfo?> getSeanceInfo(int seanceId, String authToken) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/seance_info.php?seance_id=$seanceId'),
        headers: {
          'Authorization': 'Bearer $authToken',
          'Content-Type': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success']) {
          return SeanceInfo.fromJson(data['data']);
        }
      }
      return null;
    } catch (e) {
      throw Exception('Erreur lors de la récupération des infos de séance: $e');
    }
  }

  // Marquer la présence
  static Future<bool> markPresence(
    int seanceId,
    String token,
    String authToken,
  ) async {
    try {
      final deviceId = await DeviceIdentity.getOrCreateDeviceId();
      final response = await http.post(
        Uri.parse('$baseUrl/mark_presence_mobile.php'),
        headers: {
          'Authorization': 'Bearer $authToken',
          'Content-Type': 'application/json',
        },
        body: json.encode({
          'seance_id': seanceId,
          'token': token,
          'auth_token': authToken,
          'device_id': deviceId,
        }),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] ?? false;
      }
      return false;
    } catch (e) {
      throw Exception('Erreur lors du marquage de présence: $e');
    }
  }

  // Récupérer les présences de l'étudiant
  static Future<List<Map<String, dynamic>>> getStudentPresences(
    String authToken,
  ) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/student_presences.php'),
        headers: {
          'Authorization': 'Bearer $authToken',
          'Content-Type': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success']) {
          return List<Map<String, dynamic>>.from(data['data']);
        }
      }
      return [];
    } catch (e) {
      throw Exception('Erreur lors de la récupération des présences: $e');
    }
  }

  // Récupérer les cours de l'étudiant
  static Future<List<Map<String, dynamic>>> getStudentCourses(
    String authToken,
  ) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/student_courses.php'),
        headers: {
          'Authorization': 'Bearer $authToken',
          'Content-Type': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success']) {
          return List<Map<String, dynamic>>.from(data['data']);
        }
      }
      return [];
    } catch (e) {
      throw Exception('Erreur lors de la récupération des cours: $e');
    }
  }

  // Justifier une absence
  static Future<bool> justifyAbsence(
    int presenceId,
    String motif,
    String authToken,
  ) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/student_justify_absence.php'),
        headers: {
          'Authorization': 'Bearer $authToken',
          'Content-Type': 'application/json',
        },
        body: json.encode({
          'presence_id': presenceId,
          'motif': motif,
          'auth_token': authToken,
        }),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] ?? false;
      }
      return false;
    } catch (e) {
      throw Exception('Erreur lors de la justification: $e');
    }
  }

  // Mettre à jour le profil étudiant
  static Future<bool> updateStudentProfile(
    Map<String, dynamic> profileData,
    String authToken,
  ) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/student_profile_update.php'),
        headers: {
          'Authorization': 'Bearer $authToken',
          'Content-Type': 'application/json',
        },
        body: json.encode({
          ...profileData,
          'auth_token': authToken,
        }),
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        return data['success'] ?? false;
      }
      return false;
    } catch (e) {
      throw Exception('Erreur lors de la mise à jour du profil: $e');
    }
  }

  // Récupérer le profil étudiant
  static Future<Map<String, dynamic>?> getStudentProfile(
    String authToken,
  ) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/student_profile.php'),
        headers: {
          'Authorization': 'Bearer $authToken',
          'Content-Type': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success']) {
          return data['data'];
        }
      }
      return null;
    } catch (e) {
      throw Exception('Erreur lors de la récupération du profil: $e');
    }
  }
}
