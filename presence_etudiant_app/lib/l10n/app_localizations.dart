import 'dart:async';

import 'package:flutter/foundation.dart';
import 'package:flutter/widgets.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:intl/intl.dart' as intl;

import 'app_localizations_en.dart';
import 'app_localizations_fr.dart';

// ignore_for_file: type=lint

/// Callers can lookup localized strings with an instance of AppLocalizations
/// returned by `AppLocalizations.of(context)`.
///
/// Applications need to include `AppLocalizations.delegate()` in their app's
/// `localizationDelegates` list, and the locales they support in the app's
/// `supportedLocales` list. For example:
///
/// ```dart
/// import 'l10n/app_localizations.dart';
///
/// return MaterialApp(
///   localizationsDelegates: AppLocalizations.localizationsDelegates,
///   supportedLocales: AppLocalizations.supportedLocales,
///   home: MyApplicationHome(),
/// );
/// ```
///
/// ## Update pubspec.yaml
///
/// Please make sure to update your pubspec.yaml to include the following
/// packages:
///
/// ```yaml
/// dependencies:
///   # Internationalization support.
///   flutter_localizations:
///     sdk: flutter
///   intl: any # Use the pinned version from flutter_localizations
///
///   # Rest of dependencies
/// ```
///
/// ## iOS Applications
///
/// iOS applications define key application metadata, including supported
/// locales, in an Info.plist file that is built into the application bundle.
/// To configure the locales supported by your app, you’ll need to edit this
/// file.
///
/// First, open your project’s ios/Runner.xcworkspace Xcode workspace file.
/// Then, in the Project Navigator, open the Info.plist file under the Runner
/// project’s Runner folder.
///
/// Next, select the Information Property List item, select Add Item from the
/// Editor menu, then select Localizations from the pop-up menu.
///
/// Select and expand the newly-created Localizations item then, for each
/// locale your application supports, add a new item and select the locale
/// you wish to add from the pop-up menu in the Value field. This list should
/// be consistent with the languages listed in the AppLocalizations.supportedLocales
/// property.
abstract class AppLocalizations {
  AppLocalizations(String locale)
      : localeName = intl.Intl.canonicalizedLocale(locale.toString());

  final String localeName;

  static AppLocalizations? of(BuildContext context) {
    return Localizations.of<AppLocalizations>(context, AppLocalizations);
  }

  static const LocalizationsDelegate<AppLocalizations> delegate =
      _AppLocalizationsDelegate();

  /// A list of this localizations delegate along with the default localizations
  /// delegates.
  ///
  /// Returns a list of localizations delegates containing this delegate along with
  /// GlobalMaterialLocalizations.delegate, GlobalCupertinoLocalizations.delegate,
  /// and GlobalWidgetsLocalizations.delegate.
  ///
  /// Additional delegates can be added by appending to this list in
  /// MaterialApp. This list does not have to be used at all if a custom list
  /// of delegates is preferred or required.
  static const List<LocalizationsDelegate<dynamic>> localizationsDelegates =
      <LocalizationsDelegate<dynamic>>[
    delegate,
    GlobalMaterialLocalizations.delegate,
    GlobalCupertinoLocalizations.delegate,
    GlobalWidgetsLocalizations.delegate,
  ];

  /// A list of this localizations delegate's supported locales.
  static const List<Locale> supportedLocales = <Locale>[
    Locale('en'),
    Locale('fr')
  ];

  /// No description provided for @appTitle.
  ///
  /// In en, this message translates to:
  /// **'Student attendance'**
  String get appTitle;

  /// No description provided for @navPresence.
  ///
  /// In en, this message translates to:
  /// **'Attendance'**
  String get navPresence;

  /// No description provided for @actionLogout.
  ///
  /// In en, this message translates to:
  /// **'Log out'**
  String get actionLogout;

  /// No description provided for @actionLogin.
  ///
  /// In en, this message translates to:
  /// **'Log in'**
  String get actionLogin;

  /// No description provided for @actionConnect.
  ///
  /// In en, this message translates to:
  /// **'Sign in'**
  String get actionConnect;

