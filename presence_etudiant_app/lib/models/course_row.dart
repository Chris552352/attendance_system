class CourseRow {
  const CourseRow({
    required this.coursId,
    required this.code,
    required this.nom,
    required this.enseignant,
    this.description,
    this.enseignantEmail,
    this.dateInscription,
  });

  final int coursId;
  final String code;
  final String nom;
  final String enseignant;
  final String? description;
  final String? enseignantEmail;
  final String? dateInscription;

  String get label => code.isNotEmpty ? '$code — $nom' : nom;

  static CourseRow fromJson(Map<String, dynamic> j) {
    return CourseRow(
      coursId: (j['cours_id'] as num).toInt(),
      code: j['code']?.toString() ?? '',
      nom: j['nom']?.toString() ?? '',
      description: j['description']?.toString(),
      enseignant: j['enseignant']?.toString() ?? '',
      enseignantEmail: j['enseignant_email']?.toString(),
      dateInscription: j['date_inscription']?.toString(),
    );
  }
}

