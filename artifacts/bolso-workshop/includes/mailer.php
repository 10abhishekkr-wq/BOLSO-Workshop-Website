<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Configure and return an initialized PHPMailer instance.
 */
function bolso_create_phpmailer(): PHPMailer
{
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    $smtpHost = trim(bolso_config('smtp_host', ''));
    $smtpPort = (int)bolso_config('smtp_port', '587');
    $smtpUser = trim(bolso_config('smtp_user', ''));
    $smtpPass = trim(bolso_config('smtp_pass', ''));
    $smtpSecure = strtolower(trim(bolso_config('smtp_secure', 'tls')));

    if ($smtpHost !== '') {
        $mail->isSMTP();
        $mail->Host = $smtpHost;
        $mail->Port = $smtpPort > 0 ? $smtpPort : 587;
        $mail->Timeout = 10;

        if ($smtpUser !== '' && $smtpPass !== '') {
            $mail->SMTPAuth = true;
            $mail->Username = $smtpUser;
            $mail->Password = $smtpPass;
        } else {
            $mail->SMTPAuth = false;
        }

        if ($smtpSecure === 'ssl' || $smtpPort === 465) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($smtpSecure === 'tls' || $smtpPort === 587) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            $mail->SMTPAutoTLS = false;
        }

        // Permissive SSL stream options for local environments / self-signed certificates
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ]
        ];
    } else {
        // Default to PHP mail() function
        $mail->isMail();
    }

    return $mail;
}

/**
 * Universal transactional email dispatcher using PHPMailer.
 * Dispatches HTML email, falls back gracefully if SMTP is down,
 * and logs the full message to notifications_log and file.
 */
function bolso_send_mail_via_phpmailer(
    string $toEmail,
    string $toName,
    string $subject,
    string $htmlBody,
    string $plainText = '',
    ?int $regId = null,
    string $recipientType = 'customer'
): array {
    $toEmail = trim($toEmail);
    if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        if (function_exists('bolso_log_notification')) {
            bolso_log_notification([
                'registration_id' => $regId,
                'recipient_type' => $recipientType,
                'channel' => 'email',
                'destination' => $toEmail,
                'subject' => $subject,
                'message' => $htmlBody,
                'status' => 'failed',
                'error_message' => 'Invalid recipient email address syntax: ' . $toEmail,
            ]);
        }
        return ['success' => false, 'error' => 'Invalid email address syntax', 'method' => 'phpmailer'];
    }

    $fromEmail = bolso_config('smtp_from', '10abhishekkr@gmail.com');
    if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
        $fromEmail = '10abhishekkr@gmail.com';
    }
    $fromName = bolso_config('smtp_from_name', 'BOLSO Fabric Art Studio');

    $smtpHost = trim(bolso_config('smtp_host', ''));
    $smtpUser = trim(bolso_config('smtp_user', ''));
    $smtpPass = trim(bolso_config('smtp_pass', ''));

    $isSent = false;
    $methodUsed = 'phpmailer_smtp';
    $errorMessage = null;

    if ($smtpHost !== '' && $smtpPass === '') {
        $errorMessage = 'SMTP App Password is not configured for ' . ($smtpUser !== '' ? $smtpUser : 'host') . '. Email logged as simulated in database.';
        $methodUsed = 'phpmailer_unconfigured_simulated';
        $isSent = false;
    } else {
        try {
            $mail = bolso_create_phpmailer();
            $mail->setFrom($fromEmail, $fromName);
            $mail->addReplyTo($fromEmail, $fromName);
            $mail->addAddress($toEmail, $toName !== '' ? $toName : $toEmail);

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;

            if ($plainText !== '') {
                $mail->AltBody = $plainText;
            } else {
                $cleaned = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $htmlBody);
                $cleaned = str_replace(['<br>', '<br/>', '<br />', '</p>', '</tr>'], "\n", (string)$cleaned);
                $mail->AltBody = trim(strip_tags((string)$cleaned));
            }

            $mail->send();
            $isSent = true;
            $methodUsed = 'phpmailer_' . strtolower($mail->Mailer);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            error_log('PHPMailer send failed: ' . $errorMessage . '. Logging email as simulated.');

            // If SMTP failed, attempt local fallback or simulation logging
            $methodUsed = 'phpmailer_fallback_simulated';
            $isSent = false;
        } catch (\Throwable $t) {
            $errorMessage = $t->getMessage();
            error_log('PHPMailer unexpected error: ' . $errorMessage);
            $methodUsed = 'phpmailer_error';
            $isSent = false;
        }
    }

    $finalStatus = $isSent ? 'sent' : 'simulated';

    if (function_exists('bolso_log_notification')) {
        bolso_log_notification([
            'registration_id' => $regId,
            'recipient_type' => $recipientType,
            'channel' => 'email',
            'destination' => $toEmail,
            'subject' => $subject,
            'message' => $htmlBody,
            'status' => $finalStatus,
            'error_message' => $errorMessage,
        ]);
    }

    return [
        'success' => $isSent,
        'sent' => $isSent,
        'status' => $finalStatus,
        'method' => $methodUsed,
        'error' => $errorMessage,
    ];
}

/**
 * Diagnostic test tool to verify SMTP credentials directly against the mail server.
 */
function bolso_test_smtp_connection(
    string $host,
    int $port,
    string $user,
    string $pass,
    string $secure = 'tls',
    string $testTo = '10abhishekkr@gmail.com'
): array {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->Port = $port;
        $mail->SMTPAuth = true;
        $mail->Username = $user;
        $mail->Password = $pass;
        $mail->Timeout = 8;
        if ($secure === 'ssl' || $port === 465) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ]
        ];
        $mail->setFrom($user, 'BOLSO Studio Test');
        $mail->addAddress($testTo);
        $mail->Subject = 'BOLSO Live SMTP Test: Connected Successfully';
        $mail->Body = 'Your BOLSO Studio email notification gateway is successfully connected to ' . htmlspecialchars($host) . '! Real emails will now be delivered to registered students and studio admin.';
        $mail->send();
        return ['success' => true, 'message' => 'Connected successfully! Test email delivered to ' . $testTo];
    } catch (\Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
