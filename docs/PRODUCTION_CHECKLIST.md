# Production Readiness Checklist

- [ ] Replace all example email addresses, domains, package IDs and passwords.
- [ ] Set `APP_ENV=production`, `APP_DEBUG=false`, a unique `APP_KEY`, and HTTPS `APP_URL`.
- [ ] Restrict `/platform` to trusted administrators; enable MFA at the identity/network layer if available.
- [ ] Confirm every school administrator has only the intended memberships.
- [ ] Configure mail for password reset and operational notifications.
- [ ] Configure Gemini only after privacy approval; otherwise leave it disabled.
- [ ] Test student CSV import with a staging copy and reconcile counts before production entry.
- [ ] Issue new portal PINs after replacing temporary admission numbers.
- [ ] Complete the UAT checklist with at least one administrator, examination officer, teacher, student and parent representative.
- [ ] Configure TLS, firewall, backups, monitoring, log rotation and alerting.
- [ ] Confirm storage permissions and maximum upload size.
- [ ] Test Android app on low-end devices and unreliable Nigerian mobile networks.
- [ ] Record a rollback plan for application image and database migration.
