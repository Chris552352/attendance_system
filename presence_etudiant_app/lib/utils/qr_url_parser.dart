import 'package:presence_etudiant_app/models/qr_session_params.dart';

/// Erreur métier : URL du QR non reconnue ou incomplète.
class InvalidQrException implements Exception {
  InvalidQrException(this.message);
  final String message;

  @override
  String toString() => message;
}

/// Retire les artefacts fréquents des lecteurs QR (BOM, préfixes, espaces, bytes nuls).
String normalizeRawQrPayload(String raw) {
  var s = raw.trim().replaceAll('\x00', '');
  if (s.startsWith('\uFEFF')) {
    s = s.substring(1).trim();
  }
  const prefixes = ['URL:', 'url:', 'URI:', 'uri:'];
  for (final p in prefixes) {
    if (s.startsWith(p)) {
      s = s.substring(p.length).trim();
      break;
    }
  }
  final lines = s.split(RegExp(r'[\r\n]+'));
  for (final line in lines) {
    final t = line.trim();
    if (t.startsWith('http://') || t.startsWith('https://')) {
      s = t;
      break;
    }
  }
  return s;
}

/// Première sous-chaîne `http(s)://…` qui ressemble à une URL complète (évite le bruit autour).
String? _extractHttpUrlSubstring(String s) {
  final m = RegExp(
    r'https?://[^\s<>"{}|\\^`\[\]\u0000-\u001F]+',
    caseSensitive: false,
  ).firstMatch(s);
  return m?.group(0);
}

/// Format bookmark ZXing / ML Kit : `MEBKM:TITLE:…;URL:http://hôte/…;;`
/// ([Barcode.rawValue] ne commence pas par `http` dans ce cas.)
String? extractHttpUrlFromMebkm(String s) {
  if (!s.toUpperCase().contains('MEBKM:')) {
    return null;
  }
  for (final segment in s.split(';')) {
    final t = segment.trim();
    if (!t.toUpperCase().startsWith('URL:')) {
      continue;
    }
    var url = t.substring(4).trim();
    if (url.startsWith('//')) {
      url = 'http:$url';
    }
    if (url.startsWith('http://') || url.startsWith('https://')) {
      return url;
    }
  }
  return null;
}

/// Parse une URL http(s) : tentatives multiples (lecteurs QR capricieux / bruit).
Uri parseHttpUriFromQr(String raw) {
  final trimmed = raw.trim();
  if (trimmed.isEmpty) {
    throw InvalidQrException('QR vide.');
  }

  final candidates = <String>{
    trimmed,
    normalizeRawQrPayload(trimmed),
  };
  if (trimmed.startsWith('//')) {
    candidates.add('http:$trimmed');
  }
  for (final base in [trimmed, normalizeRawQrPayload(trimmed)]) {
    final mebkm = extractHttpUrlFromMebkm(base);
    if (mebkm != null) {
      candidates.add(mebkm);
    }
  }
  for (final c in candidates.toList()) {
    final extracted = _extractHttpUrlSubstring(c);
    if (extracted != null) {
      candidates.add(extracted);
      candidates.add(normalizeRawQrPayload(extracted));
    }
  }

  for (final attempt in candidates) {
    if (attempt.isEmpty) continue;
    Uri? uri;
    try {
      uri = Uri.parse(attempt);
    } catch (_) {
      uri = Uri.tryParse(attempt);
    }
    if (uri != null && uri.hasScheme && (uri.scheme == 'http' || uri.scheme == 'https')) {
      return uri;
    }
  }

  throw InvalidQrException('Contenu du QR illisible.');
}

/// Extrait l’URL base du serveur à partir d’un QR contenant un lien web du projet.
///
/// Exemple d’entrée :
/// `http://192.168.1.10/attendance_system/presence_qr.php?seance=1&token=...`
/// Sortie :
/// `http://192.168.1.10/attendance_system`
String extractBaseUrlFromQrUrl(String raw) {
  final uri = parseHttpUriFromQr(raw);

  // On retire le fichier (ex: presence_qr.php) pour garder le dossier du projet.
  final seg = uri.pathSegments;
  if (seg.isEmpty) {
    throw InvalidQrException('URL du QR incomplète.');
  }
  final withoutLast = seg.length > 1 ? seg.take(seg.length - 1).toList() : <String>[];
  final basePath = withoutLast.isEmpty ? '' : '/${withoutLast.join('/')}';
  return '${uri.scheme}://${uri.authority}$basePath';
}

/// Extrait `seance` (ou `session`) et `token` depuis l’URL encodée dans le QR.
QrSessionParams parseQrPayload(String raw) {
  final uri = parseHttpUriFromQr(raw);

  final qp = uri.queryParameters;
  final seanceRaw = qp['seance'] ?? qp['session'];
  final token = qp['token'];

  if (seanceRaw == null || seanceRaw.isEmpty) {
    throw InvalidQrException('Paramètre de séance manquant dans l’URL.');
  }
  if (token == null || token.isEmpty) {
    throw InvalidQrException('Token de sécurité manquant dans l’URL.');
  }

  final seanceId = int.tryParse(seanceRaw);
  if (seanceId == null || seanceId <= 0) {
    throw InvalidQrException('Identifiant de séance invalide.');
  }

  return QrSessionParams(seanceId: seanceId, token: token);
}
