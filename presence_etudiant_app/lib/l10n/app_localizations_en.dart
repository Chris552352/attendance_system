// ignore: unused_import
import 'package:intl/intl.dart' as intl;
import 'app_localizations.dart';

// ignore_for_file: type=lint

/// The translations for English (`en`).
class AppLocalizationsEn extends AppLocalizations {
  AppLocalizationsEn([String locale = 'en']) : super(locale);

  @override
  String get appTitle => 'Student attendance';

  @override
  String get navPresence => 'Attendance';

  @override
  String get actionLogout => 'Log out';

  @override
  String get actionLogin => 'Log in';

  @override
  String get actionConnect => 'Sign in';

  @override
  String get studentLoginTitle => 'Student login';

  @override
  String get sameWifiHint => 'Same Wi‑Fi network as the PC (WAMP).';

  @override
  String get serverLabel => 'Server';

  @override
  String get serverNotConfiguredSnackbar =>
      'Server not configured. Scan a QR code to set it up.';

  @override
  String get configureServer => 'Set up';

  @override
  String get changeServer => 'Change';

  @override
  String get notConfigured => 'Not configured';

  @override
  String get emailLabel => 'Email';

  @override
  String get passwordLabel => 'Password';

  @override
  String get emailRequired => 'Email is required.';

  @override
  String get emailInvalid => 'Invalid email.';

  @override
  String get passwordRequired => 'Password is required.';

  @override
  String get unexpectedError => 'Unexpected error.';

  @override
  String get scanQrButton => 'Scan QR code';

  @override
  String get scanQrTitle => 'Scan QR';

  @override
  String get torchTooltip => 'Flashlight';

  @override
  String get afterScanBiometricHint =>
      'After scanning, confirm with fingerprint, face recognition, or your phone PIN.';

  @override
  String get myPresencesJustifications => 'My attendance & justifications';

  @override
  String get myProfile => 'My profile';

  @override
  String get myCourses => 'My courses';

  @override
  String get disconnect => 'Sign out';

  @override
  String matriculeWithValue(String id) {
    return 'Student ID: $id';
  }

  @override
  String emailWithLabel(String email) {
    return 'Email: $email';
  }

  @override
  String get biometricCancelledSnackbar =>
      'Attendance cancelled: authentication required.';

  @override
  String get biometricUnavailableSnackbar =>
      'Biometrics unavailable on this device — add a fingerprint or face in Settings for better security.';

  @override
  String get biometricOrSendInProgress =>
      'Biometric authentication or sending…';

  @override
  String get frameQrHint =>
      'Frame the QR code shown on the teacher’s computer.';

  @override
  String get configureServerTitle => 'Configure server';

  @override
  String get configuringServer => 'Configuring…';

  @override
  String get scanQrForServerHint =>
      'Scan a presence QR shown by the teacher.\nThe app will detect the server URL automatically.';

  @override
  String get invalidQrSnackbar => 'Invalid QR.';

  @override
  String get biometricConfirmReason =>
      'Confirm your identity to record attendance (fingerprint or face).';

  @override
  String get retry => 'Retry';

  @override
  String get noCoursesFound => 'No courses found.';

  @override
  String get noPresencesYet => 'No attendance recorded yet.';

  @override
  String get mesPresencesTitle => 'My attendance & absences';

  @override
  String get refreshTooltip => 'Refresh';

  @override
  String get justifyAbsenceTitle => 'Justify an absence';

  @override
  String dateLine(String date) {
    return 'Date: $date';
  }

  @override
  String get motifLabel => 'Reason';

  @override
  String get fileTooLarge => 'File too large (max 5 MB).';

  @override
  String get cannotReadFile => 'Could not read this file.';

  @override
  String get removeAttachment => 'Remove attachment';

  @override
  String get cancel => 'Cancel';

  @override
  String get send => 'Send';

  @override
  String get enterReason => 'Enter a reason.';

  @override
  String get justificationPending => 'Justification pending';

  @override
  String get justificationAccepted => 'Justification accepted';

  @override
  String get justificationRejected => 'Justification rejected';

  @override
  String get justified => 'Excused';

  @override
  String get present => 'Present';

  @override
  String get absent => 'Absent';

  @override
  String yourMessage(String text) {
    return 'Your message: $text';
  }

  @override
  String staffReply(String text) {
    return 'Reply: $text';
  }

  @override
  String get sendJustification => 'Submit a justification';

  @override
  String get messaging => 'Messages';

  @override
  String get attachFileButton => 'Add attachment (PDF/JPG/PNG)';

  @override
  String attachmentNamed(String name) {
    return 'Attachment: $name';
  }

  @override
  String get profileTitle => 'My profile';

  @override
  String get firstName => 'First name';

  @override
  String get lastName => 'Last name';

  @override
  String get save => 'Save';

  @override
  String get passwordChangeHint =>
      'Password changes on the website (if enabled).';

  @override
  String get errorGeneric => 'Error.';

  @override
  String get languageTitle => 'Language';

  @override
  String get languageFrench => 'French';

  @override
  String get languageEnglish => 'English';

  @override
  String get languageSystem => 'System default';

  @override
  String teacherLabel(String name) {
    return 'Teacher: $name';
  }

  @override
  String get authCancelledNoPresence =>
      'Authentication cancelled. Attendance was not recorded.';

  @override
  String get presenceMarkedSuccess => 'Attendance recorded successfully!';

  @override
  String get presenceMarkFailed => 'Could not record attendance.';

  @override
  String get profileClassLabel => 'Program (level · major)';

  @override
  String get profileClassNotSet => 'Not assigned';
}
