import 'package:flutter/material.dart';

import 'package:presence_etudiant_app/l10n/app_localizations.dart';
import 'package:presence_etudiant_app/models/course_row.dart';
import 'package:presence_etudiant_app/models/student_session.dart';
import 'package:presence_etudiant_app/services/student_profile_service.dart';

class MesCoursScreen extends StatefulWidget {
  const MesCoursScreen({super.key, required this.session});

  final StudentSession session;

  @override
  State<MesCoursScreen> createState() => _MesCoursScreenState();
}

class _MesCoursScreenState extends State<MesCoursScreen> {
  final _svc = StudentProfileService();
  Future<List<CourseRow>>? _future;

  @override
  void initState() {
    super.initState();
    _future = _svc.fetchCourses(widget.session);
  }

  @override
  void dispose() {
    _svc.dispose();
    super.dispose();
  }

  void _reload() {
    final f = _svc.fetchCourses(widget.session);
    setState(() => _future = f);
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context)!;

    return Scaffold(
      appBar: AppBar(
        title: Text(l10n.myCourses),
        actions: [
          IconButton(onPressed: _reload, icon: const Icon(Icons.refresh)),
        ],
      ),
      body: FutureBuilder<List<CourseRow>>(
        future: _future,
        builder: (context, snap) {
          if (snap.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snap.hasError) {
            final err = snap.error;
            final msg = err is ProfileException ? err.message : err.toString();
            return Center(
              child: Padding(
                padding: const EdgeInsets.all(24),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(msg, textAlign: TextAlign.center),
                    const SizedBox(height: 16),
                    FilledButton(onPressed: _reload, child: Text(l10n.retry)),
                  ],
                ),
              ),
            );
          }
          final list = snap.data ?? [];
          if (list.isEmpty) {
            return Center(
              child: Text(
                l10n.noCoursesFound,
                style: TextStyle(color: Colors.grey.shade700),
              ),
            );
          }
          return ListView.separated(
            padding: const EdgeInsets.all(16),
            itemCount: list.length,
            separatorBuilder: (_, __) => const SizedBox(height: 8),
            itemBuilder: (context, i) {
              final c = list[i];
              return Card(
                child: Padding(
                  padding: const EdgeInsets.all(12),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(c.label, style: const TextStyle(fontWeight: FontWeight.w600)),
                      if (c.enseignant.isNotEmpty) ...[
                        const SizedBox(height: 6),
                        Text(
                          l10n.teacherLabel(c.enseignant),
                          style: TextStyle(color: Colors.grey.shade800),
                        ),
                      ],
                      if ((c.description ?? '').trim().isNotEmpty) ...[
                        const SizedBox(height: 8),
                        Text(c.description!.trim()),
                      ],
                    ],
                  ),
                ),
              );
            },
          );
        },
      ),
    );
  }
}
