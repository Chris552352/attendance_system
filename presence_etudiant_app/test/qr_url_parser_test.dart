import 'package:flutter_test/flutter_test.dart';
import 'package:presence_etudiant_app/utils/qr_url_parser.dart';

void main() {
  test('extractBaseUrlFromQrUrl accepte le préfixe URL:', () {
    expect(
      extractBaseUrlFromQrUrl(
        'URL:http://192.168.1.2/attendance_system/presence_qr.php?seance=1&token=abcdef',
      ),
      'http://192.168.1.2/attendance_system',
    );
  });

  test('parseQrPayload lit seance et token', () {
    final p = parseQrPayload(
      'http://10.0.0.1/attendance_system/presence_qr.php?seance=42&token=feedface',
    );
    expect(p.seanceId, 42);
    expect(p.token, 'feedface');
  });

  test('normalizeRawQrPayload enlève BOM et bytes nuls', () {
    const raw = '\uFEFFhttp://192.168.0.5/attendance_system/presence_qr.php?seance=1&token=a\x00';
    expect(
      normalizeRawQrPayload(raw),
      'http://192.168.0.5/attendance_system/presence_qr.php?seance=1&token=a',
    );
  });

  test('extractBaseUrlFromQrUrl gère le format MEBKM de ML Kit (rawValue)', () {
    expect(
      extractBaseUrlFromQrUrl(
        'MEBKM:TITLE:français;URL:http://192.168.0.3/attendance_system/presence_qr.php?seance=2&token=abcdef;;',
      ),
      'http://192.168.0.3/attendance_system',
    );
  });

  test('parseHttpUriFromQr accepte une URL //hôte (schéma implicite)', () {
    final u = parseHttpUriFromQr(
      '//192.168.0.3/attendance_system/presence_qr.php?seance=1&token=a',
    );
    expect(u.scheme, 'http');
    expect(u.authority, '192.168.0.3');
  });
}
