import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import 'package:presence_etudiant_app/main.dart';

void main() {
  testWidgets('L’app démarre (écran de chargement ou login)', (tester) async {
    await tester.pumpWidget(const PresenceEtudiantApp());
    await tester.pump();
    expect(find.byType(MaterialApp), findsOneWidget);
  });
}
