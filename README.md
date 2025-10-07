# Laravel Central Auth Provider (CAP)

A robust, single place to authenticate all your internal company portals. Built with **Laravel + Livewire** and **MySQL**, offering employee-ID login, optional MFA, social account linking (Google & Microsoft Entra ID), centralized RBAC, searchable audit logs, and an OAuth2/OIDC SSO flow so users sign in once and access every portal.

---

## ✨ Features (mapped to your requirements)

* **Employee ID + Password login** (not email-first)
* **Optional MFA** (TOTP: Google Authenticator/Authy compatible) per-user or enforced by policy
* **Social account linking:** Google + Microsoft Entra ID (personal/work)
* **Controlled signup:** Users can self-activate **only** if their Employee ID exists from admin import
* **User profile model:** roles, departments, user types; flexible type→portal mapping
* **Centralized Access & Permissions UI:** assign roles, per-portal scopes, and fine-grained permissions
* **Auditing:** comprehensive, searchable audit trail for logins, user updates, and permission changes
* **SSO:** OAuth2/OIDC Authorization Server via Laravel Passport (Auth Code + PKCE) for all portals
* **Admin UX:** Livewire components for users, roles/permissions, portals, audits, and security policies

---

## 🏗️ Architecture

```
+-------------------------+         +--------------------+
|  Client Portal A (SPA)  | <-----> |                    |
|  Client Portal B (Admin)| <-----> |   CAP OAuth2/OIDC  |
|  Client Portal C (API)  | <-----> |  (Laravel Passport)|
+-------------------------+         |                    |
                                    |  RBAC (Spatie)     |
                                    |  Livewire Console  |
                                    |  MFA (TOTP)        |
                                    |  Socialite (G/MS)  |
                                    |  Audit Log         |
                                    +---------+----------+
                                              |
                                              v
                                           MySQL
```

* **Auth Server:** Laravel Passport acts as OAuth2 Authorization Server with OIDC claims.
* **RBAC:** `spatie/laravel-permission` for roles & permissions; portal-scoped abilities via custom policies.
* **Auditing:** granular events (login, logout, 2FA, profile changes, role grants/revokes, client registrations).
* **Social Login:** `laravel/socialite` + Microsoft provider; accounts are **linked** to the employee identity.
* **MFA:** TOTP secrets, recovery codes, device remember, policy toggles.

---

## 📦 Tech Stack

* **Backend:** Laravel 11+, PHP 8.3
* **UI:** Livewire 3, Blade, TailwindCSS
* **DB:** MySQL 8.x
* **Auth:** Laravel Passport (OAuth2/OIDC), Laravel Socialite
* **RBAC:** spatie/laravel-permission
* **MFA:** pragmarx/google2fa or equivalent TOTP lib
* **Audit:** custom table + events (or `owen-it/laravel-auditing`)
* **Queues/Jobs:** Redis (recommended) for email/notifications

---

## 📁 Project Structure (high level)

```
app/
  Domain/
    Access/ (Roles, Permissions, Policies, PortalScopes)
    Auth/   (MFA, Social Linking, Login pipelines)
    Audit/  (Models, Listeners)
    Users/  (User, Department, Types)
    SSO/    (Passport/OIDC controllers, claims builders)
app/Livewire/
  Admin/
    Users/, Roles/, Portals/, Audits/, Settings/
database/
  migrations/, seeders/, factories/
routes/
  web.php, api.php, oauth.php
resources/
  views/ (Livewire + Blade)
```

---

## 🗄️ Database Model (core tables)

* `users` (id, employee_id*, name, email, password, department_id, type, status)
* `departments` (id, name)
* `user_types` (id, code, name) — flexible mapping
* `model_has_roles`, `roles`, `permissions`, `role_has_permissions` (Spatie)
* `portals` (id, key, name, description, active)
* `portal_permissions` (portal_id, permission_id) — optional scoping
* `user_portal_access` (user_id, portal_id, enabled, scopes[])
* `social_accounts` (user_id, provider, provider_user_id, email, linked_at)
* `mfa_secrets` (user_id, secret, recovery_codes, enabled, last_used_at)
* `audit_logs` (id, user_id?, actor_id?, action, resource_type, resource_id, ip, ua, old, new, created_at)

* `employee_id` is unique and primary identifier for login.

---

## 🔐 SSO Protocols

* **OAuth2 / OIDC** using Passport

  * **Grant:** Authorization Code + PKCE (for SPAs & native apps)
  * **Scopes:** portal-specific (e.g., `cargosense.read`, `hr.write`)
  * **ID Token:** user claims (sub = user UUID or employee_id, name, email, roles, portals)
  * **Access Tokens:** short-lived (e.g., 15 min); **Refresh Tokens:** rotating, 30–90 days
  * **Introspection endpoint** for backend portals; **/userinfo** for OIDC

