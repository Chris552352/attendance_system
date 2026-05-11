import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';

import 'package:presence_etudiant_app/config/app_config.dart';
import 'package:presence_etudiant_app/l10n/app_localizations.dart';
import 'package:presence_etudiant_app/services/session_storage.dart';
import 'package:presence_etudiant_app/utils/barcode_payload.dart';
import 'package:presence_etudiant_app/utils/qr_url_parser.dart';

class ConfigureServerScreen extends StatefulWidget {
  const ConfigureServerScreen({super.key});

  @override
  State<ConfigureServerScreen> createState() => _ConfigureServerScreenState();
}

class _ConfigureServerScreenState extends State<ConfigureServerScreen> {
  final MobileScannerController _controller = MobileScannerController(
    detectionSpeed: DetectionSpeed.noDuplicates,
    facing: CameraFacing.back,
  );

  bool _busy = false;
  String? _lastPayload;
  DateTime? _lastPayloadAt;

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  bool _isDuplicateTooSoon(String raw) {
    final now = DateTime.now();
    if (_lastPayload == raw &&
        _lastPayloadAt != null &&
        now.difference(_lastPayloadAt!) < AppConfig.scanCooldown) {
      return true;
    }
    _lastPayload = raw;
    _lastPayloadAt = now;
    return false;
  }

  Future<void> _handle(String? raw) async {
    final l10n = AppLocalizations.of(context)!;
    if (raw == null || raw.isEmpty || _busy) return;
    if (_isDuplicateTooSoon(raw)) return;

    setState(() => _busy = true);
    await _controller.stop();

    try {
      final base = extractBaseUrlFromQrUrl(raw);
      await SessionStorage.saveServerBaseUrl(base);
      if (!mounted) return;
      Navigator.of(context).pop(base);
    } on InvalidQrException catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.message), backgroundColor: Colors.orange.shade900),
        );
      }
      await _controller.start();
      if (mounted) setState(() => _busy = false);
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(l10n.invalidQrSnackbar), backgroundColor: Colors.red),
        );
      }
      await _controller.start();
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;

    return Scaffold(
      appBar: AppBar(
        title: Text(l10n.configureServerTitle),
        actions: [
          IconButton(
            tooltip: l10n.torchTooltip,
            onPressed: () => _controller.toggleTorch(),
            icon: const Icon(Icons.flashlight_on_outlined),
          ),
        ],
      ),
      body: Stack(
        children: [
          MobileScanner(
            controller: _controller,
            onDetect: (capture) {
              if (_busy) return;
              for (final b in capture.barcodes) {
                final v = stringPayloadFromBarcode(b);
                if (v != null) {
                  _handle(v);
                  break;
                }
              }
            },
          ),
          if (_busy)
            const Positioned.fill(
              child: ColoredBox(
                color: Color(0x66000000),
                child: Center(child: CircularProgressIndicator()),
              ),
            ),
          Positioned(
            left: 16,
            right: 16,
            bottom: 32,
            child: Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Text(
                  _busy ? l10n.configuringServer : l10n.scanQrForServerHint,
                  textAlign: TextAlign.center,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
