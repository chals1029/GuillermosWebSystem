<?php

use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/../vendor/autoload.php';

class EmailApiController
{
    private const SMTP_HOST = 'smtp.gmail.com';
    private const SMTP_PORT = 465; // Switch to 587 if using STARTTLS
    private const SMTP_USERNAME = 'charlessamotanez24@gmail.com';
    private const SMTP_PASSWORD = 'ynbb jgwl vcre hmyn';
    private const SMTP_ENCRYPTION = PHPMailer::ENCRYPTION_SMTPS;
    private const SMTP_DEBUG_LEVEL = SMTP::DEBUG_SERVER; // Set to DEBUG_OFF in production
    private const FROM_EMAIL = 'charlessamotanez24@gmail.com';
    private const FROM_NAME = "Guillermo's Web System";

    /**
     * Send a verification email via Gmail SMTP + app password.
     *
     * @param string $email
     * @param string $name
     * @param string $code
    * @return bool|string
     */
    public static function sendVerificationEmail(...$args)
    {
        $email = (string)($args[0] ?? '');
        $name = (string)($args[1] ?? '');
        $code = (string)($args[2] ?? '');

        $sanitizedEmail = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
        if (!filter_var($sanitizedEmail, FILTER_VALIDATE_EMAIL)) {
            self::logEvent('Invalid recipient email: ' . $email);
            return 'Invalid recipient email address.';
        }

        $normalizedName = trim($name);
        if ($normalizedName === '') {
            $normalizedName = 'Customer';
        }

        $normalizedCode = trim($code);

        $mail = new PHPMailer(true);

        try {
            // --- Server settings (mirrors PHPMailer quickstart example) ---
            $mail->SMTPDebug = self::SMTP_DEBUG_LEVEL;
            $mail->Debugoutput = static function ($str) {
                self::logEvent('SMTP DEBUG: ' . trim($str));
            };
            $mail->isSMTP();
            $mail->Host = self::SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = self::SMTP_USERNAME;
            $mail->Password = self::SMTP_PASSWORD;
            $mail->SMTPSecure = self::SMTP_ENCRYPTION;
            $mail->Port = self::SMTP_PORT;
            $mail->CharSet = 'UTF-8';

            // --- Recipients ---
            $mail->setFrom(self::FROM_EMAIL, self::FROM_NAME);
            $mail->addAddress($sanitizedEmail, $normalizedName);
            $mail->addReplyTo(self::FROM_EMAIL, self::FROM_NAME);

            // --- Content ---
            $mail->isHTML(true);
            $mail->Subject = "Your Guillermo's Verification Code";
            $mail->Body = "Hi {$normalizedName},<br><br>Thank you for registering. Your verification code is: <b>{$normalizedCode}</b><br><br>This code will expire in 10 minutes.<br><br>Best regards,<br>Guillermo's Team";
            $mail->AltBody = "Your verification code is: {$normalizedCode}.";

            self::logEvent(sprintf('Attempting to send verification email to %s', $sanitizedEmail));
            $mail->send();
            self::logEvent(sprintf('Verification email sent successfully to %s', $sanitizedEmail));
            return true;
        } catch (PHPMailerException $e) {
            $errorMessage = 'Failed to send verification email. ' . $e->getMessage();
            if (!empty($mail->ErrorInfo)) {
                $errorMessage .= ' | Mailer error: ' . $mail->ErrorInfo;
            }

            self::logEvent($errorMessage);
            return $errorMessage;
        }
    }

    public static function sendPasswordResetEmail(string $email, string $name, string $code)
    {
        $sanitizedEmail = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
        if (!filter_var($sanitizedEmail, FILTER_VALIDATE_EMAIL)) {
            self::logEvent('Invalid recipient email for password reset: ' . $email);
            return 'Invalid recipient email address.';
        }

        $normalizedName = trim($name);
        if ($normalizedName === '') {
            $normalizedName = 'Customer';
        }

        $normalizedCode = trim($code);

        $mail = new PHPMailer(true);

        try {
            $mail->SMTPDebug = self::SMTP_DEBUG_LEVEL;
            $mail->Debugoutput = static function ($str) {
                self::logEvent('SMTP DEBUG: ' . trim($str));
            };
            $mail->isSMTP();
            $mail->Host = self::SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = self::SMTP_USERNAME;
            $mail->Password = self::SMTP_PASSWORD;
            $mail->SMTPSecure = self::SMTP_ENCRYPTION;
            $mail->Port = self::SMTP_PORT;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom(self::FROM_EMAIL, self::FROM_NAME);
            $mail->addAddress($sanitizedEmail, $normalizedName);
            $mail->addReplyTo(self::FROM_EMAIL, self::FROM_NAME);

            $mail->isHTML(true);
            $mail->Subject = "Reset your Guillermo's password";
            $mail->Body = "Hi {$normalizedName},<br><br>We received a request to reset your password. Your reset code is: <b>{$normalizedCode}</b><br><br>This code will expire in 10 minutes. If you did not request a password reset, you can safely ignore this message.<br><br>Best regards,<br>Guillermo's Team";
            $mail->AltBody = "Your password reset code is: {$normalizedCode}.";

            self::logEvent(sprintf('Attempting to send password reset email to %s', $sanitizedEmail));
            $mail->send();
            self::logEvent(sprintf('Password reset email sent successfully to %s', $sanitizedEmail));
            return true;
        } catch (PHPMailerException $e) {
            $errorMessage = 'Failed to send password reset email. ' . $e->getMessage();
            if (!empty($mail->ErrorInfo)) {
                $errorMessage .= ' | Mailer error: ' . $mail->ErrorInfo;
            }

            self::logEvent($errorMessage);
            return $errorMessage;
        }
    }

    private static function logEvent(...$args): void
    {
        $logMessage = (string)($args[0] ?? '');
        // Use system temp dir to avoid collisions with project files that may be regular files.
        $logDir = rtrim(sys_get_temp_dir(), '\\/') . DIRECTORY_SEPARATOR . 'guillermos_logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }

        $logFile = $logDir . DIRECTORY_SEPARATOR . 'email.log';
        $timestamp = (new \DateTimeImmutable('now'))->format('Y-m-d H:i:s');
        @file_put_contents($logFile, sprintf("[%s] %s%s", $timestamp, $logMessage, PHP_EOL), FILE_APPEND);
    }
}
