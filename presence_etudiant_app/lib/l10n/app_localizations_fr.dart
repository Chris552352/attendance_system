// ignore: unused_import
import 'package:intl/intl.dart' as intl;
import 'app_localizations.dart';

// ignore_for_file: type=lint

/// The translations for French (`fr`).
class AppLocalizationsFr extends AppLocalizations {
  AppLocalizationsFr([String locale = 'fr']) : super(locale);

  @override
  String get appTitle => 'Présence étudiant';

  @override
  String get navPresence => 'Présence';

  @override
  String get actionLogout => 'Déconnexion';

  @override
  String get actionLogin => 'Connexion';

  @override
  String get actionConnect => 'Se connecter';

  @override
  String get studentLoginTitle => 'Connexion étudiant';

  @override
  String get sameWifiHint => 'Même réseau Wi‑Fi que le PC (WAMP).';

  @override
  String get serverLabel => 'Serveur';

  @override
  String get serverNotConfiguredSnackbar =>
      'Serveur non configuré. Scannez un QR pour configurer.';

  @override
  String get configureServer => 'Configurer';

  @override
  String get changeServer => 'Changer';

  @override
  String get notConfigured => 'Non configuré';

  @override
  String get emailLabel => 'Email';

  @override
  String get passwordLabel => 'Mot de passe';

  @override
  String get emailRequired => 'Email requis.';

  @override
  String get emailInvalid => 'Email invalide.';

  @override
  String get passwordRequired => 'Mot de passe requis.';

  @override
  String get unexpectedError => 'Erreur inattendue.';

  @override
  String get scanQrButton => 'Scanner le code QR';

  @override
  String get scanQrTitle => 'Scanner le QR';

  @override
  String get torchTooltip => 'Torche';

  @override
  String get afterScanBiometricHint =>
      'Après le scan, vous devrez confirmer avec l’empreinte digitale, la reconnaissance faciale ou le code de votre téléphone.';

  @override
  String get myPresencesJustifications => 'Mes présences & justifications';

  @override
  String get myProfile => 'Mon profil';

  @override
  String get myCourses => 'Mes cours';

  @override
  String get disconnect => 'Se déconnecter';

  @override
  String matriculeWithValue(String id) {
    return 'Matricule : $id';
  }

  @override
  String emailWithLabel(String email) {
    return 'Email : $email';
  }

  @override
  String get biometricCancelledSnackbar =>
      'Pointage annulé : authentification requise.';

  @override
  String get biometricUnavailableSnackbar =>
      'Biométrie indisponible sur cet appareil — configurez une empreinte ou un visage dans les paramètres pour plus de sécurité.';

  @override
  String get biometricOrSendInProgress =>
      'Authentification biométrique ou envoi en cours…';

  @override
  String get frameQrHint =>
      'Cadrez le QR affiché sur l’ordinateur de l’enseignant.';

  @override
  String get configureServerTitle => 'Configurer le serveur';

  @override
  String get configuringServer => 'Configuration…';

  @override
  String get scanQrForServerHint =>
      'Scannez un QR de présence affiché par l’enseignant.\nL’app déduira automatiquement l’URL du serveur.';

  @override
  String get invalidQrSnackbar => 'QR invalide.';

  @override
  String get biometricConfirmReason =>
      'Confirmez votre identité pour enregistrer votre présence (empreinte ou visage).';

  @override
  String get retry => 'Réessayer';

  @override
  String get noCoursesFound => 'Aucun cours trouvé.';

  @override
  String get noPresencesYet => 'Aucune présence enregistrée pour le moment.';

  @override
  String get mesPresencesTitle => 'Mes présences & absences';

  @override
  String get refreshTooltip => 'Actualiser';

  @override
  String get justifyAbsenceTitle => 'Justifier une absence';

  @override
  String dateLine(String date) {
    return 'Date : $date';
  }

  @override
  String get motifLabel => 'Motif';

  @override
  String get fileTooLarge => 'Fichier trop volumineux (max 5 Mo).';

  @override
  String get cannotReadFile => 'Impossible de lire ce fichier.';

  @override
  String get removeAttachment => 'Retirer la pièce jointe';

  @override
  String get cancel => 'Annuler';

  @override
  String get send => 'Envoyer';

  @override
  String get enterReason => 'Saisissez un motif.';

  @override
  String get justificationPending => 'Justification en attente';

  @override
  String get justificationAccepted => 'Justification acceptée';

  @override
  String get justificationRejected => 'Justification refusée';

  @override
  String get justified => 'Justifiée';

  @override
  String get present => 'Présent';

  @override
  String get absent => 'Absent';

  @override
  String yourMessage(String text) {
    return 'Votre message : $text';
  }

  @override
  String staffReply(String text) {
    return 'Réponse : $text';
  }

  @override
  String get sendJustification => 'Envoyer une justification';

  @override
  String get messaging => 'Messagerie';

  @override
  String get attachFileButton => 'Ajouter une pièce jointe (PDF/JPG/PNG)';

  @override
  String attachmentNamed(String name) {
    return 'Pièce jointe : $name';
  }

  @override
  String get profileTitle => 'Mon profil';

  @override
  String get firstName => 'Prénom';

  @override
  String get lastName => 'Nom';

  @override
  String get save => 'Enregistrer';

  @override
  String get passwordChangeHint =>
      'Le mot de passe se modifie côté web (si activé).';

  @override
  String get errorGeneric => 'Erreur.';

  @override
  String get languageTitle => 'Langue';

  @override
  String get languageFrench => 'Français';

  @override
  String get languageEnglish => 'Anglais';

  @override
  String get languageSystem => 'Langue du système';

  @override
  String teacherLabel(String name) {
    return 'Enseignant : $name';
  }

  @override
  String get authCancelledNoPresence =>
      'Authentification annulée. La présence n’a pas été enregistrée.';

  @override
  String get presenceMarkedSuccess => 'Présence marquée avec succès !';

  @override
  String get presenceMarkFailed => 'Erreur lors du marquage de présence.';

  @override
  String get profileClassLabel => 'Promotion (niveau · filière)';

  @override
  String get profileClassNotSet => 'Non renseigné';
}
