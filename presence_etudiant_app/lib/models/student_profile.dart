import 'package:presence_etudiant_app/models/student_class_info.dart';

class StudentProfile {
  const StudentProfile({
    required this.id,
    required this.matricule,
    required this.nom,
    required this.prenom,
    required this.email,
    this.dateInscription,
    this.classe,
  });

  final int id;
  final String matricule;
  final String nom;
  final String prenom;
  final String email;
  final String? dateInscription;
  final StudentClassInfo? classe;

  String get displayName => '$prenom $nom'.trim();

  static StudentProfile fromJson(Map<String, dynamic> j) {
    return StudentProfile(
      id: (j['id'] as num).toInt(),
      matricule: j['matricule']?.toString() ?? '',
      nom: j['nom']?.toString() ?? '',
      prenom: j['prenom']?.toString() ?? '',
      email: j['email']?.toString() ?? '',
      dateInscription: j['date_inscription']?.toString(),
      classe: StudentClassInfo.maybeFromJson(j['classe']),
    );
  }
}

