import 'dart:convert';

import 'package:mobile_scanner/mobile_scanner.dart';

/// Récupère le texte exploitable d’un [Barcode].
///
/// Sur Android/iOS, Google ML Kit classe souvent un simple lien HTTP en
/// **type URL** : la valeur correcte est alors dans [Barcode.url], alors que
/// [Barcode.rawValue] peut être vide, du **MEBKM:** (`;URL:http://…;`) ou
/// une forme non directement parseable par [Uri.parse].
String? stringPayloadFromBarcode(Barcode barcode) {
  final bookmarkUrl = barcode.url?.url.trim();
  if (bookmarkUrl != null && bookmarkUrl.isNotEmpty) {
    if (bookmarkUrl.startsWith('//')) {
      return 'http:$bookmarkUrl';
    }
    return bookmarkUrl;
  }

  final raw = barcode.rawValue?.trim();
  if (raw != null && raw.isNotEmpty) {
    return raw;
  }

  final bytes = barcode.rawBytes;
  if (bytes != null && bytes.isNotEmpty) {
    try {
      return utf8.decode(bytes);
    } catch (_) {}
  }

  final display = barcode.displayValue?.trim();
  if (display != null && display.isNotEmpty) {
    return display;
  }

  return null;
}
