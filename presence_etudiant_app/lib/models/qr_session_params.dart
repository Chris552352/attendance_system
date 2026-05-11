/// Paramètres extraits du QR (aligné sur `presence_qr.php?seance=&token=`).
class QrSessionParams {
  const QrSessionParams({required this.seanceId, required this.token});

  final int seanceId;
  final String token;
}
