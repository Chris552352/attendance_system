import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';

import 'package:presence_etudiant_app/config/app_config.dart';
import 'package:presence_etudiant_app/l10n/app_localizations.dart';
import 'package:presence_etudiant_app/models/student_session.dart';
import 'package:presence_etudiant_app/services/biometric_auth_service.dart';
import 'package:presence_etudiant_app/services/presence_service.dart';
import 'package:presence_etudiant_app/utils/barcode_payload.dart';
import 'package:presence_etudiant_app/utils/qr_url_parser.dart';

class ScanScreen extends StatefulWidget {
  const ScanScreen({super.key, required this.session});

  final StudentSession session;

  @override
  State<ScanScreen> createState() => _ScanScreenState();
}

class _ScanScreenState extends State<ScanScreen> {
  final MobileScannerController _controller = MobileScannerController(
    detectionSpeed: DetectionSpeed.noDuplicates,
    facing: CameraFacing.back,
  );

  final PresenceService _presence = PresenceService();

  bool _busy = false;
  String? _lastPayload;
  DateTime? _lastPayloadAt;

  @override
  void dispose() {
    _controller.dispose();
    _presence.dispose();
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

  Future<void> _handleBarcode(String? raw) async {
    if (raw == null || raw.isEmpty || _busy) return;
    if (_isDuplicateTooSoon(raw)) return;

    final l10n = AppLocalizations.of(context)!;

    setState(() => _busy = true);
    await _controller.stop();

    try {
      final params = parseQrPayload(raw);

      final bio = await BiometricAuthService.confirmBeforePresence(
        localizedReason: l10n.biometricConfirmReason,
      );
      if (bio == BiometricPresenceResult.userCancelled) {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text(l10n.biometricCancelledSnackbar),
              backgroundColor: Colors.grey.shade800,
            ),
          );
        }
        await _controller.start();
        if (mounted) setState(() => _busy = false);
        return;
      }
      if (bio == BiometricPresenceResult.hardwareUnavailable && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(l10n.biometricUnavailableSnackbar),
            backgroundColor: Colors.blueGrey.shade700,
            duration: const Duration(seconds: 4),
          ),
        );
      }

      final message = await _presence.markPresence(
        session: widget.session,
        qr: params,
      );
      if (!mounted) return;
      Navigator.of(context).pop((true, message));
    } on InvalidQrException catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.message), backgroundColor: Colors.orange.shade900),
        );
      }
      await _controller.start();
      if (mounted) setState(() => _busy = false);
    } on PresenceException catch (e) {
      if (mounted) {
        final Color color = e.code == 'already_marked'
            ? Colors.amber.shade900
            : (e.code == 'teacher_only'
                ? Colors.deepOrange.shade900
                : (e.code == 'device_mismatch' ||
                        e.code == 'device_login_required' ||
                        e.code == 'device_required'
                    ? Colors.deepPurple.shade900
                    : Colors.red.shade800));
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(e.message), backgroundColor: color),
        );
      }
      await _controller.start();
      if (mounted) setState(() => _busy = false);
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(l10n.unexpectedError), backgroundColor: Colors.red),
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
        title: Text(l10n.scanQrTitle),
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
                  _handleBarcode(v);
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
                  _busy ? l10n.biometricOrSendInProgress : l10n.frameQrHint,
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
