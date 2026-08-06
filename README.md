# SharaForms

<p align="center">
<img src="https://github.com/SharaForms/SharaForms/blob/main/client/public/img/social-preview.jpg?raw=true">
</p>

<p align="center">
<a href="https://github.com/SharaForms/SharaForms/stargazers"><img src="https://img.shields.io/github/stars/SharaForms/SharaForms" alt="Github Stars"></a>
</a>
<a href="https://github.com/SharaForms/SharaForms/pulse"><img src="https://img.shields.io/github/commit-activity/m/SharaForms/SharaForms" alt="Commits per month"></a>
<a href="https://hub.docker.com/r/sharaforms/api">
<img src="https://img.shields.io/docker/pulls/sharaforms/api">
</a>
<a href="https://github.com/SharaForms/SharaForms/blob/main/LICENSE"><img src="https://img.shields.io/badge/license-AGPLv3-purple" alt="License">
<a href="https://github.com/SharaForms/SharaForms/issues/new"><img src="https://img.shields.io/badge/Report a bug-Github-%231F80C0" alt="Report a bug"></a>
<a href="https://github.com/SharaForms/SharaForms/discussions/new?category=q-a"><img src="https://img.shields.io/badge/Ask a question-Github-%231F80C0" alt="Ask a question"></a>
<a href="https://feedback.sharaforms.com"><img src="https://img.shields.io/badge/Feature request-Featurebase-%231F80C0" alt="Ask a question"></a>
<a href="https://discord.gg/sharaforms"><img src="https://img.shields.io/badge/SharaForms-Discord-%235865F2.svg" alt="Ask a question"></a>
</p>

SharaForms is a form builder.

## Get Started

The easiest way to get started with SharaForms is to sign up for our [managed service in the Cloud](https://sharaforms.com/). You get support, backups, upgrades, and more. Your data is safe and secure, and you don't need to worry about maintenance or infrastructure. Check out our quick overview of [cloud vs self-hosting](https://docs.sharaforms.com/deployment/cloud-vs-self-hosting).

## Key Features

-   🚀 No-code builder with unlimited forms & submissions
-   📝 Various input types: Text, Date, URL, File uploads & much more
-   🌐 Embed anywhere
-   📧 Email notifications
-   💬 Integrations (Slack, Webhooks, Discord)
-   🧠 Form logic & customization
-   🛡️ Captcha protection
-   📊 Form analytics

For a complete list of features and detailed documentation, visit our [Technical Documentation](https://docs.sharaforms.com).

## Quick Start

The easiest way to get started with SharaForms is through our [official managed service in the Cloud](https://sharaforms.com/).

For self-hosted installations, please refer to our [Deployment Guides](https://docs.sharaforms.com/deployment). For local development, we provide a minimal Docker-based setup - check out our [Docker Development Guide](https://docs.sharaforms.com/deployment/docker-development).

## Operational Safeguards

-   Health checks are exposed at `/api/healthcheck`.
-   Daily database backups are scheduled through `php artisan app:backup-database` using the `BACKUP_*` environment variables from `api/.env.example`.
-   Auth, password reset, OIDC lookup, and 2FA verification endpoints are rate limited by default.
-   API tokens and browser session settings now assume production-safe defaults; review `CORS_ALLOWED_ORIGINS`, `SANCTUM_TOKEN_EXPIRATION`, and the `SESSION_*` variables before launch.
-   Production Docker now routes PostgreSQL traffic through PgBouncer for connection pooling.
-   Production Docker defaults to S3-compatible object storage rather than local disk for uploaded files.
-   Soft-deleted forms are purged automatically on a schedule to prevent indefinite retention.

## Support & Community

If you need help or have questions, please join our [Discord community](https://discord.gg/sharaforms). For more information and assistance, check out the following resources:

-   [Product Helpdesk](https://help.sharaforms.com)
-   [Technical Documentation](https://docs.sharaforms.com)

## License

SharaForms is available under the GNU Affero General Public License Version 3 (AGPLv3) or any later version. You can find it [here](https://github.com/SharaForms/SharaForms/blob/main/LICENSE).

### Dual Licensing

SharaForms uses a dual-license model to make the project sustainable:

-   **Core SharaForms** (AGPL-3.0): The main application is free under AGPLv3, giving you the freedom to use, modify, and distribute it.
-   **Enterprise Edition** (Proprietary): Advanced features under `api/app/Enterprise/` are available under our [Enterprise License](https://github.com/SharaForms/SharaForms/blob/main/api/app/Enterprise/LICENSE) and [Enterprise Terms](https://sharaforms.com/terms-conditions). These features help fund ongoing development and keep SharaForms sustainable.

By offering Enterprise features alongside our core, we can continue to invest in making SharaForms better for everyone while keeping the project financially sustainable.
