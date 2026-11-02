# Forbes Grading System — Academic Records & Usernames V3

**Target baseline:** ForbesGradingSystem-main 3 with V2 patch installed and working. **This is NOT a replacement project.**

## Included

- User Management and Admin Management display a stable username for each normal account; login accepts institutional email or username. Existing user rows are assigned `student_ID`, `teacher_ID`, or `admin_ID` usernames from their unique database IDs. New accounts receive the same form. Existing passwords are NOT changed.
- Student, teacher, and administrator registration/import services already create user accounts. Added an explicit dry-run `php artisan forbes:reconcile-accounts` for legacy student/teacher rows without `user_id`; use `--apply` only after reviewing backups. New account passwords remain securely random, and admins must set valid passwords before users can sign in.
- Student Management name opens full profile with saved subject/class/teacher/schedule/quarterly grades and review notes. The original quick-view eye modal remains intact.
- Teacher Management name opens full profile with subject assignments, class adviser roles, and expandable student rosters. The original quick-view eye modal remains.
- Subject Management is backed by `subjects` and `subject_offerings` tables rather than demo rows. Create, edit/disable subjects; assign a grade level/year/section, teacher, room and optional day/time schedule; view matching student rosters. The existing teacher `subject` text field is NOT silently treated as an assignment.
- Grade Level lists actual grade/year/section groups and can assign class advisers; no guesses are made for blank student sections.
- Grades are saved in `grade_records` by student, assigned subject offering, and quarter; drafts can be submitted; pending records can be approved or returned in filtered batches by administrators or eligible class advisers (another teacher's records only). Approved entries cannot be edited directly, and are visible on the student dashboard. Teacher grade access is limited to explicitly assigned offerings.
- Teacher Dashboard uses real assignments/rosters and grade status totals; Student Dashboard lists only published approved grades. Legacy hard-coded sample metrics/grade rows in those pages have been replaced with live data or empty states.
- Dashboard pending, approved and returned totals use database records; its subject count uses the new real Subject model. Student Management gains a status filter. Teacher subject filter now lists saved teacher subject labels.
- Teacher/student shared navigation now uses role-appropriate links and a real POST logout. Existing MySQL student records, uploaded CSVs, .env, historical activity logging and the original app structure are preserved.

## Architecture and safeguards

- `2026_10_26_000002_add_account_usernames.php` — **additive** nullable unique `users.username` column and deterministic backfill. Existing email/password/name/role stay unchanged.
- `2026_10_26_000003_create_academic_records.php` — **new** tables: `subjects`, `subject_offerings`, `class_advisers`, `grade_records`; foreign keys and uniqueness constraints.
- Real existing student/teacher data stays in place. No automatic creation of subjects, grades, schedules, class advisers, or enrolment changes from incomplete 2025–2026 PDFs or CSV files.
- Class/student association requires exact stored year and grade, plus exact section or an explicitly chosen `ALL` class; all-grade `ALL` assignments are intentional and should be used sparingly.
- A teacher can save/submit grades for their assigned class. Administrators can approve any pending grade. A class adviser can approve/return only another teacher's grade entries for students in an explicitly assigned advisory grade/section/year. A teacher may **never approve their own submissions or grades for classes they teach**. Admins assign class advisers from Grade Level Management. This enforces basic separation of duties.
- Scheduling guards against overlapping class or teacher assignments when BOTH days and time ranges are supplied, not against unspecified schedules.
- Previous system-owner login and audit visibility logic is **not changed**. Its hardcoded privileged login remains a critical production security issue requiring a separate controlled migration. Do not deploy to a live school without fixing it and completing security review.

## Safe installation

1. **BACK UP** your complete working Laravel project, CSV files, and MySQL `forbes_grading_staging` database using MAMP/phpMyAdmin. Protect your previous V2 working folder.
2. Download both `Forbes_Grading_System_Academic_V3_PATCH.zip` and `forbes_apply_patch_v3.py` into `~/Downloads`.
3. Stop `php artisan serve` in VS Code (Ctrl+C). Run: `python3 "$HOME/Downloads/forbes_apply_patch_v3.py"`.
4. The installer compares the **SHA-256** of every existing file against the expected V2 baseline and refuses to overwrite diverged/local-edit files. It verifies ZIP hash and paths, never deletes folders, excludes `.env` and runtime/database storage, backs up overwritten files and prompts `APPLY`.
5. In your Laravel project root, run in sequence: `php artisan config:clear`; `php artisan db:show`; `php artisan migrate:status`. **Verify the database is the isolated `forbes_grading_staging`**. Then execute `php artisan migrate`. Never use `migrate:fresh`.
6. Check `php artisan route:list` then `php artisan serve`. Test User Management usernames, login via username/email, student/teacher account status, subject/year/section assignments, rosters, teacher draft grades, submissions, admin approval/return, student approved-only grades, and activity-log entries.
7. Optionally run `php artisan forbes:reconcile-accounts` (no `--apply`) to see whether any old student/teacher records lack login accounts. Back up and investigate mismatches before applying. An auto-created account does not have a known initial password: use **Reset Password** in User Management with a strong password and distribute credentials securely.

## Verification limits

- Static PHP syntax, Blade compilation + generated PHP syntax, JavaScript parse, route-list generation and archive/installer smoke checks were run successfully.
- Laravel PHPUnit/database integration tests were authored but **not executed**: the tool container lacks `pdo_sqlite`, `dom`, `mbstring`, and `xmlwriter`. Apply and test in an isolated local MySQL staging environment.
- Do not count imported CSV sample values (including random gender and temporary IDs) as verified real student data. Correct them from official sources before production.
- This does not implement a complete curriculum, final-average/weighted grade algorithm, online password delivery, class enrolment administration for missing sections, timetable calendar, or unscoped teacher-reviewer approval. No such formulas/policies were provided; do not invent them.
