# Architecture Overview

This document outlines the architecture for the Laravel-based centralized
authentication provider used to secure internal company portals. The solution is
implemented as a standalone Laravel application with Livewire-powered
interfaces, MySQL for persistence, and OAuth/OpenID Connect compliant token
issuance for SSO.

## Core Modules

1. **Authentication Core**
   - Employee ID + password login via Laravel's authentication guard.
   - Optional Time-based One-Time Password (TOTP) MFA with backup codes.
   - Device/session tracking to reduce unnecessary MFA prompts.
   - Social identity linking through OAuth for Google and Microsoft Entra ID.

2. **Directory & Profile Management**
   - Users have a unique employee ID, department, roles, and user types.
   - User types map to access policies for downstream portals.
   - Admin-managed bulk imports via CSV/Excel and manual user onboarding (limited
     to existing employee IDs).

3. **Access Control**
   - Role-based access control (RBAC) with per-portal permissions.
   - Policy evaluation service merges roles, types, and department-level rules.
   - Livewire-driven UI to manage users, assign roles, and configure portal
     access levels.

4. **Auditing & Reporting**
   - Comprehensive audit logs for logins, account changes, and permission
     updates.
   - Searchable interface with filters, CSV export, and API access.

5. **Single Sign-On (SSO)**
   - Implements authorization code + PKCE flow.
   - Issues signed JWT access tokens and refresh tokens.
   - Token claims include portal entitlements derived from RBAC policies.
   - Supports logout propagation via webhook notifications to downstream portals.

## Technology Stack

- Laravel 10.x with PHP 8.2+
- Livewire 3 for reactive UI components
- MySQL 8 as the primary datastore
- Redis for caching sessions and MFA state (optional)
- Composer for dependency management

## Directory Structure

```
app/
  Http/
    Controllers/
      Admin/
        PortalAccessController.php
      Auth/
        SocialAuthController.php
      AuthController.php
    Livewire/
      Auth/
        LoginForm.php
        MfaChallenge.php
        SocialLinkManager.php
      Admin/
        UserManager.php
        PortalPermissionMatrix.php
        AuditTrailTable.php
  Models/
    User.php
    Department.php
    Role.php
    Permission.php
    Portal.php
    UserType.php
    SocialAccount.php
    AuditLog.php
    LoginAttempt.php
    MfaSetting.php
  Services/
    Auth/
      MfaService.php
      SocialAccountService.php
      SsoTokenService.php
      UserProvisioningService.php
config/
  authprovider.php
  sso.php
  services.php
routes/
  web.php
  api.php
resources/views/livewire
  ...
database/migrations/
  2024_01_01_000000_create_auth_provider_tables.php
  2024_01_01_010000_create_audit_tables.php
  2024_01_01_020000_create_sso_tables.php
```

## Data Model Summary

| Table | Purpose |
|-------|---------|
| `users` | Employee-centric user profile including employee ID, department, type, and MFA flags. |
| `departments` | Departments with optional access policies. |
| `roles` | Global role definitions. |
| `user_types` | Defines user archetypes for portal mapping. |
| `portals` | Downstream portal registry. |
| `permissions` | Fine-grained action permissions scoped per portal. |
| `portal_role` | Pivot linking roles to portals. |
| `portal_permission` | Pivot linking permissions to portals. |
| `role_user` | Pivot linking users to roles. |
| `department_user` | Pivot linking users to departments (if multi-department support needed). |
| `permission_user` | Overrides for user-specific permissions. |
| `user_type_portal` | Mapping of user types to portal entitlements. |
| `social_accounts` | OAuth identities linked to users. |
| `mfa_settings` | Stores MFA secret, recovery codes, and device trust metadata. |
| `login_attempts` | Tracks every authentication attempt for auditing. |
| `audit_logs` | Change-log entries for account updates and access modifications. |
| `personal_access_tokens` | OAuth personal access tokens (Laravel Sanctum for API access). |
| `sso_clients` | Registered downstream portals/clients. |
| `sso_authorizations` | Authorization codes + PKCE verifiers for OAuth flows. |
| `sso_tokens` | Refresh/access tokens with claim payload. |

## Authentication Flow

1. **Employee Login**
   - User submits employee ID and password via Livewire `LoginForm` component.
   - Credentials validated; if MFA enabled, user is redirected to `MfaChallenge`.
   - On success, a session is established and `SsoTokenService` issues tokens as
     needed for requesting portals.

2. **MFA Enrollment**
   - Optional prompt after first login.
   - QR code generated for TOTP apps; backup codes provided.
   - Device trust cookie reduces repeated prompts.

3. **Social Linking**
   - `SocialLinkManager` triggers OAuth flows using Laravel Socialite.
   - Callback associates provider IDs with the user for future login bypass.

4. **SSO Token Exchange**
   - Portals redirect users to the provider's authorization endpoint.
   - After login, authorization code is issued and exchanged for JWT tokens.
   - Tokens include roles, permissions, and portal entitlements.

## Administration UI

- **UserManager**: Search/import users, assign roles, departments, types, and manage MFA status.
- **PortalPermissionMatrix**: Grid to manage portal access, permissions, and role mappings.
- **AuditTrailTable**: Filterable and exportable view of `audit_logs` and `login_attempts`.

## Security Considerations

- Passwords hashed using Argon2id.
- MFA secrets encrypted at rest with Laravel's encryption key.
- Audit logs are immutable and append-only.
- Rate limiting on login endpoints via Laravel's `ThrottleRequests` middleware.
- Signed URLs and CSRF protection for administrative actions.

## Deployment Notes

- Use Horizon or Supervisor for queue workers (audit, notifications).
- Configure queue + cache drivers (Redis recommended).
- Enforce HTTPS and secure cookie flags.
- Set up outbound email/SMS providers for MFA notifications if required.
