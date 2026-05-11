import 'package:presence_etudiant_app/models/student_class_info.dart';

/// Session locale après connexion (identité + jeton API + base du serveur).
class StudentSession {
  const StudentSession({
    required this.etudiantId,
    required this.matricule,
    required this.email,
    required this.prenom,
    required this.nom,
    required this.authToken,
    required this.serverBaseUrl,
    this.classe,
  });

  final int etudiantId;
  final String matricule;
  final String email;
  final String prenom;
  final String nom;
  final String authToken;

  /// Ex. `http://192.168.1.10/attendance_system` (sans slash final).
  final String serverBaseUrl;

  /// Groupe / classe renvoyé à la connexion (null si non affecté).
  final StudentClassInfo? classe;

  String get displayName => '$prenom $nom'.trim();
}