  /// No description provided for @studentLoginTitle.
  ///
  /// In en, this message translates to:
  /// **'Student login'**
  String get studentLoginTitle;

  /// No description provided for @sameWifiHint.
  ///
  /// In en, this message translates to:
  /// **'Same Wi‑Fi network as the PC (WAMP).'**
  String get sameWifiHint;

  /// No description provided for @serverLabel.
  ///
  /// In en, this message translates to:
  /// **'Server'**
  String get serverLabel;

  /// No description provided for @serverNotConfiguredSnackbar.
  ///
  /// In en, this message translates to:
  /// **'Server not configured. Scan a QR code to set it up.'**
  String get serverNotConfiguredSnackbar;

  /// No description provided for @configureServer.
  ///
  /// In en, this message translates to:
  /// **'Set up'**
  String get configureServer;

  /// No description provided for @changeServer.
  ///
  /// In en, this message translates to:
  /// **'Change'**
  String get changeServer;

  /// No description provided for @notConfigured.
  ///
  /// In en, this message translates to:
  /// **'Not configured'**
  String get notConfigured;

  /// No description provided for @emailLabel.
  ///
  /// In en, this message translates to:
  /// **'Email'**
  String get emailLabel;

  /// No description provided for @passwordLabel.
  ///
  /// In en, this message translates to:
  /// **'Password'**
  String get passwordLabel;

  /// No description provided for @emailRequired.
  ///
  /// In en, this message translates to:
  /// **'Email is required.'**
  String get emailRequired;

  /// No description provided for @emailInvalid.
  ///
  /// In en, this message translates to:
  /// **'Invalid email.'**
  String get emailInvalid;

  /// No description provided for @passwordRequired.
  ///
  /// In en, this message translates to:
  /// **'Password is required.'**
  String get passwordRequired;

  /// No description provided for @unexpectedError.
  ///
  /// In en, this message translates to:
  /// **'Unexpected error.'**
  String get unexpectedError;

  /// No description provided for @scanQrButton.
  ///
  /// In en, this message translates to:
  /// **'Scan QR code'**
  String get scanQrButton;

  /// No description provided for @scanQrTitle.
  ///
  /// In en, this message translates to:
  /// **'Scan QR'**
  String get scanQrTitle;

  /// No description provided for @torchTooltip.
  ///
  /// In en, this message translates to:
  /// **'Flashlight'**
  String get torchTooltip;

  /// No description provided for @afterScanBiometricHint.
  ///
  /// In en, this message translates to:
  /// **'After scanning, confirm with fingerprint, face recognition, or your phone PIN.'**
  String get afterScanBiometricHint;

  /// No description provided for @myPresencesJustifications.
  ///
  /// In en, this message translates to:
  /// **'My attendance & justifications'**
  String get myPresencesJustifications;

  /// No description provided for @myProfile.
  ///
  /// In en, this message translates to:
  /// **'My profile'**
  String get myProfile;

  /// No description provided for @myCourses.
  ///
  /// In en, this message translates to:
  /// **'My courses'**
  String get myCourses;

  /// No description provided for @disconnect.
  ///
  /// In en, this message translates to:
  /// **'Sign out'**
  String get disconnect;

  /// No description provided for @matriculeWithValue.
  ///
  /// In en, this message translates to:
  /// **'Student ID: {id}'**
  String matriculeWithValue(String id);

  /// No description provided for @emailWithLabel.
  ///
  /// In en, this message translates to:
  /// **'Email: {email}'**
  String emailWithLabel(String email);

  /// No description provided for @biometricCancelledSnackbar.
  ///
  /// In en, this message translates to:
  /// **'Attendance cancelled: authentication required.'**
  String get biometricCancelledSnackbar;

  /// No description provided for @biometricUnavailableSnackbar.
  ///
  /// In en, this message translates to:
  /// **'Biometrics unavailable on this device — add a fingerprint or face in Settings for better security.'**
  String get biometricUnavailableSnackbar;

  /// No description provided for @biometricOrSendInProgress.
  ///
  /// In en, this message translates to:
  /// **'Biometric authentication or sending…'**
  String get biometricOrSendInProgress;

