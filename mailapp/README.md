# MailApp — a small Gmail-style webmail app (PHP + MySQL + PHPMailer)

A self-contained webmail client: login/register, Compose (with attachments),
and three folders — **Inbox** (unread), **Sent**, and **Received** (your full
incoming history). Sending is real (via PHPMailer/SMTP); Inbox/Received are
simulated inside your own database — if the recipient is also a registered
user of this app, the email shows up in their Received/Inbox automatically.

## 1. Requirements
- XAMPP or WAMP (PHP 8+, MySQL/MariaDB, Apache)
- Composer (https://getcomposer.org/) — to install PHPMailer

## 2. Install the files
1. Copy the whole `mailapp` folder into your XAMPP `htdocs` directory, e.g.
   `C:\xampp\htdocs\mailapp` (Windows) or `/Applications/XAMPP/htdocs/mailapp` (Mac).
2. Open a terminal **inside that folder** and run:
   ```
   composer install
   ```
   This downloads PHPMailer into a `vendor/` folder that `send.php` expects.

## 3. Create the database
1. Start Apache and MySQL from the XAMPP control panel.
2. Open `http://localhost/phpmyadmin`.
3. Click **Import** → choose `db.sql` from this folder → **Go**.
   (Or from a terminal: `mysql -u root -p < db.sql`)

   This creates a `mailapp` database with `users`, `emails`, and `attachments` tables.

## 4. Configure `config.php`
Open `config.php` and fill in two sections:

**Database** — defaults already match a fresh XAMPP install (`root` / no password).
Change `DB_USER`/`DB_PASS` if yours differ.

**SMTP** — this is what actually delivers mail. Easiest option: use a Gmail
account.
1. On that Google account, turn on **2-Step Verification**.
2. Go to Google Account → Security → **App Passwords**, create one (choose
   "Mail" / "Other"), and copy the 16-character password.
3. Put your Gmail address in `SMTP_USER` and that app password in `SMTP_PASS`.

You can swap in any other SMTP provider (Outlook, SendGrid, Mailtrap for
testing, your own mail server, etc.) by changing `SMTP_HOST`/`SMTP_PORT` too.

> Note: mail is actually sent *from* the `SMTP_USER` account (that's the
> account with the real login), but each user's own address is set as the
> **Reply-To**, so replies go back to them, and the app tracks the real
> sender internally.

## 5. Make the uploads folder writable
The `uploads/` folder needs to be writable by PHP so attachments can be saved.
On Mac/Linux: `chmod -R 775 uploads`. On Windows/XAMPP this is usually fine
by default.

## 6. Run it
Visit `http://localhost/mailapp/` in your browser. Register two accounts
(e.g. `alice@test.com` and `bob@test.com` — use real addresses you can check
if you want to see delivery too) to try sending mail between them: it will
show up in the sender's **Sent** folder and the recipient's **Inbox** /
**Received** folders instantly, and PHPMailer will also attempt real
delivery over SMTP.

## How the three folders work
- **Inbox** — received mail that's still unread.
- **Received** — your full history of everything ever received (read + unread).
- **Sent** — everything you've sent.

If you'd rather Inbox show *everything* (not just unread) and drop the
Inbox/Received distinction, that's a one-line change: copy the query from
`received.php` into `inbox.php`.

## File map
```
config.php              DB + SMTP settings (edit this first)
db.sql                  Run once to create the database/tables
composer.json           Pulls in PHPMailer
login.php / register.php / logout.php
inbox.php / sent.php / received.php   The three folders
compose.php             New message form
send.php                Sends via PHPMailer + logs to DB
view_email.php          Single message view, marks as read
download_attachment.php Secure attachment download
includes/auth.php       Session helpers
includes/sidebar.php    Left nav, shown on every app page
assets/style.css        All styling
uploads/                Attachment storage (not web-accessible directly)
```

## Security notes for going further
- Passwords are hashed with `password_hash()` / verified with `password_verify()` — good as-is.
- All queries use PDO prepared statements.
- `uploads/.htaccess` blocks direct downloads; files are only served through
  `download_attachment.php`, which checks the requester is the sender or recipient.
- For production use, also add: CSRF tokens on forms, rate-limiting on
  login/register, and a stricter attachment MIME-type allow-list.
