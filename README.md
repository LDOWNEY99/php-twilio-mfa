# PHP MFA Login

A simple PHP and MySQL project with account registration, password login, and SMS two-factor authentication using Twilio Verify. Users enter a verification code sent to their phone before accessing a dashboard with a logout button.

## Setup

1. Install XAMPP with PHP 8.1 or later and Composer.
2. Start MySQL and import `setup.sql` using phpMyAdmin or a MySQL client.
3. Copy `.env.example` to `.env` and fill in your database credentials, Twilio account SID, auth token, and Verify service SID.
4. Run `composer install` in the project folder.
5. Run `php -S 127.0.0.1:8000` in the project folder and open `http://127.0.0.1:8000/sign-up.php`.
6. Create an account using a phone number starting with `+353`, then enter the SMS code. For future logins, use `login.php`.

Keep your `.env` credentials private. SMS verification requires a configured Twilio account and may incur charges.

## Issue I ran into

When running `composer install`, I received this error:

```text
Failed to download twilio/sdk from dist: The zip extension and unzip/7z commands are both missing
```

To fix it, I opened `xampp\php\php.ini` and removed the `;` from:

```ini
;extension=zip
```

So it became:

```ini
extension=zip
```

I saved the file and reran `composer install`.
