# BOLSO Workshop Website

Plain PHP + MySQL workshop site for Apache/XAMPP hosting.

## Local setup

1. Create a MySQL database by importing `database.sql` in phpMyAdmin.
2. Copy `config/config.local.example.php` to `config/config.local.php` and set your credentials.
3. Change the seeded admin password after your first login.
4. Put the folder inside XAMPP's `htdocs` directory and visit `http://localhost/bolso-workshop/`.

The registration form uses prepared PDO statements. Payment is intentionally not simulated; add your chosen provider and credentials where marked in `config/config.php`, then persist its webhook result to `registrations.payment_status` and `payments`.

## Admin

The SQL seed creates:

- Username: `admin`
- Password: `bolso2026`

Change this immediately in the database after importing.

## Customization

- Replace `assets/images/work.jpg` and `assets/images/work2.jpg` with artwork.
- Change `BOLSO_WHATSAPP_NUMBER` or `BOLSO_WHATSAPP` in `config/config.local.php`.
- Keep credentials in `config/config.local.php` or hosting environment variables, never in committed files.