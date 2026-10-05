# Deploying the donation system

The whole site (static pages + PHP) deploys as one unit to any PHP 8+ host (needs the `pdo_sqlite`, `curl`, `mbstring`, `gd`/`dom` extensions, which most hosts have).

1. **Install dependencies** (locally or via SSH): `composer install --no-dev`
2. **Create `config.php`**: copy `config.sample.php` and fill it in.
   - `smtp.password`: Gmail **App Password** for mctbangalore@gmail.com (Google Account > Security > 2-Step Verification > App passwords).
   - `db_path`: ideally a folder **outside** the public web root; it must be writable by PHP.
3. **Google Drive**
   - In Google Cloud Console create a project, enable the *Drive API*, and create an OAuth client of type *Desktop app*. Put the client ID/secret in `config.php`.
   - Create a Drive folder "Donation Screenshots" and put its ID (the last part of its URL) in `drive.folder_id`.
   - Run `php tools/get_drive_token.php` once and paste the printed refresh token into `config.php`.
   - If the OAuth consent screen is in "Testing" mode the token expires after 7 days; publish the app (or set it to "In production") for a permanent token.
4. **Admin password**: `php tools/make_admin_hash.php "your-password"` and paste the result into `config.php` under `admins`.
5. **Upload everything** (including `vendor/` and `config.php`) to the host. Make sure HTTPS is on and the `.htaccess` is honoured (Apache). On nginx, add equivalent deny rules for `/data`, `/lib`, `/templates`, `/vendor`, `/tools` and `config.php`.
6. Test: submit the form at `/donate`, then log in at `/admin/login.php` and approve it.
