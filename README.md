# PHP MFA Login

A small PHP application that combines username/password authentication with an SMS verification code sent through Twilio Verify. User accounts are stored in MySQL, and PHP sessions track authentication.

## How it works

1. The user enters their username and password on `login.php`.
2. The application retrieves the account from MySQL and checks the password with `password_verify()`.
3. After a successful password check, the account is stored in the session as `pending_user`, and Twilio Verify sends an SMS to its stored phone number.
4. The user enters the code on `verify.php`. The application asks Twilio to check it.
5. When Twilio returns `approved`, the application regenerates the session ID, creates the authenticated session, clears the pending account, and redirects to `dashboard.php`.
6. The dashboard displays the username and provides a logout button that clears and destroys the session.

The dashboard requires an authenticated session. Password verification alone does not grant access.

## Requirements

- PHP 8.1 or later (the code uses the `never` return type).
- PHP PDO with the MySQL driver, and the extensions required by the Composer dependencies.
- Composer.
- A MySQL database.
- A Twilio account and a Twilio Verify service configured for SMS.
- A phone number that can receive verification messages through your Twilio configuration.
- Outbound connectivity to Twilio from the PHP server.

## Setup

### 1. Install dependencies

From the project directory, run:

```sh
composer install
composer check-platform-reqs
```

Composer installs `twilio/sdk`, `vlucas/phpdotenv`, and their dependencies using the versions in `composer.lock`.

### 2. Configure the environment

Create or update `.env` in the project root with your own values. Preserve any existing working configuration.

```dotenv
DB_HOST=127.0.0.1
DB_NAME=mfa_login
DB_USER=your_database_user
DB_PASS="your_database_password"

TWILIO_ACCOUNT_SID=your_account_sid
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_VERIFY_SID=your_verify_service_sid
```

`TWILIO_VERIFY_SID` identifies the Verify service used to send and check codes. Keep `.env` private and out of version control.

### 3. Create the database table

No database migration or schema file is included. The following example creates the table expected by the application. If you choose a different database name, update `DB_NAME` accordingly.

```sql
CREATE DATABASE IF NOT EXISTS mfa_login
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE mfa_login;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL
);
```

Ensure the configured database user can connect to this database and read the `users` table.

### 4. Add a user

There is no registration page. Create accounts directly in the database, storing a password hash rather than a plaintext password.

Generate a hash locally with PHP, replacing the example password first:

```sh
php -r "echo password_hash('replace-with-a-unique-password', PASSWORD_DEFAULT), PHP_EOL;"
```

Insert the generated hash and the user's real phone number:

```sql
INSERT INTO users (username, password_hash, phone)
VALUES ('demo', 'PASTE_GENERATED_HASH_HERE', '+15551234567');
```

Use an international phone number with its country code, such as the format shown above. The sample number is a placeholder.

### 5. Run locally

From the project directory:

```sh
php -S 127.0.0.1:8000
```

Open `http://127.0.0.1:8000/login.php`, sign in with the account you created, and enter the SMS code. There is no `index.php`, so open the login page explicitly.

The PHP built-in server is for local development. Sending verification messages uses the configured Twilio account and may incur charges.

## Project files

| File | Purpose |
| --- | --- |
| `config.php` | Starts the session, loads environment variables and Composer autoloading, connects to MySQL, creates the Twilio client, and defines redirect and HTML-escaping helpers. |
| `login.php` | Checks credentials and requests an SMS verification code. |
| `verify.php` | Checks the submitted code through Twilio and completes authentication. |
| `dashboard.php` | Shows a protected welcome page and handles logout. |
| `test.php` | Sends an SMS verification request to a hardcoded phone number. |
| `composer.json` | Declares the Twilio SDK and dotenv dependencies. |
| `composer.lock` | Records dependency versions for reproducible installation. |
| `.env` | Stores local database and Twilio configuration. |
| `vendor/` | Contains dependencies installed by Composer. |

## Manual verification

There is no automated test suite. To check the application manually:

1. Open `dashboard.php` without signing in and confirm that it redirects to login.
2. Submit incorrect credentials and confirm that an error appears.
3. Submit valid credentials and confirm that an SMS arrives and the verification page opens.
4. Before completing verification, confirm that the dashboard still requires login.
5. Submit an incorrect code and confirm that access is denied.
6. Submit the valid code and confirm that the dashboard displays the correct username.
7. Log out and confirm that the dashboard is protected again.

`test.php` is a live SMS helper, not an automated test. Before running it, replace its hardcoded phone number with an intended test recipient. Opening it sends an SMS request immediately, and it also requires a working database connection because it loads `config.php`.

## Troubleshooting

| Symptom | Check |
| --- | --- |
| Missing `vendor/autoload.php` | Run `composer install` in the project directory. |
| Environment loading fails | Ensure `.env` exists in the project root and contains the required settings. |
| Database connection fails | Check the MySQL server, PDO MySQL extension, database name, credentials, and permissions. |
| Valid credentials are rejected | Confirm the account exists and `password_hash` contains a hash generated by `password_hash()`. |
| SMS cannot be sent | Check the Twilio credentials, Verify service SID, recipient number, account restrictions, and server connectivity. Inspect the PHP error log for details. |
| Code verification fails | Check the submitted code and whether it is still valid. Inspect the PHP error log for Twilio exceptions. |

## Current scope and limitations

This project provides a basic authentication demonstration. It has no registration, password reset, phone enrollment, recovery codes, resend button, or account-management screens. The dashboard contains only a welcome message and logout.

The code uses prepared database queries, password-hash verification, escaped HTML output, and session ID regeneration after successful MFA. It does not implement application-level rate limiting, CSRF tokens, or an explicit expiry for pending authentication. Twilio handles SMS code checking; the application does not generate or store codes itself.

Before public deployment, address those missing controls, configure HTTPS and secure session cookies, prevent web access to `.env`, and remove or restrict `test.php`. Database connection errors currently expose exception details to the browser, and `test.php` displays Twilio exception details directly.