---

## 🚀 Getting Started

### Prerequisites

* PHP 8.3, Composer 2.5+
* MySQL 8.x
* Node 18+ & PNPM/Yarn/NPM
* Redis (recommended)
* OpenSSL (for Passport keys)

### Installation

```bash
git clone https://github.com/your-org/cap.git
cd cap
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed

# Passport (OAuth2)
php artisan passport:install --uuids
# (Stores keys in storage/oauth-*; ensure writable)

# Front-end (Livewire/Tailwind)
pnpm install
pnpm run build # or: pnpm dev
```

Set a web server to point to `/public`. Ensure `APP_URL` is correct and HTTPS is enforced in production.

---

## ⚙️ Environment (.env) Keys

```dotenv
APP_NAME="Central Auth Provider"
APP_ENV=local
APP_URL=https://auth.company.local
APP_KEY=base64:...

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cap
DB_USERNAME=cap_user
DB_PASSWORD=secret

SESSION_DRIVER=redis
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_CLIENT=phpredis

# OAuth2 / Passport
PASSPORT_PERSONAL_ACCESS_TOKENS_TTL=PT30M
PASSPORT_PASSWORD_GRANT_TOKENS_TTL=PT30M
PASSPORT_REFRESH_TOKENS_TTL=P30D

# Socialite (Google)
GOOGLE_CLIENT_ID=...
GOOGLE_CLIENT_SECRET=...
GOOGLE_REDIRECT_URI=${APP_URL}/oauth/social/google/callback

# Socialite (Microsoft Entra ID - v2)
MICROSOFT_CLIENT_ID=...
MICROSOFT_CLIENT_SECRET=...
MICROSOFT_REDIRECT_URI=${APP_URL}/oauth/social/microsoft/callback
MICROSOFT_TENANT=common # or your tenant id

# Mail (for invites/MFA recovery/alerts)
MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=auth@company.com
MAIL_FROM_NAME="${APP_NAME}"
```

---

## 🔑 Admin Access & Seeding

* The seeder creates:

  * An **admin** user with role `super-admin` (credentials shown in console after seeding)
  * Default roles: `super-admin`, `it-admin`, `manager`, `employee`
  * Example portals & permissions
* Change the seeded password immediately.

---

## 👥 User Lifecycle

### 1) Admin Import (CSV/XLSX)

Upload a file (Livewire import UI) with headers:

```
employee_id,name,email,department,type,role
EMP001,Anita Sharma,anita@company.com,Engineering,Employee,employee
EMP002,Rahul Verma,rahul@company.com,IT,Manager,it-admin
```

* New rows create placeholder accounts (inactive until password set).
* Existing rows update attributes (role/department/type).

### 2) Controlled Signup (Self-Activation)

* User visits `/register`, enters **Employee ID** & email.
* If **employee_id exists**, proceed to set password + optional MFA.
* Otherwise, registration is blocked and prompts to contact IT.

### 3) Social Linking

* From profile → “Linked Accounts” → Connect Google / Microsoft.
* Login flow can allow “Continue with Google/Microsoft” **only** after link exists (policy toggle to allow/disallow first-time social sign-in).

### 4) MFA (Optional)

* User enables MFA (TOTP QR + 8 recovery codes).
* Admin policy can mark MFA as **recommended** or **required** by role/portal.

---

## 🧭 Access & Permissions (RBAC)

* **Roles** grant **permissions** (Spatie).
* **Portals** define available **scopes**; map permissions to portal scopes in UI.
* **User portal access:** enable/disable per user + select scopes.
* Livewire “Permissions Console” offers:

  * Assign role(s) to user
  * Grant/remove portal access
  * Review effective permissions
  * Bulk actions by department/type

---

## 📚 SSO for Client Portals (How to Integrate)

### Register a Client

1. Admin → **Portals** → “Register New Client”
2. Capture: `name`, `redirect_uris`, `post_logout_redirect_uris`, `scopes`, `policy`
3. System generates **client_id** (+ optional **client_secret** for confidential clients)

### Authorization Code + PKCE Flow

1. Redirect user to:

```
GET ${APP_URL}/oauth/authorize?
  response_type=code&
  client_id=CLIENT_ID&
  redirect_uri=ENCODED_URI&
  scope=cargosense.read cargosense.write&
  state=RANDOM&
  code_challenge=...&code_challenge_method=S256
```

