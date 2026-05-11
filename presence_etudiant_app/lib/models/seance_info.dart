class SeanceInfo {
  final int id;
  final String nomCours;
  final String typeSeance;
  final String salleNom;
  final String modeMarquage;
  final String token;
  final String? heureDebut;
  final String? heureFin;
  final String statutSeance;
  /// Indique si l’étudiant peut pointer via QR (faux pour examen/contrôle/conférence manuelle).
  final bool studentQrAllowed;

  SeanceInfo({
    required this.id,
    required this.nomCours,
    required this.typeSeance,
    required this.salleNom,
    required this.modeMarquage,
    required this.token,
    this.heureDebut,
    this.heureFin,
    required this.statutSeance,
    required this.studentQrAllowed,
  });

  factory SeanceInfo.fromJson(Map<String, dynamic> json) {
    final mode = json['mode_marquage']?.toString() ?? 'qr';
    final allowed = json['student_qr_allowed'];
    return SeanceInfo(
      id: json['id'] ?? 0,
      nomCours: json['nom_cours'] ?? '',
      typeSeance: json['type_seance'] ?? '',
      salleNom: json['salle_nom'] ?? '',
      modeMarquage: mode,
      token: json['token'] ?? '',
      heureDebut: json['heure_debut'],
      heureFin: json['heure_fin'],
      statutSeance: json['statut_seance'] ?? 'planifiee',
      studentQrAllowed: allowed is bool ? allowed : (mode == 'qr'),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'nom_cours': nomCours,
      'type_seance': typeSeance,
      'salle_nom': salleNom,
      'mode_marquage': modeMarquage,
      'token': token,
      'heure_debut': heureDebut,
      'heure_fin': heureFin,
      'statut_seance': statutSeance,
      'student_qr_allowed': studentQrAllowed,
    };
  }

  bool get isExamMode => modeMarquage == 'enseignant_seul';
  bool get isQrMode => modeMarquage == 'qr';
  bool get isManualMode => modeMarquage == 'manuel';
  bool get isActive => statutSeance == 'en_cours';
}
