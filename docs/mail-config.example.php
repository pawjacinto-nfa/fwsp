<?php
// Copy to C:/xampp/fsr-private/mail.php on this machine (outside the public website).
// On another host: set FSR_MAIL_CONFIG_FILE to the private file's absolute path.
return [
    'host' => '', // Outgoing SMTP server supplied by your email provider.
    'port' => 587,
    'encryption' => 'tls', // tls for STARTTLS, or ssl with port 465.
    'username' => '',
    'password' => '', // SMTP credential / app password; never commit this value.
    'from' => '', // Authorized sender email address.
    'base_url' => '', // Full HTTPS application URL, e.g. https://your-domain.example/fsr
];
