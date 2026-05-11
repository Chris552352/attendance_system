/// Promotion universitaire (niveau LMD + filière + groupe) renvoyée par l’API.
class StudentClassInfo {
  const StudentClassInfo({
    required this.id,
    required this.code,
    required this.nom,
    this.niveau,
    this.filiere,
  });

  final int id;
  final String code;
  final String nom;
  final String? niveau;
  final String? filiere;

  /// Libellé pour l’UI : code — niveau · filière · nom du groupe.
  String get displayLine {
    final parts = <String>[];
    final n = niveau?.trim();
    final f = filiere?.trim();
    if (n != null && n.isNotEmpty) parts.add(n);
    if (f != null && f.isNotEmpty) parts.add(f);
    final g = nom.trim();
    if (g.isNotEmpty) parts.add(g);
    if (parts.isEmpty) return code;
    return '$code — ${parts.join(' · ')}';
  }

  static StudentClassInfo? maybeFromJson(dynamic raw) {
    if (raw is! Map<String, dynamic>) return null;
    final id = raw['id'];
    final code = raw['code']?.toString();
    final nom = raw['nom']?.toString();
    if (id is! num || code == null || nom == null) return null;
    final n = raw['niveau']?.toString();
    final fi = raw['filiere']?.toString();
    return StudentClassInfo(
      id: id.toInt(),
      code: code,
      nom: nom,
      niveau: n != null && n.isNotEmpty ? n : null,
      filiere: fi != null && fi.isNotEmpty ? fi : null,
    );
  }
}