  /// No description provided for @frameQrHint.
  ///
  /// In en, this message translates to:
  /// **'Frame the QR code shown on the teacher’s computer.'**
  String get frameQrHint;

  /// No description provided for @configureServerTitle.
  ///
  /// In en, this message translates to:
  /// **'Configure server'**
  String get configureServerTitle;

  /// No description provided for @configuringServer.
  ///
  /// In en, this message translates to:
  /// **'Configuring…'**
  String get configuringServer;

  /// No description provided for @scanQrForServerHint.
  ///
  /// In en, this message translates to:
  /// **'Scan a presence QR shown by the teacher.\nThe app will detect the server URL automatically.'**
  String get scanQrForServerHint;

  /// No description provided for @invalidQrSnackbar.
  ///
  /// In en, this message translates to:
  /// **'Invalid QR.'**
  String get invalidQrSnackbar;

  /// No description provided for @biometricConfirmReason.
  ///
  /// In en, this message translates to:
  /// **'Confirm your identity to record attendance (fingerprint or face).'**
  String get biometricConfirmReason;

  /// No description provided for @retry.
  ///
  /// In en, this message translates to:
  /// **'Retry'**
  String get retry;

  /// No description provided for @noCoursesFound.
  ///
  /// In en, this message translates to:
  /// **'No courses found.'**
  String get noCoursesFound;

  /// No description provided for @noPresencesYet.
  ///
  /// In en, this message translates to:
  /// **'No attendance recorded yet.'**
  String get noPresencesYet;

  /// No description provided for @mesPresencesTitle.
  ///
  /// In en, this message translates to:
  /// **'My attendance & absences'**
  String get mesPresencesTitle;

  /// No description provided for @refreshTooltip.
  ///
  /// In en, this message translates to:
  /// **'Refresh'**
  String get refreshTooltip;

  /// No description provided for @justifyAbsenceTitle.
  ///
  /// In en, this message translates to:
  /// **'Justify an absence'**
  String get justifyAbsenceTitle;

  /// No description provided for @dateLine.
  ///
  /// In en, this message translates to:
  /// **'Date: {date}'**
  String dateLine(String date);

  /// No description provided for @motifLabel.
  ///
  /// In en, this message translates to:
  /// **'Reason'**
  String get motifLabel;

  /// No description provided for @fileTooLarge.
  ///
  /// In en, this message translates to:
  /// **'File too large (max 5 MB).'**
  String get fileTooLarge;

  /// No description provided for @cannotReadFile.
  ///
  /// In en, this message translates to:
  /// **'Could not read this file.'**
  String get cannotReadFile;

  /// No description provided for @removeAttachment.
  ///
  /// In en, this message translates to:
  /// **'Remove attachment'**
  String get removeAttachment;

  /// No description provided for @cancel.
  ///
  /// In en, this message translates to:
  /// **'Cancel'**
  String get cancel;

  /// No description provided for @send.
  ///
  /// In en, this message translates to:
  /// **'Send'**
  String get send;

  /// No description provided for @enterReason.
  ///
  /// In en, this message translates to:
  /// **'Enter a reason.'**
  String get enterReason;

  /// No description provided for @justificationPending.
  ///
  /// In en, this message translates to:
  /// **'Justification pending'**
  String get justificationPending;

  /// No description provided for @justificationAccepted.
  ///
  /// In en, this message translates to:
  /// **'Justification accepted'**
  String get justificationAccepted;

  /// No description provided for @justificationRejected.
  ///
  /// In en, this message translates to:
  /// **'Justification rejected'**
  String get justificationRejected;

  /// No description provided for @justified.
  ///
  /// In en, this message translates to:
  /// **'Excused'**
  String get justified;

  /// No description provided for @present.
  ///
  /// In en, this message translates to:
  /// **'Present'**
  String get present;

  /// No description provided for @absent.
  ///
  /// In en, this message translates to:
  /// **'Absent'**
  String get absent;

  /// No description provided for @yourMessage.
  ///
  /// In en, this message translates to:
  /// **'Your message: {text}'**
  String yourMessage(String text);

