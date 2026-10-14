# Email password resets

Users request a reset with their employee number. The system sends a six-digit confirmation code and a random link only to their registered email. The link opens the confirmation form with their employee number filled in; it does not reset or authenticate an account on its own. Users may also enter their employee number and emailed code manually.

Codes expire after 15 minutes, allow five attempts, and are stored using password hashing. Link tokens and session grants use 32 random bytes and are stored as SHA-256 hashes. Verifying a code consumes it and creates a grant tied to the browser session; completing the reset consumes that grant atomically. Both stages share the original expiration. Resends have a 60-second cooldown and invalidate earlier codes and grants. Existing HTTP authentication rate limits and CSRF protection remain in effect. Prior administrator-issued reset codes no longer work. Successful resets invalidate existing signed-in sessions through the password fingerprint and revoke offline devices.

## Configure the sender

Copy `mail-config.example.php` to `C:/xampp/fsr-private/mail.php`, outside the public website. Fill in the outgoing SMTP settings supplied by your email provider, an authorized sender address, and the deployed HTTPS application base URL. Restrict file access to the server administrator and PHP service. Do not store credentials in source control or send them through chat.

Other hosts can specify a private file using `FSR_MAIL_CONFIG_FILE`. Environment variables `FSR_MAIL_HOST`, `FSR_MAIL_PORT`, `FSR_MAIL_USERNAME`, `FSR_MAIL_PASSWORD`, `FSR_MAIL_ENCRYPTION`, `FSR_MAIL_FROM`, and `FSR_MAIL_BASE_URL` override file settings. TLS certificate verification stays enabled. The URL comes from configuration, never the request Host header.

Run `php tests/send-reset-email.php pawjacinto@gmail.com` to send a clearly marked delivery test without changing an account or issuing working reset credentials. SMTP acceptance does not confirm inbox delivery; check the inbox/spam folder. The centered HTML template lives in `app/Views/emails/password-reset.php`, with embedded header/footer artwork under `assets/images/email`. It uses the registered full name, confirmation code, and reset URL as escaped dynamic values. The exact subject is `NFA Farmer Seller Registry: Password Reset Request`. A plain-text alternative remains available in `app/Support/ResetMailer.php`. Run `php tests/reset-email-template.php` to validate escaping and create a browser preview in `output/reset-email-preview.html`.

Missing configuration displays a general service-unavailable message. Delivery failures invalidate the newly issued code and produce a server-log notice without email content or credentials. Public account-specific results are deliberately generic. Accounts without a valid registered email must have their address corrected through the existing account management process.

The SMTP integration uses [PHPMailer's documented TLS settings](https://phpmailer.github.io/PHPMailer/classes/PHPMailer-PHPMailer-PHPMailer.html).
