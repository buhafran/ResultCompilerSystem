# User Acceptance Test Checklist

Run this in staging with representative school staff before cutover.

## Tenant and role isolation

- [ ] Platform administrator can create two schools.
- [ ] School A administrator cannot see School B records.
- [ ] Teacher can sign in but cannot open user, student, template or release resources.
- [ ] Teacher A cannot load Teacher B’s assignment by changing a URL/API ID.
- [ ] Disabled membership loses access immediately.

## Setup

- [ ] School admin updates landing-page name, motto, address, contact, logo and next-term date.
- [ ] School admin creates a new teacher account directly from Users & Roles.
- [ ] Session, term, class, subject and class-subject mapping save correctly.
- [ ] Teacher assignment contains the intended term, class and subject.
- [ ] Student admission number is unique only inside the school.

## Mobile entry

- [ ] Teacher sees only assigned score sheets.
- [ ] CA accepts 0–30 and rejects 30.01.
- [ ] Examination accepts 0–70 and rejects 70.01.
- [ ] Absent student saves without converting to an ordinary F.
- [ ] Close the app offline after saving; reopen and confirm queued data remains.
- [ ] Reconnect and confirm queued changes synchronize.
- [ ] Edit the same student on two devices and confirm stale data is rejected.
- [ ] Locked term is read-only in the mobile app and rejects API changes.
- [ ] A two-device conflict preserves the unsynchronized local values for deliberate review and retry.

## Compilation

- [ ] Compilation stops when at least one student/subject is `not_entered`.
- [ ] Compilation automatically locks the term before taking the result snapshot.
- [ ] Totals equal CA + examination.
- [ ] Grade boundaries are A70, B60, C50, D45, E40 and F below 40 unless customized.
- [ ] Scores `100, 90, 90, 80` receive positions `1, 2, 2, 4`.
- [ ] Class-total ties receive the same competition ranking.
- [ ] Version 2 does not change version 1 snapshots.
- [ ] Audit entries exist for score save, compile and release.

## Comments, template and release

- [ ] Gemini draft contains no student identity in the outbound request/log.
- [ ] Deterministic fallback works with no Gemini key.
- [ ] Officer edits AI comments before release.
- [ ] Template colors/options affect preview and PDF.
- [ ] Compiled result is not visible in the student portal.
- [ ] Released result becomes visible immediately.
- [ ] Public verification token opens only a released result.
- [ ] PDF matches the browser result and prints on A4.

## Landing page, dashboard and bulk operations

- [ ] Slider can be enabled and disabled from School Profile.
- [ ] Only active slides appear; drag ordering, next/previous buttons and automatic rotation work.
- [ ] Dashboard counts match the current school and do not include another school’s records.
- [ ] Creating a new term persists it under the current school and activates only the selected term.
- [ ] An existing unassigned platform user appears when adding a school member.
- [ ] Platform administrator grants a user access to selected schools only.
- [ ] CSV template imports valid students and reports invalid rows without importing them.
- [ ] Re-importing an admission number updates the student instead of creating a duplicate.
- [ ] Student-list CSV downloads with the correct class and active status.
- [ ] Class broadsheet contains all students and subjects.
- [ ] Combined report-card PDF contains one report for every compiled student.
