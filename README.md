# Central Authentication Provider (Laravel)

This repository contains a Laravel-based authentication provider intended to be the
single sign-on (SSO) hub for all internal company portals. The implementation
focuses on employee-centric login, optional multi-factor authentication, social
identity linking, centralized access management, and extensive auditing.

## Key Capabilities

- **Employee login** using employee IDs and passwords, with conditional multi-factor
  authentication (MFA) support (TOTP and backup codes).
- **Single sign-on experience** for downstream portals via signed OAuth/OpenID Connect
  tokens issued after successful authentication.
- **Role and permission management** tied to departments, user types, and per-portal
  privilege assignments.
- **Social identity linking** for Google and Microsoft Entra ID accounts to simplify
  future logins.
- **Auditing and observability** with searchable login, account change, and access
  modification trails.
- **Livewire-powered UI** for responsive administration screens and authentication
  flows.

For implementation details, reference the architectural blueprint in
[`docs/architecture.md`](docs/architecture.md).
