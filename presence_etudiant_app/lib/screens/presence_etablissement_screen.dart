import 'package:flutter/material.dart';
import 'package:presence_etudiant_app/l10n/app_localizations.dart';
import 'package:presence_etudiant_app/services/api_service.dart';
import 'package:presence_etudiant_app/services/biometric_auth_service.dart';
import 'package:presence_etudiant_app/models/student_session.dart';
import 'package:presence_etudiant_app/models/seance_info.dart';
import 'package:presence_etudiant_app/services/session_storage.dart';
import 'package:qr_code_scanner/qr_code_scanner.dart';

class PresenceEtablissementScreen extends StatefulWidget {
  final StudentSession session;

  const PresenceEtablissementScreen({Key? key, required this.session}) : super(key: key);

  @override
  _PresenceEtablissementScreenState createState() => _PresenceEtablissementScreenState();
}

class _PresenceEtablissementScreenState extends State<PresenceEtablissementScreen> {
  final GlobalKey qrKey = GlobalKey(debugLabel: 'QR');
  QRViewController? controller;
  bool isScanning = false;
  SeanceInfo? currentSeance;
  List<Map<String, dynamic>> mesPresences = [];
  bool isLoading = false;
  String? errorMessage;

  @override
  void initState() {
    super.initState();
    _loadMesPresences();
  }

  @override
  void dispose() {
    controller?.dispose();
    super.dispose();
  }

  Future<void> _loadMesPresences() async {
    setState(() => isLoading = true);
    try {
      final presences = await ApiService.getStudentPresences(widget.session.authToken);
      setState(() {
        mesPresences = presences;
        isLoading = false;
        errorMessage = null;
      });
    } catch (e) {
      setState(() {
        isLoading = false;
        errorMessage = e.toString();
      });
    }
  }

  void _onQRViewCreated(QRViewController controller) {
    this.controller = controller;
    controller.scannedDataStream.listen((scanData) {
      if (scanData != null && isScanning) {
        _processQRCode(scanData.code!);
      }
    });
  }

  Future<void> _processQRCode(String qrData) async {
    setState(() => isScanning = false);
    controller?.pauseCamera();

    try {
      // Parser les données du QR code
      final uri = Uri.parse(qrData);
      final seanceId = int.parse(uri.queryParameters['seance_id'] ?? '0');
      final token = uri.queryParameters['token'] ?? '';

      if (seanceId > 0 && token.isNotEmpty) {
        // Récupérer les infos de la séance
        final seance = await ApiService.getSeanceInfo(seanceId, widget.session.authToken);
        
        if (seance != null) {
          setState(() => currentSeance = seance);
          _showSeanceDialog();
        } else {
          _showError('Séance invalide ou expirée');
        }
      } else {
        _showError('QR code invalide');
      }
    } catch (e) {
      _showError('Erreur lors du traitement du QR code: $e');
    }
  }

