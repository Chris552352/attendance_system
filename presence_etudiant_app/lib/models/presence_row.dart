class PresenceRow {
  const PresenceRow({
    required this.presenceId,
    required this.datePresence,
    required this.statut,
    required this.justifie,
    required this.coursNom,
    required this.coursCode,
    this.justifStatut,
    this.justifContenu,
    this.justifDate,
    this.justifCommentaire,
  });

  final int presenceId;
  final String datePresence;
  final String statut;
  final bool justifie;
  final String coursNom;
  final String coursCode;
  final String? justifStatut;
  final String? justifContenu;
  final String? justifDate;
  final String? justifCommentaire;

  bool get isAbsent => statut.toLowerCase() == 'absent';

  bool get hasPendingJustification => justifStatut == 'en_attente';

  /// Même logique que le tableau de bord web : pas de doublon tant qu’une demande est en attente.
  bool get canSubmitJustification => isAbsent && !justifie && !hasPendingJustification;

  String get coursLabel {
    if (coursCode.isNotEmpty) return '$coursCode — $coursNom';
    return coursNom.isEmpty ? 'Cours' : coursNom;
  }

  static PresenceRow fromJson(Map<String, dynamic> j) {
    return PresenceRow(
      presenceId: (j['presence_id'] as num).toInt(),
      datePresence: j['date_presence']?.toString() ?? '',
      statut: j['statut']?.toString() ?? '',
      justifie: j['justifie'] == true,
      coursNom: j['cours_nom']?.toString() ?? '',
      coursCode: j['cours_code']?.toString() ?? '',
      justifStatut: j['justif_statut']?.toString(),
      justifContenu: j['justif_contenu']?.toString(),
      justifDate: j['justif_date']?.toString(),
      justifCommentaire: j['justif_commentaire']?.toString(),
    );
  }
}
