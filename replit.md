# BOLSO Workshop Website

BOLSO is a small-batch fabric-painting workshop site with public workshop registration and a PHP session-based admin dashboard.

## Run & Operate

- The BOLSO preview is served by the managed web workflow with PHP's built-in server.
- For production, upload `artifacts/bolso-workshop/` to Apache/PHP and import `database.sql` in phpMyAdmin.
- Copy `config/config.local.example.php` to `config/config.local.php` and configure the MySQL connection before accepting registrations.
- `find artifacts/bolso-workshop -name '*.php' -print0 | xargs -0 -n1 php -l` — lint PHP files.

## Stack

- PHP 8.1+ with PDO MySQL
- MySQL 8+ / MariaDB
- HTML5, CSS3, vanilla JavaScript, Bootstrap 5
- PHP Sessions for admin authentication

## Where things live

- `artifacts/bolso-workshop/index.php` — public home page
- `artifacts/bolso-workshop/workshops.php` — the two workshop options
- `artifacts/bolso-workshop/registration.php` — registration form and live price calculation
- `artifacts/bolso-workshop/admin/` — session-protected login and registration dashboard
- `artifacts/bolso-workshop/config/` — database and brand configuration
- `artifacts/bolso-workshop/database.sql` — MySQL schema and starter data
- `artifacts/bolso-workshop/assets/` — BOLSO styles, browser behavior, and artwork placeholders

## Architecture decisions

- Keep the public site intentionally small: Home, Workshops, Registration, and Admin.
- Use PDO prepared statements for registration inserts and admin mutations.
- Keep payment provider integration as a configuration seam; never simulate a payment.
- Keep artwork paths stable so real work can replace the placeholders without changing templates.

## Product

- Visitors can understand BOLSO's teaching approach and choose a 2-day or 5-day class.
- Visitors can register for online or offline sessions and see the correct price immediately.
- Admins can view registration details, update payment status, and delete registrations.

## User preferences

- Keep the project deployable on a standard Apache + PHP + MySQL host without a Node build process.

## Gotchas

- The registration form intentionally shows a setup error until MySQL credentials are configured.
- The seeded admin account is `admin` / `bolso2026`; change it immediately after importing the SQL.
- Replace `assets/images/work.jpg` and `work2.jpg` with real artwork on the hosting server.

## Pointers

- See `artifacts/bolso-workshop/README.md` for the XAMPP setup checklist.