  /// No description provided for @staffReply.
  ///
  /// In en, this message translates to:
  /// **'Reply: {text}'**
  String staffReply(String text);

  /// No description provided for @sendJustification.
  ///
  /// In en, this message translates to:
  /// **'Submit a justification'**
  String get sendJustification;

  /// No description provided for @messaging.
  ///
  /// In en, this message translates to:
  /// **'Messages'**
  String get messaging;

  /// No description provided for @attachFileButton.
  ///
  /// In en, this message translates to:
  /// **'Add attachment (PDF/JPG/PNG)'**
  String get attachFileButton;

  /// No description provided for @attachmentNamed.
  ///
  /// In en, this message translates to:
  /// **'Attachment: {name}'**
  String attachmentNamed(String name);

  /// No description provided for @profileTitle.
  ///
  /// In en, this message translates to:
  /// **'My profile'**
  String get profileTitle;

  /// No description provided for @firstName.
  ///
  /// In en, this message translates to:
  /// **'First name'**
  String get firstName;

  /// No description provided for @lastName.
  ///
  /// In en, this message translates to:
  /// **'Last name'**
  String get lastName;

  /// No description provided for @save.
  ///
  /// In en, this message translates to:
  /// **'Save'**
  String get save;

  /// No description provided for @passwordChangeHint.
  ///
  /// In en, this message translates to:
  /// **'Password changes on the website (if enabled).'**
  String get passwordChangeHint;

  /// No description provided for @errorGeneric.
  ///
  /// In en, this message translates to:
  /// **'Error.'**
  String get errorGeneric;

  /// No description provided for @languageTitle.
  ///
  /// In en, this message translates to:
  /// **'Language'**
  String get languageTitle;

  /// No description provided for @languageFrench.
  ///
  /// In en, this message translates to:
  /// **'French'**
  String get languageFrench;

  /// No description provided for @languageEnglish.
  ///
  /// In en, this message translates to:
  /// **'English'**
  String get languageEnglish;

  /// No description provided for @languageSystem.
  ///
  /// In en, this message translates to:
  /// **'System default'**
  String get languageSystem;

  /// No description provided for @teacherLabel.
  ///
  /// In en, this message translates to:
  /// **'Teacher: {name}'**
  String teacherLabel(String name);

  /// No description provided for @authCancelledNoPresence.
  ///
  /// In en, this message translates to:
  /// **'Authentication cancelled. Attendance was not recorded.'**
  String get authCancelledNoPresence;

  /// No description provided for @presenceMarkedSuccess.
  ///
  /// In en, this message translates to:
  /// **'Attendance recorded successfully!'**
  String get presenceMarkedSuccess;

  /// No description provided for @presenceMarkFailed.
  ///
  /// In en, this message translates to:
  /// **'Could not record attendance.'**
  String get presenceMarkFailed;

  /// No description provided for @profileClassLabel.
  ///
  /// In en, this message translates to:
  /// **'Program (level · major)'**
  String get profileClassLabel;

  /// No description provided for @profileClassNotSet.
  ///
  /// In en, this message translates to:
  /// **'Not assigned'**
  String get profileClassNotSet;
}

class _AppLocalizationsDelegate
    extends LocalizationsDelegate<AppLocalizations> {
  const _AppLocalizationsDelegate();

  @override
  Future<AppLocalizations> load(Locale locale) {
    return SynchronousFuture<AppLocalizations>(lookupAppLocalizations(locale));
  }

  @override
  bool isSupported(Locale locale) =>
      <String>['en', 'fr'].contains(locale.languageCode);

  @override
  bool shouldReload(_AppLocalizationsDelegate old) => false;
}

AppLocalizations lookupAppLocalizations(Locale locale) {
  // Lookup logic when only language code is specified.
  switch (locale.languageCode) {
    case 'en':
      return AppLocalizationsEn();
    case 'fr':
      return AppLocalizationsFr();
  }

  throw FlutterError(
      'AppLocalizations.delegate failed to load unsupported locale "$locale". This is likely '
      'an issue with the localizations generation tool. Please file an issue '
      'on GitHub with a reproducible sample app and the gen-l10n configuration '
      'that was used.');
}