  void _showSeanceDialog() {
    if (currentSeance == null) return;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        title: Text('Séance: ${currentSeance!.nomCours}'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text('Type: ${currentSeance!.typeSeance}'),
            Text('Salle: ${currentSeance!.salleNom}'),
            Text('Mode: ${currentSeance!.modeMarquage}'),
            const SizedBox(height: 10),
            if (!currentSeance!.studentQrAllowed)
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: Colors.red.shade50,
                  border: Border.all(color: Colors.red.shade200),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Row(
                  children: [
                    Icon(Icons.warning, color: Colors.red),
                    SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'Pas de pointage QR : l’enseignant enregistre les présences (examen, contrôle ou mode manuel).',
                        style: TextStyle(color: Colors.red, fontWeight: FontWeight.bold),
                      ),
                    ),
                  ],
                ),
              ),
            if (currentSeance!.studentQrAllowed && currentSeance!.modeMarquage == 'qr')
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: Colors.green.shade50,
                  border: Border.all(color: Colors.green.shade200),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Row(
                  children: [
                    Icon(Icons.check_circle, color: Colors.green),
                    SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'Mode NORMAL: Vous pouvez marquer votre présence',
                        style: TextStyle(color: Colors.green, fontWeight: FontWeight.bold),
                      ),
                    ),
                  ],
                ),
              ),
          ],
        ),
        actions: [
          if (currentSeance!.studentQrAllowed && currentSeance!.modeMarquage == 'qr')
            TextButton(
              onPressed: () => _marquerPresence(),
              child: const Text('Marquer ma présence'),
            ),
          TextButton(
            onPressed: () {
              Navigator.of(context).pop();
              controller?.resumeCamera();
              setState(() => isScanning = true);
            },
            child: const Text('Fermer'),
          ),
        ],
      ),
    );
  }

  Future<void> _marquerPresence() async {
    if (currentSeance == null) return;

    final l10n = AppLocalizations.of(context)!;
    final bio = await BiometricAuthService.confirmBeforePresence(
      localizedReason: l10n.biometricConfirmReason,
    );
    if (bio == BiometricPresenceResult.userCancelled) {
      if (mounted) {
        _showError(l10n.authCancelledNoPresence);
      }
      return;
    }

    try {
      final success = await ApiService.markPresence(
        currentSeance!.id,
        currentSeance!.token,
        widget.session.authToken,
      );

      Navigator.of(context).pop(); // Fermer le dialog

      if (success) {
        _showSuccess(l10n.presenceMarkedSuccess);
        _loadMesPresences(); // Rafraîchir la liste
      } else {
        _showError(l10n.presenceMarkFailed);
      }
    } catch (e) {
      Navigator.of(context).pop();
      _showError('${l10n.errorGeneric}: $e');
    }
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.red,
        duration: const Duration(seconds: 3),
      ),
    );
  }

  void _showSuccess(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: Colors.green,
        duration: const Duration(seconds: 3),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Présence - Établissement'),
        backgroundColor: const Color(0xFF1565C0),
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.history),
            onPressed: () => _showHistorique(),
          ),
        ],
      ),
      body: Column(
        children: [
          // Header avec infos étudiant
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [Colors.blue.shade600, Colors.blue.shade800],
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  '${widget.session.prenom} ${widget.session.nom}',
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                Text(
                  'Matricule: ${widget.session.matricule}',
                  style: const TextStyle(color: Colors.white70),
                ),
              ],
            ),
          ),

          // Zone de scan QR
          Expanded(
            flex: 2,
            child: Container(
              margin: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                border: Border.all(color: Colors.grey.shade300),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Column(
                children: [
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.grey.shade100,
                      borderRadius: const BorderRadius.only(
                        topLeft: Radius.circular(12),
                        topRight: Radius.circular(12),
                      ),
                    ),
                    child: Row(
                      children: [
                        Icon(
                          Icons.qr_code_scanner,
                          color: isScanning ? Colors.green : Colors.grey,
                        ),
                        const SizedBox(width: 8),
                        Text(
                          isScanning ? 'Scan en cours...' : 'Scanner un QR code',
                          style: TextStyle(
                            fontWeight: FontWeight.bold,
                            color: isScanning ? Colors.green : Colors.grey,
                          ),
                        ),
                        const Spacer(),
                        if (isScanning)
                          ElevatedButton(
                            onPressed: () {
                              controller?.pauseCamera();
                              setState(() => isScanning = false);
                            },
                            style: ElevatedButton.styleFrom(
                              backgroundColor: Colors.red,
                              foregroundColor: Colors.white,
                            ),
                            child: const Text('Arrêter'),
                          )
                        else
                          ElevatedButton(
                            onPressed: () {
                              controller?.resumeCamera();
                              setState(() => isScanning = true);
                            },
                            style: ElevatedButton.styleFrom(
                              backgroundColor: Colors.green,
                              foregroundColor: Colors.white,
                            ),
                            child: const Text('Démarrer'),
                          ),
                      ],
                    ),
                  ),
                  Expanded(
                    child: QRView(
                      key: qrKey,
                      onQRViewCreated: _onQRViewCreated,
                      overlay: QrScannerOverlayShape(
                        borderColor: isScanning ? Colors.green : Colors.grey,
                        borderRadius: 10,
                        borderLength: 30,
                        borderWidth: 10,
                        cutOutSize: 250,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),

          // Historique des présences
          Expanded(
            flex: 1,
            child: Container(
              margin: const EdgeInsets.fromLTRB(16, 0, 16, 16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Mes présences récentes',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Expanded(
                    child: isLoading
                        ? const Center(child: CircularProgressIndicator())
                        : errorMessage != null
                            ? Center(
                                child: Text(
                                  'Erreur: $errorMessage',
                                  style: const TextStyle(color: Colors.red),
                                ),
                              )
                            : mesPresences.isEmpty
                                ? const Center(
                                    child: Text('Aucune présence enregistrée'),
                                  )
                                : ListView.builder(
                                    itemCount: mesPresences.length,
                                    itemBuilder: (context, index) {
                                      final presence = mesPresences[index];
                                      return Card(
                                        child: ListTile(
                                          leading: Icon(
                                            presence['statut'] == 'present'
                                                ? Icons.check_circle
                                                : presence['statut'] == 'retard'
                                                    ? Icons.access_time
                                                    : Icons.cancel,
                                            color: presence['statut'] == 'present'
                                                ? Colors.green
                                                : presence['statut'] == 'retard'
                                                    ? Colors.orange
                                                    : Colors.red,
                                          ),
                                          title: Text(presence['nom_cours'] ?? 'Cours inconnu'),
                                          subtitle: Text(
                                            '${presence['date_presence']} - ${presence['type_seance'] ?? ''}',
                                          ),
                                          trailing: Container(
                                            padding: const EdgeInsets.symmetric(
                                              horizontal: 8,
                                              vertical: 4,
                                            ),
                                            decoration: BoxDecoration(
                                              color: presence['statut'] == 'present'
                                                  ? Colors.green
                                                  : presence['statut'] == 'retard'
                                                      ? Colors.orange
                                                      : Colors.red,
                                              borderRadius: BorderRadius.circular(12),
                                            ),
                                            child: Text(
                                              presence['statut']?.toUpperCase() ?? 'INCONNU',
                                              style: const TextStyle(
                                                color: Colors.white,
                                                fontSize: 12,
                                                fontWeight: FontWeight.bold,
                                              ),
                                            ),
                                          ),
                                        ),
                                      );
                                    },
                                  ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  void _showHistorique() {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (context) => HistoriquePresencesScreen(
          presences: mesPresences,
          session: widget.session,
        ),
      ),
    );
  }
}

class HistoriquePresencesScreen extends StatelessWidget {
  final List<Map<String, dynamic>> presences;
  final StudentSession session;

  const HistoriquePresencesScreen({
    Key? key,
    required this.presences,
    required this.session,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Historique des présences'),
        backgroundColor: const Color(0xFF1565C0),
        foregroundColor: Colors.white,
      ),
      body: presences.isEmpty
          ? const Center(
              child: Text('Aucune présence enregistrée'),
            )
          : ListView.builder(
              itemCount: presences.length,
              itemBuilder: (context, index) {
                final presence = presences[index];
                return Card(
                  margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                  child: ListTile(
                    leading: CircleAvatar(
                      backgroundColor: presence['statut'] == 'present'
                          ? Colors.green
                          : presence['statut'] == 'retard'
                              ? Colors.orange
                              : Colors.red,
                      child: Icon(
                        presence['statut'] == 'present'
                            ? Icons.check
                            : presence['statut'] == 'retard'
                                ? Icons.access_time
                                : Icons.close,
                        color: Colors.white,
                      ),
                    ),
                    title: Text(presence['nom_cours'] ?? 'Cours inconnu'),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Date: ${presence['date_presence']}'),
                        if (presence['heure_arrivee'] != null)
                          Text('Arrivée: ${presence['heure_arrivee']}'),
                        if (presence['type_seance'] != null)
                          Text('Type: ${presence['type_seance']}'),
                      ],
                    ),
                    trailing: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: presence['statut'] == 'present'
                            ? Colors.green
                            : presence['statut'] == 'retard'
                                ? Colors.orange
                                : Colors.red,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        presence['statut']?.toUpperCase() ?? 'INCONNU',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ),
                );
              },
            ),
    );
  }
}
