import 'package:shared_preferences/shared_preferences.dart';

import 'package:presence_etudiant_app/models/student_class_info.dart';
import 'package:presence_etudiant_app/models/student_session.dart';

/// Persistance légère de la session (matricule, jeton, URL serveur).
class SessionStorage {
  SessionStorage._();

  static const _kEtudiantId = 'etudiant_id';
  static const _kMatricule = 'matricule';
  static const _kEmail = 'email';
  static const _kPrenom = 'prenom';
  static const _kNom = 'nom';
  static const _kAuthToken = 'auth_token';
  static const _kServerBase = 'server_base_url';
  static const _kClasseId = 'classe_id';
  static const _kClasseCode = 'classe_code';
  static const _kClasseNom = 'classe_nom';
  static const _kClasseNiveau = 'classe_niveau';
  static const _kClasseFiliere = 'classe_filiere';

  static Future<String?> loadServerBaseUrl() async {
    final p = await SharedPreferences.getInstance();
    final base = p.getString(_kServerBase);
    if (base == null || base.trim().isEmpty) return null;
    return base;
  }

  static Future<void> saveServerBaseUrl(String baseUrl) async {
    final p = await SharedPreferences.getInstance();
    await p.setString(_kServerBase, baseUrl);
  }

  static Future<StudentSession?> load() async {
    final p = await SharedPreferences.getInstance();
    final id = p.getInt(_kEtudiantId);
    final token = p.getString(_kAuthToken);
    final base = p.getString(_kServerBase);
    final matricule = p.getString(_kMatricule);
    final email = p.getString(_kEmail) ?? '';
    final prenom = p.getString(_kPrenom);
    final nom = p.getString(_kNom);
    final cid = p.getInt(_kClasseId);
    final ccode = p.getString(_kClasseCode);
    final cnom = p.getString(_kClasseNom);

    if (id == null ||
        token == null ||
        base == null ||
        matricule == null ||
        prenom == null ||
        nom == null) {
      return null;
    }

    StudentClassInfo? classe;
    if (cid != null && ccode != null && cnom != null) {
      final nv = p.getString(_kClasseNiveau);
      final fi = p.getString(_kClasseFiliere);
      classe = StudentClassInfo(
        id: cid,
        code: ccode,
        nom: cnom,
        niveau: nv != null && nv.isNotEmpty ? nv : null,
        filiere: fi != null && fi.isNotEmpty ? fi : null,
      );
    }

    return StudentSession(
      etudiantId: id,
      matricule: matricule,
      email: email,
      prenom: prenom,
      nom: nom,
      authToken: token,
      serverBaseUrl: base,
      classe: classe,
    );
  }

  static Future<void> save(StudentSession session) async {
    final p = await SharedPreferences.getInstance();
    await p.setInt(_kEtudiantId, session.etudiantId);
    await p.setString(_kMatricule, session.matricule);
    await p.setString(_kEmail, session.email);
    await p.setString(_kPrenom, session.prenom);
    await p.setString(_kNom, session.nom);
    await p.setString(_kAuthToken, session.authToken);
    await p.setString(_kServerBase, session.serverBaseUrl);
    final c = session.classe;
    if (c != null) {
      await p.setInt(_kClasseId, c.id);
      await p.setString(_kClasseCode, c.code);
      await p.setString(_kClasseNom, c.nom);
      if (c.niveau != null) {
        await p.setString(_kClasseNiveau, c.niveau!);
      } else {
        await p.remove(_kClasseNiveau);
      }
      if (c.filiere != null) {
        await p.setString(_kClasseFiliere, c.filiere!);
      } else {
        await p.remove(_kClasseFiliere);
      }
    } else {
      await p.remove(_kClasseId);
      await p.remove(_kClasseCode);
      await p.remove(_kClasseNom);
      await p.remove(_kClasseNiveau);
      await p.remove(_kClasseFiliere);
    }
  }

  static Future<void> clear() async {
    final p = await SharedPreferences.getInstance();
    await p.remove(_kEtudiantId);
    await p.remove(_kMatricule);
    await p.remove(_kEmail);
    await p.remove(_kPrenom);
    await p.remove(_kNom);
    await p.remove(_kAuthToken);
    await p.remove(_kServerBase);
    await p.remove(_kClasseId);
    await p.remove(_kClasseCode);
    await p.remove(_kClasseNom);
    await p.remove(_kClasseNiveau);
    await p.remove(_kClasseFiliere);
  }
}