2. After consent, CAP redirects to client with `code`.
3. Exchange `code` for tokens:

```
POST ${APP_URL}/oauth/token
{
  "grant_type":"authorization_code",
  "client_id":"CLIENT_ID",
  "redirect_uri":"ENCODED_URI",
  "code_verifier":"...",
  "code":"..."
}
```

4. Use `access_token` to call your APIs; read user info:

```
GET ${APP_URL}/oauth/userinfo
Authorization: Bearer ACCESS_TOKEN
```

5. Validate ID Token (OIDC) via published JWKs (Passport keys).

### Token Introspection (for server-side portals)

```
POST ${APP_URL}/oauth/introspect
Authorization: Basic base64(client_id:client_secret)
token=ACCESS_OR_REFRESH_TOKEN
```

---

## 🧾 API Endpoints (selected)

* `POST /login` — employee_id + password
* `POST /logout`
* `POST /mfa/enable`, `POST /mfa/verify`, `POST /mfa/disable`
* `GET /me` — profile + roles + portals + scopes
* `GET /oauth/authorize`, `POST /oauth/token`, `GET /.well-known/openid-configuration`, `GET /oauth/userinfo`, `GET /oauth/jwks.json`
* `POST /admin/users/import` — CSV/XLSX
* `GET /admin/audits` — filters: actor, action, resource, date range
* `POST /admin/portals` — register client app
* `POST /admin/users/{id}/roles`, `POST /admin/users/{id}/portals`

(Exact routes may vary; see `routes/`.)

---

## 🔍 Audit Logging

Tracked events (non-exhaustive):

* Login success/failure (with reason), logout, MFA prompts/verify/fail
* User created/updated/deleted; password resets; social link/unlink
* Role/permission/portal assignments, client registrations/changes
* Admin actions (CSV imports, policy updates)
* Token events (issued, refreshed, revoked)

**Filters:** date range, actor, affected user, portal, action, IP, user agent.
**Storage:** `audit_logs` JSON columns `old`/`new`.

---

## 🛡️ Security & Compliance

* Enforce HTTPS, secure cookies, HSTS
* Short-lived access tokens, rotating refresh tokens
* Password hashing: Argon2id (default)
* Account lockout on repeated failures (configurable)
* CSRF protection for first-party forms
* Secret rotation & key management (Passport + APP_KEY backups)
* Admin policy: require MFA for privileged roles
* Full auditability for compliance reviews

---

## 🧰 Admin UI (Livewire)

* **Users:** CRUD, import, status, reset password, force MFA
* **Roles & Permissions:** create roles, map permissions, assign to users
* **Portals:** register clients, set redirect URIs, map scopes ↔ permissions
* **Audits:** filterable table + export (CSV)
* **Security Policies:** MFA requirement, session TTLs, password rules

---

## 🧪 Testing

```bash
php artisan test
# Or run specific suites:
php artisan test --testsuite=Feature
```

* Feature tests for: login, MFA, social linking, RBAC gates, OAuth flows
* Integration tests for: audit events, CSV imports

---

## 🔁 Operations

* **Queues:** `php artisan queue:work` (Redis recommended)
* **Scheduler:** `php artisan schedule:work` (token pruning, audit archiving)
* **Backups:** DB + `storage/oauth-*` keys; automate daily snapshots
* **Monitoring:** failed jobs, audit anomaly alerts (optional)

---

## 📈 Roadmap (optional)

* Device management (remembered devices, revoke)
* WebAuthn (FIDO2) MFA
* SCIM user provisioning for portals
* Admin audit analytics dashboard

---

## 📝 License

Private, proprietary — © Evolvedge Ventures Private Limited.
All rights reserved.

---

## 🙋 Support

* IT Admins: open an internal ticket or contact `it@company.com`
* Security: `security@company.com`

---

### Appendix A — Quick CSV Template

```
employee_id,name,email,department,type,role,enabled
EMP1001,Anurag Kumar,anurag@company.com,IT,Manager,super-admin,1
EMP1023,Amit Barla,amit@company.com,Engineering,Employee,employee,1
```

### Appendix B — Example OIDC Discovery

* `GET ${APP_URL}/.well-known/openid-configuration`
  Provides `authorization_endpoint`, `token_endpoint`, `userinfo_endpoint`, `jwks_uri`, `scopes_supported`, etc.

---

> **Tip:** If your portals are **Laravel** apps, use Passport as Resource Server and validate tokens with the CAP JWKS. For **SPA** frontends, prefer **Auth Code + PKCE** and store only short-lived tokens in memory.
