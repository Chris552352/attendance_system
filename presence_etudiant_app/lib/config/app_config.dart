/// Constantes partagées (projet académique — pas de secrets côté client).
class AppConfig {
  AppConfig._();

  /// Délai minimum entre deux traitements de scan (évite les doubles lectures).
  static const Duration scanCooldown = Duration(seconds: 4);

  /// Timeout des requêtes HTTP vers le serveur local.
  static const Duration httpTimeout = Duration(seconds: 20);
}
