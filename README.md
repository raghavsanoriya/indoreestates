# indoreestate

Indore-only real estate websites and dashboards built with Laravel 13 and PHP 8.3+. Option 3 (Homez) remains at the repository root. Option 1 and Option 2 are in `options/option-1` and `options/option-2`. Each retains its own design. Local previews use SQLite; production uses one shared, isolated MySQL database.

## Features

- Customer, agent and administrator permissions with session authentication.
- Property management, approval, recoverable deletion, ordered photos and media uploads.
- Indore-only public search, favorites, private inquiries, moderated reviews and database-driven statistics.
- Profile/avatar settings, password recovery, contact submissions and newsletter subscriptions.
- Full-width dashboard, responsive sidebar and hover/tap account menu.

## Local setup

Requirements: PHP 8.3+, Composer 2, and the `curl`, `dom`, `fileinfo`, `intl`, `mbstring`, `openssl`, `pdo_sqlite`, `sqlite3`, `xml` and `zip` extensions. Enable uploads of 5 MB, `post_max_size=160M` and `max_file_uploads=32` in your own PHP configuration.

```sh
git clone https://github.com/raghavsanoriya/indoreestates.git
cd indoreestates
composer install --no-scripts --no-plugins
php -r "file_exists('.env') || copy('.env.example', '.env');"
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan package:discover
php artisan key:generate
php artisan migrate --seed
php artisan local:serve --port=8003
```

Open `http://127.0.0.1:8003` and `/dashboard`. The local server uses the application's `public` directory. On Windows, `start-local.cmd` can also start the preview; an optional ignored `php.ini` beside it overrides the system configuration.

The supplied CSS, JavaScript and fonts are served directly, so the current UI does not require a Vite server. If working on the optional Vite entrypoints, use Node.js 24 and npm 11.17.0, run `npm ci --ignore-scripts`, then review required dependency scripts before running a build.

## Accounts and existing local data

Register your own account through the website. New accounts are customers; registration never accepts privileged roles. There are no shared administrator credentials or seeded demo accounts.

For a new local installation only, an operator can promote their registered account from `php artisan tinker`:

```php
$user = App\Models\User::where('email', 'your-registered-email@example.com')->firstOrFail();
$user->role = 'admin';
$user->save();
```

Administrators can then manage agent access through the dashboard. Replace the example email with your own; do not create public credentials in source control.

Existing local database records, imported project photos, user uploads, mail logs and sessions are deliberately not included. Back them up separately and securely when moving an existing installation. The amenity seeder is repeatable and does not reset user data.

To import current developer projects into a fresh database, use an existing agent/administrator email:

```sh
php artisan properties:import-developer ruchi --owner=your-registered-email@example.com --publish
php artisan properties:import-developer emerald --owner=your-registered-email@example.com --publish
```

Imports contact third-party developer websites and may depend on their availability or page structure. Only Indore projects are eligible for publication; missing prices display a contact action.

## Verification

```sh
php artisan test --compact
composer audit --locked
npm audit --audit-level=high
```

PHPUnit uses an isolated in-memory SQLite database, not the local site's database. GitHub Actions runs these tests, dependency audits and first-party JavaScript syntax checks on pushes and pull requests. The legacy optional `tests/browser/add-property.mjs` is not part of CI; it requires a separate Playwright installation and explicit credentials for an isolated disposable local test account.

## Configuration and asset rights

Copy `.env.example` and configure your own application key, database and mail settings. Email is logged locally by default; SMTP is not configured. Paid subscriptions and checkout are not active.

Before deployment, use HTTPS, set `APP_ENV=production`, `APP_DEBUG=false`, configure secure cookies and SMTP, and point the web server at `public`. Pushing a commit to GitHub does not by itself publish the websites.

## cPanel deployment — real-estate subdomain only

The checked-in `.cpanel.yml` invokes `deploy/cpanel.sh`. Its paths are restricted to `/home2/geicic3c/indore-estates.geic.in` and `/home2/geicic3c/indore-estates-apps`; it never targets the main domain's `public_html` or database. Create the cPanel-managed repository at `/home2/geicic3c/repositories/indoreestates` and configure a read-only GitHub deploy key for this repository only.

Before the first deployment, create the private `/home2/geicic3c/indore-estates-apps/shared/.env` from `deploy/shared.env.example`, configure a strong application key and the dedicated database credentials, and import Option 3's existing records and private media. Do not put real credentials, database backups, or media in Git. After verifying that import, create the private `shared/.master-import-complete` marker. The deploy task refuses to publish until those prerequisites exist.

The deployment installs locked production PHP dependencies with scripts/plugins disabled, builds three private releases, runs only pending additive migrations, and exposes only each release's `public` folder. It preserves previous releases and shared data; no reset, fresh migration, or seeding runs. The host must support public-folder symlinks and PHP 8.3 with MySQL, SQLite (for the initial import), and the extensions listed above. Persistent framework storage is separate for each option; photos and database records are shared.

Use cPanel **Update from Remote**, then **Deploy HEAD Commit** after green GitHub checks. This is a GitHub-backed manual deployment, not an automatic push-to-live workflow. Expected URLs are `/option-1/`, `/option-2/`, `/option-3/`, with each CRM at its own `/dashboard`. Only the subdomain root receives the option selector.

The source repository includes application code and the template assets used by the UI. Supplied Homez assets, fonts, images and the user-provided illustration retain their respective owners' licenses; their inclusion does not grant new redistribution rights. Unused captured third-party scripts and local website snapshots are excluded.
