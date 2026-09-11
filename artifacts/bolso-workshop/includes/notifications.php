<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/mailer.php';

/**
 * Clean and format phone number for international WhatsApp.
 * Automatically prefixes Indian 10-digit numbers with 91.
 */
function bolso_clean_phone(string $phone): string
{
    $digits = preg_replace('/\D+/', '', $phone);
    if (strlen($digits) === 10) {
        $digits = '91' . $digits;
    }
    return $digits;
}

/**
 * Ensure notifications_log table exists in database.
 */
function bolso_notification_ensure_table(): bool
{
    $pdo = bolso_db();
    if (!$pdo) return false;

    static $checked = false;
    if ($checked) return true;

    try {
        $pdo->exec(
            "CREATE TABLE IF NOT EXISTS notifications_log (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                registration_id INT UNSIGNED NULL,
                recipient_type ENUM('customer', 'admin') NOT NULL,
                channel ENUM('email', 'whatsapp') NOT NULL,
                destination VARCHAR(190) NOT NULL,
                subject VARCHAR(255) NULL,
                message MEDIUMTEXT NOT NULL,
                status ENUM('sent', 'simulated', 'failed', 'ready') NOT NULL DEFAULT 'sent',
                error_message TEXT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_notif_reg (registration_id),
                INDEX idx_notif_type (recipient_type),
                INDEX idx_notif_channel (channel),
                INDEX idx_notif_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
        );
        @$pdo->exec("ALTER TABLE registrations ADD COLUMN IF NOT EXISTS timing_slot VARCHAR(255) NULL, ADD COLUMN IF NOT EXISTS timing_sent_at DATETIME NULL");
        $checked = true;
        return true;
    } catch (PDOException $e) {
        error_log('BOLSO notifications table creation failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Record notification into database and log file.
 */
function bolso_log_notification(array $data): int
{
    bolso_notification_ensure_table();
    $pdo = bolso_db();

    $regId = !empty($data['registration_id']) ? (int)$data['registration_id'] : null;
    $recipientType = in_array($data['recipient_type'] ?? '', ['customer', 'admin'], true) ? $data['recipient_type'] : 'customer';
    $channel = in_array($data['channel'] ?? '', ['email', 'whatsapp'], true) ? $data['channel'] : 'email';
    $destination = (string)($data['destination'] ?? '');
    $subject = !empty($data['subject']) ? (string)$data['subject'] : null;
    $message = (string)($data['message'] ?? '');
    $status = in_array($data['status'] ?? '', ['sent', 'simulated', 'failed', 'ready'], true) ? $data['status'] : 'sent';
    $error = !empty($data['error_message']) ? (string)$data['error_message'] : null;

    $insertedId = 0;
    if ($pdo) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO notifications_log (registration_id, recipient_type, channel, destination, subject, message, status, error_message)
                 VALUES (:reg_id, :recipient_type, :channel, :destination, :subject, :message, :status, :error)'
            );
            $stmt->execute([
                ':reg_id' => $regId,
                ':recipient_type' => $recipientType,
                ':channel' => $channel,
                ':destination' => $destination,
                ':subject' => $subject,
                ':message' => $message,
                ':status' => $status,
                ':error' => $error,
            ]);
            $insertedId = (int)$pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log('BOLSO notification log db error: ' . $e->getMessage());
        }
    }

    // Append to file log
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0777, true);
    }
    $logFile = $logDir . '/notifications.log';
    $logLine = sprintf(
        "[%s] [%s|%s] To: %s | Status: %s | Subject: %s %s\n",
        date('Y-m-d H:i:s'),
        strtoupper($recipientType),
        strtoupper($channel),
        $destination,
        strtoupper($status),
        $subject ?? '(none)',
        $error ? " | Error: {$error}" : ""
    );
    @file_put_contents($logFile, $logLine, FILE_APPEND);

    return $insertedId;
}

/**
 * Low-level SMTP Socket Sender.
 * Connects directly to SMTP server (e.g. Gmail, Outlook, cPanel) without external libraries.
 */
function bolso_smtp_send(
    string $host,
    int $port,
    string $username,
    string $password,
    string $secure,
    string $fromEmail,
    string $fromName,
    string $toEmail,
    string $subject,
    string $htmlContent
): array {
    $timeout = 15;
    $context = stream_context_create([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ]
    ]);

    $protocol = '';
    if ($secure === 'ssl' || $port === 465) {
        $protocol = 'ssl://';
    }

    $socket = @stream_socket_client(
        $protocol . $host . ':' . $port,
        $errno,
        $errstr,
        $timeout,
        STREAM_CLIENT_CONNECT,
        $context
    );

    if (!$socket) {
        return ['success' => false, 'error' => "Socket connect failed to {$host}:{$port} ({$errno} - {$errstr})"];
    }

    stream_set_timeout($socket, $timeout);

    $readResponse = function() use ($socket) {
        $response = '';
        while ($line = fgets($socket, 515)) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return $response;
    };

    $sendCommand = function(string $cmd) use ($socket, $readResponse) {
        fputs($socket, $cmd . "\r\n");
        return $readResponse();
    };

    $greeting = $readResponse();
    if (!str_starts_with($greeting, '220')) {
        fclose($socket);
        return ['success' => false, 'error' => "SMTP greeting failed: " . trim($greeting)];
    }

    $ehlo = $sendCommand("EHLO " . (gethostname() ?: 'localhost'));

    // If STARTTLS is required (e.g. port 587)
    if (($secure === 'tls' || $port === 587) && str_contains($ehlo, 'STARTTLS')) {
        $starttls = $sendCommand("STARTTLS");
        if (!str_starts_with($starttls, '220')) {
            fclose($socket);
            return ['success' => false, 'error' => "STARTTLS rejected: " . trim($starttls)];
        }
        if (!@stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($socket);
            return ['success' => false, 'error' => "TLS encryption negotiation failed"];
        }
        $sendCommand("EHLO " . (gethostname() ?: 'localhost'));
    }

    // Authenticate if credentials provided
    if ($username !== '' && $password !== '') {
        $auth = $sendCommand("AUTH LOGIN");
        if (!str_starts_with($auth, '334')) {
            fclose($socket);
            return ['success' => false, 'error' => "AUTH LOGIN rejected: " . trim($auth)];
        }
        $uRes = $sendCommand(base64_encode($username));
        if (!str_starts_with($uRes, '334')) {
            fclose($socket);
            return ['success' => false, 'error' => "Username rejected: " . trim($uRes)];
        }
        $pRes = $sendCommand(base64_encode($password));
        if (!str_starts_with($pRes, '235')) {
            fclose($socket);
            return ['success' => false, 'error' => "Password/Authentication rejected: " . trim($pRes)];
        }
    }

    $mailFrom = $sendCommand("MAIL FROM:<{$fromEmail}>");
    if (!str_starts_with($mailFrom, '250')) {
        fclose($socket);
        return ['success' => false, 'error' => "MAIL FROM rejected: " . trim($mailFrom)];
    }

    $rcptTo = $sendCommand("RCPT TO:<{$toEmail}>");
    if (!str_starts_with($rcptTo, '250')) {
        fclose($socket);
        return ['success' => false, 'error' => "RCPT TO rejected: " . trim($rcptTo)];
    }

    $dataCmd = $sendCommand("DATA");
    if (!str_starts_with($dataCmd, '354')) {
        fclose($socket);
        return ['success' => false, 'error' => "DATA command rejected: " . trim($dataCmd)];
    }

    $headers = [
        "From: =?UTF-8?B?" . base64_encode($fromName) . "?= <{$fromEmail}>",
        "To: <{$toEmail}>",
        "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=",
        "MIME-Version: 1.0",
        "Date: " . date('r'),
        "Content-Type: text/html; charset=UTF-8",
        "Content-Transfer-Encoding: 8bit",
        "X-Mailer: BOLSO Studio Notification Engine",
    ];

    $emailData = implode("\r\n", $headers) . "\r\n\r\n" . $htmlContent . "\r\n.";
    $sendData = $sendCommand($emailData);

    $sendCommand("QUIT");
    fclose($socket);

    if (str_starts_with($sendData, '250')) {
        return ['success' => true, 'error' => null];
    }

    return ['success' => false, 'error' => "Message data rejected: " . trim($sendData)];
}

/**
 * Universal Mail Dispatcher using official PHPMailer.
 * Dispatches via PHPMailer SMTP / mail() with automatic database and file logging.
 */
function bolso_send_mail(
    string $toEmail,
    string $toName,
    string $subject,
    string $htmlBody,
    string $plainText = '',
    ?int $regId = null,
    string $recipientType = 'customer'
): array {
    return bolso_send_mail_via_phpmailer($toEmail, $toName, $subject, $htmlBody, $plainText, $regId, $recipientType);
}

/**
 * Universal WhatsApp Notification Dispatcher.
 * Cleans phone number, triggers external API if configured, generates wa.me direct link, and logs message.
 */
function bolso_send_whatsapp(
    string $phone,
    string $message,
    ?int $regId = null,
    string $recipientType = 'customer'
): array {
    $cleanPhone = bolso_clean_phone($phone);
    $waLink = 'https://wa.me/' . $cleanPhone . '?text=' . rawurlencode($message);

    $apiSent = false;
    $apiError = null;

    // A. Check CallMeBot API (free automated WhatsApp for admin phone 9341469219)
    $callmebotKey = bolso_config('callmebot_apikey');
    if ($recipientType === 'admin' && !empty($callmebotKey)) {
        try {
            $cmbUrl = 'https://api.callmebot.com/whatsapp.php?phone=' . urlencode($cleanPhone) . '&text=' . urlencode($message) . '&apikey=' . urlencode($callmebotKey);
            $ch = curl_init($cmbUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 12);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $cmbResp = curl_exec($ch);
            $cmbCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($cmbCode >= 200 && $cmbCode < 300) {
                $apiSent = true;
                $apiError = null;
            } else {
                $apiError = "CallMeBot returned HTTP {$cmbCode}: " . substr((string)$cmbResp, 0, 120);
            }
        } catch (Throwable $t) {
            $apiError = 'CallMeBot exception: ' . $t->getMessage();
        }
    }

    // B. Check Universal WhatsApp gateway API (e.g. UltraMsg, GreenAPI, Twilio)
    if (!$apiSent) {
        $apiUrl = bolso_config('whatsapp_api_url');
        $apiToken = bolso_config('whatsapp_api_token');

        if ($apiUrl !== '' && filter_var($apiUrl, FILTER_VALIDATE_URL)) {
            try {
                $ch = curl_init($apiUrl);
                $payload = [
                    'token' => $apiToken,
                    'to' => '+' . $cleanPhone,
                    'body' => $message,
                    'message' => $message,
                ];
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode >= 200 && $httpCode < 300) {
                    $apiSent = true;
                    $apiError = null;
                } else {
                    $apiError = "Gateway returned HTTP {$httpCode}: " . substr((string)$response, 0, 150);
                }
            } catch (Throwable $t) {
                $apiError = $t->getMessage();
            }
        } elseif (!$apiSent && empty($apiError)) {
            $apiError = 'No WhatsApp API Gateway configured in Settings. wa.me link ready for 1-click dispatch.';
        }
    }

    $status = $apiSent ? 'sent' : 'ready';

    bolso_log_notification([
        'registration_id' => $regId,
        'recipient_type' => $recipientType,
        'channel' => 'whatsapp',
        'destination' => $cleanPhone,
        'subject' => 'WhatsApp Notification',
        'message' => $message,
        'status' => $status,
        'error_message' => $apiError,
    ]);

    return [
        'success' => true,
        'phone' => $cleanPhone,
        'wa_link' => $waLink,
        'api_sent' => $apiSent,
        'error' => $apiError,
    ];
}

/**
 * Generate Customer Email HTML Template.
 */
function bolso_build_customer_email_html(array $data): string
{
    $name = htmlspecialchars($data['name'] ?? 'Friend', ENT_QUOTES, 'UTF-8');
    $workshopTitle = htmlspecialchars($data['workshop_title'] ?? ($data['workshop'] ?? 'Fabric Painting'), ENT_QUOTES, 'UTF-8');
    $mode = htmlspecialchars(ucfirst($data['mode'] ?? 'online'), ENT_QUOTES, 'UTF-8');
    $preferredDate = htmlspecialchars($data['preferred_date'] ?? 'Upcoming Batch', ENT_QUOTES, 'UTF-8');
    $timingSlot = htmlspecialchars($data['timing_slot'] ?? ($data['timing'] ?? 'Morning Batch (10:30 AM – 1:30 PM)'), ENT_QUOTES, 'UTF-8');
    $price = number_format((float)($data['price'] ?? 0));
    $paymentId = htmlspecialchars($data['payment_id'] ?? 'PAID-VERIFIED', ENT_QUOTES, 'UTF-8');
    $regId = (int)($data['id'] ?? ($data['registration_id'] ?? 0));
    $whatsapp = htmlspecialchars($data['whatsapp'] ?? '', ENT_QUOTES, 'UTF-8');
    $studioWhatsApp = bolso_config('admin_whatsapp', '919341469219');
    $isPayAtStudio = (($data['payment_method'] ?? '') === 'offline') || ($paymentId === 'PAY_AT_STUDIO');

    $badgeHtml = $isPayAtStudio 
        ? '<span class="badge-confirmed" style="background: #fff3e0; color: #b45309; border: 1px solid #fed7aa;">✓ Spot Reserved · Pay at Studio on Day 1</span>'
        : '<span class="badge-confirmed">✓ Payment Done · Spot Confirmed</span>';

    $pageTitle = $isPayAtStudio ? 'Spot Reserved - BOLSO Workshop' : 'Payment Confirmed - BOLSO Workshop';

    $introHtml = $isPayAtStudio
        ? "We are delighted to confirm that your in-person spot for the <strong>{$workshopTitle}</strong> (Offline Studio Batch) is officially secured! Since you opted to <strong>Pay at Studio</strong>, your workshop fee of <strong>₹{$price}</strong> will be collected in Cash or via UPI directly at our Kolkata studio upon arrival on Day 1."
        : "Thank you so much for choosing BOLSO! We are delighted to confirm that your payment of <strong>₹{$price}</strong> has been received successfully. Your registration for the <strong>{$workshopTitle}</strong> is officially secured.";

    $venueHtml = ($isPayAtStudio || strtolower($data['mode'] ?? '') === 'offline')
        ? '<div style="background: #fdfaf5; border-left: 4px solid #d46d47; border-radius: 8px; padding: 18px 22px; margin: 24px 0;">
            <h3 style="margin: 0 0 8px 0; font-size: 16px; color: #8e232e;">📍 Studio Location &amp; Venue</h3>
            <p style="margin: 0 0 8px 0; font-size: 14px; line-height: 1.55; color: #2e3842;">
                <strong>Jayanti Abasan, Jhowtala Hatiara, Near Lokenath Mandir, Chinar Park, Kolkata - 700157</strong><br>
                <span style="font-size: 12.5px; color: #78716c;">All fabric canvases, soft-touch pigments, fine brushes, and curated artist colour palettes will be prepared for you at the studio.</span>
            </p>
            <div style="margin-top: 10px;">
                <a href="https://maps.google.com/?q=Jayanti+Abasan+Jhowtala+Hatiara+Chinar+Park+Kolkata+700157" target="_blank" style="display: inline-block; background: #8e232e; color: #ffffff; text-decoration: none; font-size: 12px; font-weight: 600; padding: 8px 16px; border-radius: 6px;">
                    Open Studio in Google Maps →
                </a>
            </div>
           </div>'
        : '<div style="background: #f0f7ff; border-left: 4px solid #2563eb; border-radius: 8px; padding: 18px 22px; margin: 24px 0;">
            <h3 style="margin: 0 0 8px 0; font-size: 16px; color: #1d4ed8;">💻 Online Live Studio Access</h3>
            <p style="margin: 0; font-size: 14px; line-height: 1.55; color: #2e3842;">
                <strong>Interactive live session via Google Meet / Zoom</strong><br>
                <span style="font-size: 12.5px; color: #64748b;">The private class link and material prep guide will be sent directly to your WhatsApp (' . $whatsapp . ') and email before the session starts.</span>
            </p>
           </div>';

    $statusVal = $isPayAtStudio 
        ? '<span style="color: #b45309; font-weight: 700;">Pay at Studio (Cash / UPI on Arrival)</span>'
        : '<span style="color: #2e7d32; font-weight: 700;">Paid &amp; Confirmed ✓</span>';

    $paymentMethodRow = $isPayAtStudio
        ? '<tr><td class="label">Payment Option</td><td class="value">Offline (Pay at Studio on Day 1)</td></tr>'
        : '<tr><td class="label">Payment Option</td><td class="value">Online (UPI / Card / NetBanking)</td></tr>';

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$pageTitle}</title>
<style>
    body { margin: 0; padding: 0; background-color: #f6f0e6; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; color: #172432; }
    .email-container { max-width: 600px; margin: 30px auto; background: #ffffff; border-radius: 18px; overflow: hidden; box-shadow: 0 12px 35px rgba(23, 36, 50, 0.08); border: 1px solid #e7ded0; }
    .email-header { background: #8e232e; padding: 32px 30px; text-align: center; color: #fbf8f1; }
    .email-header h1 { margin: 0; font-family: "Georgia", serif; font-size: 28px; letter-spacing: -0.02em; font-weight: normal; }
    .email-header p { margin: 6px 0 0; font-size: 11px; letter-spacing: 0.18em; text-transform: uppercase; color: #e5b352; font-weight: 600; }
    .email-body { padding: 36px 32px; }
    .badge-confirmed { display: inline-block; background: #e8f5e9; color: #2e7d32; font-size: 11px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; padding: 6px 14px; border-radius: 999px; margin-bottom: 16px; }
    h2.greeting { font-family: "Georgia", serif; font-size: 26px; margin: 0 0 14px 0; color: #172432; }
    p.intro { font-size: 16px; line-height: 1.6; color: #445466; margin: 0 0 24px 0; }
    
    /* Timing callout box */
    .timing-box { background: #fff9ed; border-left: 4px solid #c97a3e; border-radius: 8px; padding: 18px 22px; margin: 26px 0; }
    .timing-box h3 { margin: 0 0 8px 0; font-size: 16px; color: #8e232e; display: flex; align-items: center; gap: 8px; }
    .timing-box p { margin: 0; font-size: 14.5px; line-height: 1.55; color: #2e3842; }

    .details-table { width: 100%; border-collapse: collapse; margin: 26px 0; border: 1px solid #efe7db; border-radius: 10px; overflow: hidden; }
    .details-table tr { border-bottom: 1px solid #efe7db; }
    .details-table tr:last-child { border-bottom: none; }
    .details-table td { padding: 13px 18px; font-size: 14px; }
    .details-table td.label { width: 38%; color: #6a7c92; font-weight: 500; background: #faf7f2; }
    .details-table td.value { width: 62%; color: #172432; font-weight: 600; }

    .contact-card { background: #faf7f2; border: 1px solid #efe7db; border-radius: 10px; padding: 20px 22px; margin: 26px 0; }
    .contact-card h4 { margin: 0 0 8px 0; font-size: 15px; color: #8e232e; }
    .contact-card p { margin: 0 0 10px 0; font-size: 13.5px; color: #496174; line-height: 1.5; }
    .contact-card ul { margin: 0; padding-left: 20px; font-size: 13px; color: #1f2d3d; line-height: 1.7; }

    .btn-wrap { text-align: center; margin: 28px 0 16px; }
    .btn-whatsapp { display: inline-block; background: #25d366; color: #ffffff !important; text-decoration: none; font-size: 15px; font-weight: 600; padding: 14px 28px; border-radius: 999px; box-shadow: 0 4px 14px rgba(37, 211, 102, 0.3); }

    .email-footer { background: #faf7f2; padding: 24px 30px; text-align: center; font-size: 12px; color: #8091a5; border-top: 1px solid #efe7db; }
    .email-footer p { margin: 4px 0; }
</style>
</head>
<body>
<div class="email-container">
    <div class="email-header">
        <h1>BOLSO</h1>
        <p>Art · Emotion · Fashion</p>
    </div>
    <div class="email-body">
        {$badgeHtml}
        <h2 class="greeting">Thank you, {$name}!</h2>
        <p class="intro">
            {$introHtml}
        </p>

        {$venueHtml}

        <!-- Explicit timing slot details -->
        <div class="timing-box">
            <h3>🕒 Workshop Timing &amp; Batch Schedule</h3>
            <p>
                <strong>Selected Slot: {$timingSlot}</strong><br>
                Our studio instructors are preparing your batch and will reach out to you directly on WhatsApp (<strong>{$whatsapp}</strong>) and email with your final orientation guide and timing confirmation.
            </p>
        </div>

        <table class="details-table">
            <tr>
                <td class="label">Registration ID</td>
                <td class="value">#{$regId}</td>
            </tr>
            <tr>
                <td class="label">Workshop Tier</td>
                <td class="value">{$workshopTitle}</td>
            </tr>
            <tr>
                <td class="label">Learning Mode</td>
                <td class="value">{$mode}</td>
            </tr>
            <tr>
                <td class="label">Preferred Batch Date</td>
                <td class="value">{$preferredDate}</td>
            </tr>
            <tr>
                <td class="label">Timing Slot</td>
                <td class="value"><strong>{$timingSlot}</strong></td>
            </tr>
            {$paymentMethodRow}
            <tr>
                <td class="label">Payment Reference</td>
                <td class="value" style="font-family: monospace; font-size: 13px;">{$paymentId}</td>
            </tr>
            <tr>
                <td class="label">Payment Status</td>
                <td class="value">{$statusVal}</td>
            </tr>
        </table>

        <!-- Admin Contact Details -->
        <div class="contact-card">
            <h4>📞 Need Assistance or Have Questions?</h4>
            <p>Our Studio Admin is available anytime to assist with directions, scheduling, or material queries:</p>
            <ul>
                <li><strong>Admin Email:</strong> <a href="mailto:10abhishekkr@gmail.com" style="color: #8e232e;">10abhishekkr@gmail.com</a></li>
                <li><strong>WhatsApp Helpline:</strong> <a href="https://wa.me/{$studioWhatsApp}" style="color: #25d366;">+{$studioWhatsApp}</a></li>
                <li><strong>Studio Address:</strong> Jayanti Abasan, Jhowtala Hatiara, Chinar Park, Kolkata - 700157</li>
            </ul>
        </div>

        <div class="btn-wrap">
            <a class="btn-whatsapp" href="https://wa.me/{$studioWhatsApp}?text=Hello%20BOLSO!%20My%20spot%20is%20reserved%20for%20workshop%20reg%20%23{$regId}.%20Looking%20forward%20to%20my%20session." target="_blank">
                Chat with Studio on WhatsApp →
            </a>
        </div>
    </div>
    <div class="email-footer">
        <p><strong>BOLSO Fabric Art Studio</strong></p>
        <p>Small-batch fabric painting workshops · Personal guidance · Joyful practice</p>
        <p>WhatsApp Helpline: +{$studioWhatsApp} · Email: 10abhishekkr@gmail.com</p>
    </div>
</div>
</body>
</html>
HTML;
}

/**
 * Generate Admin Email HTML Template.
 * Sent directly to 10abhishekkr@gmail.com.
 */
function bolso_build_admin_email_html(array $data): string
{
    $name = htmlspecialchars($data['name'] ?? 'Student', ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($data['email'] ?? 'Not provided', ENT_QUOTES, 'UTF-8');
    $whatsapp = htmlspecialchars($data['whatsapp'] ?? 'Not provided', ENT_QUOTES, 'UTF-8');
    $cleanWa = bolso_clean_phone($data['whatsapp'] ?? '');
    $workshopTitle = htmlspecialchars($data['workshop_title'] ?? ($data['workshop'] ?? '2-Day Workshop'), ENT_QUOTES, 'UTF-8');
    $mode = htmlspecialchars(ucfirst($data['mode'] ?? 'online'), ENT_QUOTES, 'UTF-8');
    $preferredDate = htmlspecialchars($data['preferred_date'] ?? 'Upcoming Batch', ENT_QUOTES, 'UTF-8');
    $timingSlot = htmlspecialchars($data['timing_slot'] ?? ($data['timing'] ?? 'Not specified'), ENT_QUOTES, 'UTF-8');
    $price = number_format((float)($data['price'] ?? 0));
    $paymentId = htmlspecialchars($data['payment_id'] ?? 'VERIFIED_PAYMENT', ENT_QUOTES, 'UTF-8');
    $regId = (int)($data['id'] ?? ($data['registration_id'] ?? 0));
    $interest = htmlspecialchars($data['interest'] ?? 'Fabric painting', ENT_QUOTES, 'UTF-8');
    $experience = htmlspecialchars($data['experience'] ?? 'Not specified', ENT_QUOTES, 'UTF-8');
    $notes = htmlspecialchars($data['message'] ?? 'None', ENT_QUOTES, 'UTF-8');
    $isPayAtStudio = (($data['payment_method'] ?? '') === 'offline') || ($paymentId === 'PAY_AT_STUDIO');

    $headerStyle = $isPayAtStudio ? 'background: #172432;' : 'background: #8e232e;';
    $eyebrowText = $isPayAtStudio ? 'BOLSO Studio Alert · Offline Booking' : 'BOLSO Studio Alert';
    $eyebrowColor = $isPayAtStudio ? '#f59e0b' : '#e5b352';
    $headerTitle = $isPayAtStudio ? "📍 Pay at Studio: ₹{$price} to Collect" : "💰 Payment Received: ₹{$price}";
    
    $paymentRow = $isPayAtStudio
        ? '<tr><td class="lbl">Payment Option</td><td class="val"><strong>Offline · Pay at Studio on Day 1</strong></td></tr>
           <tr><td class="lbl">Amount to Collect</td><td class="val" style="color: #b45309; font-size: 16px; font-weight: bold;">₹' . $price . ' (Collect on Arrival)</td></tr>'
        : '<tr><td class="lbl">Payment Option</td><td class="val">Online (Razorpay / UPI / Cards)</td></tr>
           <tr><td class="lbl">Amount Received</td><td class="val" style="color: #2e7d32; font-size: 16px;">₹' . $price . ' (Verified Paid)</td></tr>';

    $actionNotice = $isPayAtStudio
        ? '<div class="action-notice" style="background: #fff8e1; border-left: 4px solid #f59e0b; color: #78350f;">
            <strong>👉 Action Required:</strong> Contact ' . $name . ' to coordinate their workshop timing (Slot: <strong>' . $timingSlot . '</strong>), and collect <strong>₹' . $price . '</strong> (Cash/UPI) upon arrival at the studio on Day 1!
           </div>'
        : '<div class="action-notice">
            <strong>👉 Action Required:</strong> Please contact ' . $name . ' on WhatsApp (+91 ' . $cleanWa . ') to confirm their workshop timing (Slot: <strong>' . $timingSlot . '</strong>)!
           </div>';

    $waDirectLink = $isPayAtStudio
        ? 'https://wa.me/' . $cleanWa . '?text=' . rawurlencode("Hello {$data['name']}! Thank you for registering for the BOLSO {$workshopTitle} (Offline Studio Batch). Your spot is reserved (Pay ₹{$price} at Studio). Your chosen slot is {$timingSlot}. Here are your session details: ")
        : 'https://wa.me/' . $cleanWa . '?text=' . rawurlencode("Hello {$data['name']}! Thank you for registering for the BOLSO {$workshopTitle} workshop. Your payment of ₹{$price} is received. Your chosen slot is {$timingSlot}. Here are your session details: ");

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Alert: {$headerTitle} - BOLSO</title>
<style>
    body { margin: 0; padding: 0; background-color: #172432; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
    .admin-container { max-width: 620px; margin: 30px auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 16px 40px rgba(0,0,0,0.35); }
    .admin-header { {$headerStyle} padding: 28px 30px; color: #ffffff; }
    .admin-header .eyebrow { font-size: 11px; text-transform: uppercase; letter-spacing: 0.18em; color: {$eyebrowColor}; font-weight: 700; margin-bottom: 6px; }
    .admin-header h1 { margin: 0; font-size: 24px; font-weight: 700; }
    .admin-body { padding: 32px 30px; color: #172432; }

    .highlight-card { background: #fdfaf5; border: 1.5px solid #d46d47; border-radius: 12px; padding: 20px; margin-bottom: 26px; }
    .highlight-card h3 { margin: 0 0 12px 0; font-size: 16px; color: #8e232e; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700; }
    .customer-item { margin-bottom: 8px; font-size: 15px; }
    .customer-item strong { color: #556577; width: 140px; display: inline-block; }

    .info-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; }
    .info-table td { padding: 11px 14px; border-bottom: 1px solid #eee; font-size: 14px; }
    .info-table td.lbl { width: 40%; color: #6a7c92; font-weight: 500; background: #fbfbfb; }
    .info-table td.val { width: 60%; font-weight: 600; color: #172432; }

    .action-notice { background: #e8f5e9; border-left: 4px solid #2e7d32; padding: 16px; border-radius: 6px; margin: 24px 0; font-size: 14px; color: #1b5e20; }
    
    .button-group { margin-top: 24px; }
    .btn-wa { background: #25d366; color: #ffffff !important; padding: 12px 22px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px; display: inline-block; margin-right: 10px; margin-bottom: 10px; }
    .btn-admin { background: #8e232e; color: #ffffff !important; padding: 12px 22px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px; display: inline-block; }

    .admin-footer { background: #f4f6f8; padding: 18px 30px; text-align: center; font-size: 12px; color: #78889b; border-top: 1px solid #e2e8f0; }
</style>
</head>
<body>
<div class="admin-container">
    <div class="admin-header">
        <div class="eyebrow">{$eyebrowText}</div>
        <h1>{$headerTitle}</h1>
    </div>
    <div class="admin-body">
        <div class="highlight-card">
            <h3>👤 Student / User Information</h3>
            <div class="customer-item"><strong>Full Name:</strong> <span style="font-size: 17px; font-weight: bold; color: #172432;">{$name}</span></div>
            <div class="customer-item"><strong>Email:</strong> <a href="mailto:{$email}" style="color: #8e232e; font-weight: bold;">{$email}</a></div>
            <div class="customer-item"><strong>WhatsApp No:</strong> <a href="https://wa.me/{$cleanWa}" style="color: #25d366; font-weight: bold;">+{$cleanWa} ({$whatsapp})</a></div>
        </div>

        <table class="info-table">
            <tr>
                <td class="lbl">Workshop Chosen</td>
                <td class="val">{$workshopTitle}</td>
            </tr>
            <tr>
                <td class="lbl">Learning Mode</td>
                <td class="val">{$mode}</td>
            </tr>
            <tr>
                <td class="lbl">Preferred Batch Date</td>
                <td class="val">{$preferredDate}</td>
            </tr>
            <tr>
                <td class="lbl">Timing Slot Chosen</td>
                <td class="val" style="color: #8e232e; font-weight: bold;">{$timingSlot}</td>
            </tr>
            {$paymentRow}
            <tr>
                <td class="lbl">Payment Reference</td>
                <td class="val" style="font-family: monospace;">{$paymentId}</td>
            </tr>
            <tr>
                <td class="lbl">Registration ID</td>
                <td class="val">#{$regId}</td>
            </tr>
            <tr>
                <td class="lbl">Canvas / Interest</td>
                <td class="val">{$interest}</td>
            </tr>
            <tr>
                <td class="lbl">Prior Experience</td>
                <td class="val">{$experience}</td>
            </tr>
            <tr>
                <td class="lbl">Student Vision / Note</td>
                <td class="val">{$notes}</td>
            </tr>
        </table>

        {$actionNotice}

        <div class="button-group">
            <a class="btn-wa" href="{$waDirectLink}" target="_blank">
                📱 Message {$name} on WhatsApp
            </a>
            <a class="btn-admin" href="http://localhost/bolso-workshop/admin/registrations.php" target="_blank">
                📊 Open Registrations Manager
            </a>
        </div>
    </div>
    <div class="admin-footer">
        Automated alert for BOLSO Studio Admin (10abhishekkr@gmail.com · 9341469219)
    </div>
</div>
</body>
</html>
HTML;
}

/**
 * Generate Customer WhatsApp Message Text.
 */
function bolso_build_customer_whatsapp_text(array $data): string
{
    $name = trim((string)($data['name'] ?? 'there'));
    $workshopTitle = trim((string)($data['workshop_title'] ?? ($data['workshop'] ?? 'Fabric Painting')));
    $mode = ucfirst(trim((string)($data['mode'] ?? 'online')));
    $price = number_format((float)($data['price'] ?? 0));
    $preferredDate = trim((string)($data['preferred_date'] ?? 'Upcoming Batch'));
    $regId = (int)($data['id'] ?? ($data['registration_id'] ?? 0));
    $paymentId = trim((string)($data['payment_id'] ?? ''));
    $isPayAtStudio = (($data['payment_method'] ?? '') === 'offline') || ($paymentId === 'PAY_AT_STUDIO');

    if ($isPayAtStudio) {
        return "Hello *{$name}*! 🎨\n\n" .
               "Thank you for registering for the *BOLSO {$workshopTitle}* (Offline Studio Batch)!\n" .
               "Your in-person spot is successfully *reserved*! ✨\n\n" .
               "📋 *Booking Summary:*\n" .
               "• Registration ID: #{$regId}\n" .
               "• Workshop: {$workshopTitle} ({$mode})\n" .
               "• Preferred Date: {$preferredDate}\n" .
               "• Payment Option: *Pay at Studio (Cash / UPI on Day 1)*\n" .
               "• Fee to Pay on Arrival: *₹{$price}*\n\n" .
               "📍 *Studio Venue:*\n" .
               "Jayanti Abasan, Jhowtala Hatiara, Near Lokenath Mandir, Chinar Park, Kolkata - 700157\n" .
               "(All premium canvases, pigments, and palettes will be provided at the studio!)\n\n" .
               "🕒 *Schedule & Timing:*\n" .
               "*Your timing for the workshop will be given to you very soon!*\n" .
               "Our studio team will message you shortly with the exact timings and batch details.\n\n" .
               "If you have any questions, feel free to reply right here!\n\n" .
               "Warm regards,\n" .
               "*BOLSO Fabric Art Studio*\n" .
               "_Art · Emotion · Fashion_";
    }

    return "Hello *{$name}*! 🎨\n\n" .
           "Thank you for registering for the *BOLSO {$workshopTitle}*!\n" .
           "We are delighted to confirm that your payment of *₹{$price}* has been successfully received. Your spot is secured! ✨\n\n" .
           "📋 *Booking Summary:*\n" .
           "• Registration ID: #{$regId}\n" .
           "• Workshop: {$workshopTitle} ({$mode})\n" .
           "• Preferred Date: {$preferredDate}\n" .
           "• Payment Status: *Paid & Confirmed* ✓\n\n" .
           "🕒 *Schedule & Timing:*\n" .
           "*Your timing for the workshop will be given to you very soon!*\n" .
           "Our studio team is organizing the upcoming batch slots and will message you shortly with the exact timings and session details.\n\n" .
           "If you have any questions in the meantime, feel free to reply right here!\n\n" .
           "Warm regards,\n" .
           "*BOLSO Fabric Art Studio*\n" .
           "_Art · Emotion · Fashion_";
}

/**
 * Generate Admin WhatsApp Alert Text.
 * Destination: 9341469219
 */
function bolso_build_admin_whatsapp_text(array $data): string
{
    $name = trim((string)($data['name'] ?? 'Student'));
    $email = trim((string)($data['email'] ?? 'Not provided'));
    $whatsapp = trim((string)($data['whatsapp'] ?? 'Not provided'));
    $cleanWa = bolso_clean_phone($whatsapp);
    $workshopTitle = trim((string)($data['workshop_title'] ?? ($data['workshop'] ?? 'Workshop')));
    $mode = ucfirst(trim((string)($data['mode'] ?? 'online')));
    $price = number_format((float)($data['price'] ?? 0));
    $preferredDate = trim((string)($data['preferred_date'] ?? 'Upcoming Batch'));
    $paymentId = trim((string)($data['payment_id'] ?? 'VERIFIED_PAID'));
    $regId = (int)($data['id'] ?? ($data['registration_id'] ?? 0));
    $interest = trim((string)($data['interest'] ?? 'General'));
    $isPayAtStudio = (($data['payment_method'] ?? '') === 'offline') || ($paymentId === 'PAY_AT_STUDIO');

    if ($isPayAtStudio) {
        return "🔔 *NEW OFFLINE REGISTRATION (PAY AT STUDIO) - BOLSO WORKSHOP!*\n\n" .
               "📍 *Payment Option:* Pay at Studio on Arrival\n" .
               "💵 *Amount to Collect:* ₹{$price} (Cash / UPI on Day 1)\n" .
               "🎨 *Workshop:* {$workshopTitle} ({$mode})\n" .
               "📅 *Preferred Date:* {$preferredDate}\n\n" .
               "👤 *CUSTOMER DETAILS:*\n" .
               "• Name: *{$name}*\n" .
               "• Email: {$email}\n" .
               "• WhatsApp: +{$cleanWa} ({$whatsapp})\n" .
               "• Painting Interest: {$interest}\n" .
               "• Reg ID: #{$regId}\n" .
               "• Payment Ref: {$paymentId}\n\n" .
               "👉 *ACTION NEEDED:*\n" .
               "1. Contact {$name} with their workshop timing soon.\n" .
               "2. Collect ₹{$price} upon their arrival at studio on Day 1.\n" .
               "Quick chat link: https://wa.me/{$cleanWa}";
    }

    return "🔔 *NEW PAYMENT RECEIVED - BOLSO WORKSHOP!*\n\n" .
           "💰 *Amount Received:* ₹{$price}\n" .
           "🎨 *Workshop:* {$workshopTitle} ({$mode})\n" .
           "📅 *Preferred Date:* {$preferredDate}\n\n" .
           "👤 *CUSTOMER DETAILS:*\n" .
           "• Name: *{$name}*\n" .
           "• Email: {$email}\n" .
           "• WhatsApp: +{$cleanWa} ({$whatsapp})\n" .
           "• Painting Interest: {$interest}\n" .
           "• Reg ID: #{$regId}\n" .
           "• Payment ID: {$paymentId}\n\n" .
           "👉 *ACTION NEEDED:*\n" .
           "Please contact {$name} to provide their workshop timing soon!\n" .
           "Quick chat link: https://wa.me/{$cleanWa}";
}

/**
 * Master Notification Coordinator.
 * Dispatches Customer Email + WhatsApp & Admin Email + WhatsApp upon payment success.
 */
function bolso_notify_payment_success(array $registration, string $paymentId = ''): array
{
    $pdo = bolso_db();

    // Enrich registration details if missing workshop title
    if (!isset($registration['workshop_title']) && $pdo) {
        try {
            $stmt = $pdo->prepare('SELECT title FROM workshops WHERE slug = :slug LIMIT 1');
            $stmt->execute([':slug' => $registration['workshop'] ?? '2-day']);
            $w = $stmt->fetch();
            if ($w && !empty($w['title'])) {
                $registration['workshop_title'] = $w['title'];
            }
        } catch (PDOException $e) {
            // ignore
        }
    }
    if (empty($registration['workshop_title'])) {
        $registration['workshop_title'] = (($registration['workshop'] ?? '') === '5-day') ? '5-Day Workshop' : '2-Day Workshop';
    }

    $registration['payment_id'] = $paymentId ?: ($registration['payment_id'] ?? 'VERIFIED_PAID');
    $regId = !empty($registration['id']) ? (int)$registration['id'] : null;
    $isPayAtStudio = (($registration['payment_method'] ?? '') === 'offline') || ($registration['payment_id'] === 'PAY_AT_STUDIO');

    $adminEmail = bolso_config('admin_email', '10abhishekkr@gmail.com');
    $adminPhone = bolso_config('admin_whatsapp', '919341469219');

    // 1. Customer Email
    $customerSubject = $isPayAtStudio
        ? 'Spot Reserved: Your BOLSO Studio Workshop Spot is Confirmed! (ID: #' . ($regId ?: 'NEW') . ')'
        : 'Payment Confirmed: Your BOLSO Workshop Spot is Secured! (ID: #' . ($regId ?: 'NEW') . ')';
    $customerHtml = bolso_build_customer_email_html($registration);
    $customerEmailResult = bolso_send_mail(
        $registration['email'],
        $registration['name'],
        $customerSubject,
        $customerHtml,
        '',
        $regId,
        'customer'
    );

    // 2. Admin Email (to 10abhishekkr@gmail.com)
    $adminSubject = $isPayAtStudio
        ? '📍 Spot Reserved (Pay at Studio): ₹' . number_format((float)$registration['price']) . ' to collect from ' . $registration['name'] . ' (' . $registration['workshop_title'] . ')'
        : '🔔 Payment Received: ₹' . number_format((float)$registration['price']) . ' from ' . $registration['name'] . ' (' . $registration['workshop_title'] . ')';
    $adminHtml = bolso_build_admin_email_html($registration);
    $adminEmailResult = bolso_send_mail(
        $adminEmail,
        'BOLSO Admin',
        $adminSubject,
        $adminHtml,
        '',
        $regId,
        'admin'
    );

    // 3. Customer WhatsApp
    $customerWaText = bolso_build_customer_whatsapp_text($registration);
    $customerWaResult = bolso_send_whatsapp(
        $registration['whatsapp'],
        $customerWaText,
        $regId,
        'customer'
    );

    // 4. Admin WhatsApp (to 9341469219)
    $adminWaText = bolso_build_admin_whatsapp_text($registration);
    $adminWaResult = bolso_send_whatsapp(
        $adminPhone,
        $adminWaText,
        $regId,
        'admin'
    );

    return [
        'customer_email' => $customerEmailResult,
        'admin_email' => $adminEmailResult,
        'customer_whatsapp' => $customerWaResult,
        'admin_whatsapp' => $adminWaResult,
        'customer_whatsapp_link' => $customerWaResult['wa_link'] ?? '',
        'admin_whatsapp_link' => $adminWaResult['wa_link'] ?? '',
    ];
}

/**
 * Generate Customer Workshop Timing Email HTML.
 */
function bolso_build_customer_timing_email_html(array $data, string $timingSlot, string $venueOrLink = '', string $notes = ''): string
{
    $name = htmlspecialchars($data['name'] ?? 'Friend', ENT_QUOTES, 'UTF-8');
    $workshopTitle = htmlspecialchars($data['workshop_title'] ?? ($data['workshop'] ?? 'Fabric Painting'), ENT_QUOTES, 'UTF-8');
    $mode = htmlspecialchars(ucfirst($data['mode'] ?? 'online'), ENT_QUOTES, 'UTF-8');
    $timing = htmlspecialchars($timingSlot, ENT_QUOTES, 'UTF-8');
    $venue = htmlspecialchars($venueOrLink ?: (($data['mode'] ?? '') === 'offline' ? 'BOLSO Art Studio, Indiranagar, Bengaluru' : 'Online Session via Google Meet (link will be activated 15 mins before start)'), ENT_QUOTES, 'UTF-8');
    $notesClean = htmlspecialchars($notes ?: 'Please keep your fabric canvas, acrylic/fabric paints, brushes (round #2, #4, flat #8), palette, and clean water cup ready.', ENT_QUOTES, 'UTF-8');
    $regId = (int)($data['id'] ?? ($data['registration_id'] ?? 0));
    $studioPhone = bolso_config('admin_whatsapp', '919341469219');
    $waChatLink = 'https://wa.me/' . bolso_clean_phone($studioPhone) . '?text=' . urlencode("Hi BOLSO Studio, I have a question regarding my workshop timing for Reg #{$regId}");

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your BOLSO Workshop Timing</title>
<style>
    body { margin:0; padding:0; background-color:#FBF7F0; font-family:-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; color:#1C1917; }
    .email-container { max-width:600px; margin:30px auto; background:#FFFFFF; border-radius:16px; overflow:hidden; border:1px solid #E7E5E4; box-shadow:0 10px 30px rgba(0,0,0,0.06); }
    .header { background:linear-gradient(135deg, #1C1917 0%, #292524 100%); padding:36px 32px; text-align:center; color:#FFFFFF; }
    .brand-mark { display:inline-block; width:44px; height:44px; line-height:44px; background:#E06D53; color:#FFFFFF; font-weight:700; font-size:22px; border-radius:10px; margin-bottom:12px; }
    .brand-title { font-size:22px; font-weight:700; letter-spacing:2px; margin:0 0 6px 0; text-transform:uppercase; }
    .brand-sub { font-size:13px; color:#D6D3D1; letter-spacing:1px; margin:0; text-transform:uppercase; }
    .content { padding:36px 32px; }
    .badge-pill { display:inline-block; padding:6px 16px; background:#ECFDF5; color:#065F46; border:1px solid #A7F3D0; border-radius:20px; font-size:12px; font-weight:700; letter-spacing:0.5px; text-transform:uppercase; margin-bottom:18px; }
    h1 { font-size:24px; font-weight:700; color:#1C1917; margin:0 0 14px 0; line-height:1.3; }
    p { font-size:15px; line-height:1.6; color:#44403C; margin:0 0 16px 0; }
    .timing-card { background:#F0FDF4; border:2px solid #86EFAC; border-radius:12px; padding:22px 24px; margin:24px 0; }
    .timing-label { font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#166534; margin-bottom:6px; }
    .timing-val { font-size:20px; font-weight:800; color:#14532D; margin-bottom:8px; }
    .details-table { width:100%; border-collapse:collapse; margin:20px 0; background:#FAFAF9; border-radius:12px; overflow:hidden; border:1px solid #E7E5E4; }
    .details-table td { padding:14px 18px; font-size:14px; border-bottom:1px solid #E7E5E4; }
    .details-table tr:last-child td { border-bottom:none; }
    .lbl { color:#78716C; font-weight:600; width:38%; }
    .val { color:#1C1917; font-weight:700; }
    .materials-box { background:#FFFBEB; border-left:4px solid #F59E0B; padding:16px 18px; border-radius:4px; margin:22px 0; }
    .materials-title { font-weight:700; color:#92400E; margin-bottom:6px; font-size:14px; }
    .materials-text { color:#78350F; font-size:13px; line-height:1.5; margin:0; }
    .btn-container { text-align:center; margin:32px 0 16px 0; }
    .btn-wa { display:inline-block; padding:12px 24px; background:#25D366; color:#FFFFFF !important; text-decoration:none; font-weight:700; font-size:13px; border-radius:8px; }
    .footer { background:#F5F5F4; padding:24px 32px; text-align:center; font-size:12px; color:#78716C; border-top:1px solid #E7E5E4; }
</style>
</head>
<body>
<div class="email-container">
    <div class="header">
        <h1 class="brand-title">BOLSO</h1>
        <p class="brand-sub">Art · Emotion · Fashion</p>
    </div>
    <div class="content">
        <span class="badge-pill">✓ Workshop Timing Confirmed</span>
        <h1>Hello {$name}, here is your workshop schedule!</h1>
        <p>We are delighted to confirm that your batch timing has been finalized for the <strong>{$workshopTitle}</strong>.</p>

        <div class="timing-card">
            <div class="timing-label">Confirmed Timing & Schedule</div>
            <div class="timing-val">🕒 {$timing}</div>
            <div style="font-size:13px; color:#166534;">Please ensure you are ready 10 minutes prior to session start.</div>
        </div>

        <table class="details-table">
            <tr>
                <td class="lbl">Workshop</td>
                <td class="val">{$workshopTitle} ({$mode})</td>
            </tr>
            <tr>
                <td class="lbl">Registration ID</td>
                <td class="val">#{$regId}</td>
            </tr>
            <tr>
                <td class="lbl">Location / Platform</td>
                <td class="val">{$venue}</td>
            </tr>
        </table>

        <div class="materials-box">
            <div class="materials-title">🎨 Important Note & Session Checklist:</div>
            <p class="materials-text">{$notesClean}</p>
        </div>

        <div class="btn-container">
            <a class="btn-wa" href="{$waChatLink}" target="_blank">
                📱 Message Studio Support on WhatsApp
            </a>
        </div>
    </div>
    <div class="footer">
        <p style="margin:0 0 6px 0;"><strong>BOLSO Workshop Studio</strong> · Bengaluru, India</p>
        <p style="margin:0;">Support WhatsApp: +91 9341469219 · Email: 10abhishekkr@gmail.com</p>
    </div>
</div>
</body>
</html>
HTML;
}

/**
 * Generate Customer WhatsApp Message Text for Timing.
 */
function bolso_build_customer_timing_whatsapp_text(array $data, string $timingSlot, string $venueOrLink = '', string $notes = ''): string
{
    $name = trim((string)($data['name'] ?? 'there'));
    $workshopTitle = trim((string)($data['workshop_title'] ?? ($data['workshop'] ?? 'Fabric Painting')));
    $mode = ucfirst(trim((string)($data['mode'] ?? 'online')));
    $regId = (int)($data['id'] ?? ($data['registration_id'] ?? 0));
    $venue = trim($venueOrLink ?: (($data['mode'] ?? '') === 'offline' ? 'BOLSO Art Studio, Indiranagar, Bengaluru' : 'Online Session via Google Meet'));

    $msg = "Hello *{$name}*! 🎨\n\n" .
           "Great news! Your schedule for the *BOLSO {$workshopTitle}* has been finalized:\n\n" .
           "🕒 *Session Timing:*\n" .
           "*{$timingSlot}*\n\n" .
           "📍 *Venue / Mode:*\n{$venue} ({$mode})\n\n" .
           "📋 *Booking ID:* #{$regId}\n\n";

    if (!empty($notes)) {
        $msg .= "📝 *Checklist & Note:*\n{$notes}\n\n";
    }

    $msg .= "We look forward to having you create with us! If you need any assistance, feel free to reply right here.\n\n" .
            "Warm regards,\n" .
            "*BOLSO Fabric Art Studio*\n" .
            "+91 9341469219";

    return $msg;
}

/**
 * Dispatch Workshop Timing Schedule to Student (Email + WhatsApp) and Notify Admin.
 */
function bolso_notify_timing_schedule(int $registrationId, string $timingSlot, string $venueOrLink = '', string $notes = ''): array
{
    $pdo = bolso_db();
    if (!$pdo) {
        return ['success' => false, 'error' => 'Database connection failed'];
    }

    $stmt = $pdo->prepare('SELECT * FROM registrations WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $registrationId]);
    $reg = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reg) {
        return ['success' => false, 'error' => 'Registration not found'];
    }

    // Enrich workshop title
    try {
        $wStmt = $pdo->prepare('SELECT title FROM workshops WHERE slug = :slug LIMIT 1');
        $wStmt->execute([':slug' => $reg['workshop']]);
        $w = $wStmt->fetch();
        if ($w && !empty($w['title'])) {
            $reg['workshop_title'] = $w['title'];
        }
    } catch (PDOException $e) {
        // ignore
    }
    if (empty($reg['workshop_title'])) {
        $reg['workshop_title'] = ($reg['workshop'] === '5-day') ? '5-Day Workshop' : '2-Day Workshop';
    }

    // Update registration row with timing details
    try {
        $up = $pdo->prepare('UPDATE registrations SET timing_slot = :timing, timing_sent_at = NOW() WHERE id = :id');
        $up->execute([':timing' => $timingSlot, ':id' => $registrationId]);
    } catch (PDOException $e) {
        error_log('Failed to save timing_slot on registration: ' . $e->getMessage());
    }

    // 1. Send Customer Timing Email
    $customerSubject = 'Confirmed Workshop Timing: ' . $reg['workshop_title'] . ' (ID: #' . $registrationId . ')';
    $customerHtml = bolso_build_customer_timing_email_html($reg, $timingSlot, $venueOrLink, $notes);
    $customerEmailResult = bolso_send_mail(
        $reg['email'],
        $reg['name'],
        $customerSubject,
        $customerHtml,
        '',
        $registrationId,
        'customer'
    );

    // 2. Send Customer WhatsApp
    $customerWaText = bolso_build_customer_timing_whatsapp_text($reg, $timingSlot, $venueOrLink, $notes);
    $customerWaResult = bolso_send_whatsapp(
        $reg['whatsapp'],
        $customerWaText,
        $registrationId,
        'customer'
    );

    // 3. Admin Confirmation Email to 10abhishekkr@gmail.com
    $adminEmail = bolso_config('admin_email', '10abhishekkr@gmail.com');
    $adminPhone = bolso_config('admin_whatsapp', '919341469219');
    $adminSubject = 'Timing Dispatched: ' . $reg['name'] . ' (' . $timingSlot . ')';
    $adminHtml = "<div style='font-family:sans-serif; padding:20px; background:#f9f9f9;'>" .
                 "<h2 style='color:#14532d;'>✓ Timing Dispatched to Student</h2>" .
                 "<p><strong>Student:</strong> {$reg['name']} ({$reg['email']})</p>" .
                 "<p><strong>WhatsApp:</strong> {$reg['whatsapp']}</p>" .
                 "<p><strong>Workshop:</strong> {$reg['workshop_title']}</p>" .
                 "<p><strong>Timing Assigned:</strong> <strong>{$timingSlot}</strong></p>" .
                 "<p><strong>Venue / Link:</strong> {$venueOrLink}</p>" .
                 "</div>";
    $adminEmailResult = bolso_send_mail(
        $adminEmail,
        'BOLSO Admin',
        $adminSubject,
        $adminHtml,
        '',
        $registrationId,
        'admin'
    );

    // 4. Admin WhatsApp Log to 9341469219
    $adminWaText = "✅ *TIMING SENT TO STUDENT*\n\n" .
                   "• Student: *{$reg['name']}*\n" .
                   "• Workshop: {$reg['workshop_title']}\n" .
                   "• Timing: *{$timingSlot}*\n" .
                   "• WhatsApp: +{$customerWaResult['phone']}\n" .
                   "• Reg ID: #{$registrationId}";
    $adminWaResult = bolso_send_whatsapp(
        $adminPhone,
        $adminWaText,
        $registrationId,
        'admin'
    );

    return [
        'success' => true,
        'timing_slot' => $timingSlot,
        'customer_email' => $customerEmailResult,
        'customer_whatsapp' => $customerWaResult,
        'customer_whatsapp_link' => $customerWaResult['wa_link'] ?? '',
        'admin_email' => $adminEmailResult,
        'admin_whatsapp' => $adminWaResult,
    ];
}

/**
 * Resend Payment Confirmation & Timing Clarification for an existing registration.
 */
function bolso_resend_payment_confirmation(int $registrationId): array
{
    $pdo = bolso_db();
    if (!$pdo) {
        return ['success' => false, 'error' => 'Database connection failed'];
    }

    $stmt = $pdo->prepare('SELECT * FROM registrations WHERE id = :id LIMIT 1');
    $stmt->execute([':id' => $registrationId]);
    $reg = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reg) {
        return ['success' => false, 'error' => 'Registration not found'];
    }

    return bolso_notify_payment_success($reg, 'RESENT_BY_ADMIN');
}

/**
 * Get notification statistics.
 */
function bolso_get_notification_stats(): array
{
    bolso_notification_ensure_table();
    $pdo = bolso_db();
    $stats = [
        'total' => 0,
        'email_count' => 0,
        'whatsapp_count' => 0,
        'customer_count' => 0,
        'admin_count' => 0,
        'sent_count' => 0,
        'simulated_count' => 0,
        'failed_count' => 0,
        'today_count' => 0,
    ];

    if (!$pdo) return $stats;

    try {
        $row = $pdo->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN channel = 'email' THEN 1 ELSE 0 END) as email_count,
                SUM(CASE WHEN channel = 'whatsapp' THEN 1 ELSE 0 END) as whatsapp_count,
                SUM(CASE WHEN recipient_type = 'customer' THEN 1 ELSE 0 END) as customer_count,
                SUM(CASE WHEN recipient_type = 'admin' THEN 1 ELSE 0 END) as admin_count,
                SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent_count,
                SUM(CASE WHEN status = 'simulated' THEN 1 ELSE 0 END) as simulated_count,
                SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_count,
                SUM(CASE WHEN DATE(created_at) = CURRENT_DATE() THEN 1 ELSE 0 END) as today_count
            FROM notifications_log
        ")->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            foreach ($stats as $k => $v) {
                $stats[$k] = (int)($row[$k] ?? 0);
            }
        }
    } catch (PDOException $e) {
        error_log('Error fetching notification stats: ' . $e->getMessage());
    }

    return $stats;
}

/**
 * Get notification logs with filtering and pagination.
 */
function bolso_get_notifications(int $limit = 50, int $offset = 0, array $filters = []): array
{
    bolso_notification_ensure_table();
    $pdo = bolso_db();
    if (!$pdo) return [];

    $where = [];
    $params = [];

    if (!empty($filters['channel']) && in_array($filters['channel'], ['email', 'whatsapp'], true)) {
        $where[] = 'n.channel = :channel';
        $params[':channel'] = $filters['channel'];
    }

    if (!empty($filters['recipient_type']) && in_array($filters['recipient_type'], ['customer', 'admin'], true)) {
        $where[] = 'n.recipient_type = :recipient_type';
        $params[':recipient_type'] = $filters['recipient_type'];
    }

    if (!empty($filters['status']) && in_array($filters['status'], ['sent', 'simulated', 'failed', 'ready'], true)) {
        $where[] = 'n.status = :status';
        $params[':status'] = $filters['status'];
    }

    if (!empty($filters['search'])) {
        $where[] = '(n.destination LIKE :search OR n.subject LIKE :search OR r.name LIKE :search)';
        $params[':search'] = '%' . $filters['search'] . '%';
    }

    $sql = "SELECT n.*, r.name as student_name, r.workshop as workshop_slug 
            FROM notifications_log n
            LEFT JOIN registrations r ON r.id = n.registration_id";

    if (!empty($where)) {
        $sql .= ' WHERE ' . implode(' AND ', $where);
    }

    $safeLimit = max(1, min(100, (int)$limit));
    $safeOffset = max(0, (int)$offset);
    $sql .= " ORDER BY n.id DESC LIMIT {$safeLimit} OFFSET {$safeOffset}";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error fetching notification logs: ' . $e->getMessage());
        return [];
    }
}

/**
 * Send password reset email for users or admins.
 */
function bolso_send_password_reset_email(string $userType, string $toEmail, string $toName, string $resetLink): array
{
    $appName = 'BOLSO Fabric Art Studio';
    $roleName = $userType === 'admin' ? 'Studio Administrator' : 'Student';
    $subject = "Reset Your Password · {$appName}";
    $safeName = htmlspecialchars($toName, ENT_QUOTES, 'UTF-8');
    $safeLink = htmlspecialchars($resetLink, ENT_QUOTES, 'UTF-8');

    $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f6f0e6; color: #1f2d3d; margin: 0; padding: 30px 15px; }
  .card { max-width: 540px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid rgba(31,45,61,0.12); padding: 32px; box-shadow: 0 8px 24px rgba(84,29,44,0.06); }
  .logo { font-size: 24px; font-weight: bold; color: #7c2639; font-style: italic; letter-spacing: 0.1em; text-align: center; margin-bottom: 24px; }
  h2 { font-size: 20px; color: #1f2d3d; margin-top: 0; }
  p { font-size: 14px; line-height: 1.6; color: #496174; }
  .btn { display: inline-block; background: #7c2639; color: #f6f0e6 !important; text-decoration: none; padding: 13px 28px; font-size: 13px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.08em; border-radius: 4px; margin: 20px 0; }
  .footnote { font-size: 12px; color: #8898aa; border-top: 1px solid #eee; padding-top: 16px; margin-top: 24px; }
</style>
</head>
<body>
<div class="card">
  <div class="logo">BOLSO</div>
  <h2>Password Reset Request</h2>
  <p>Hello <strong>{$safeName}</strong>,</p>
  <p>We received a request to reset your password for your BOLSO {$roleName} account. Click the button below to choose a new password:</p>
  <div style="text-align: center;">
    <a href="{$safeLink}" class="btn">Reset My Password</a>
  </div>
  <p>Or paste this link into your browser:</p>
  <p style="word-break: break-all; font-size: 12px; color: #7c2639;">{$safeLink}</p>
  <p class="footnote">This link will expire in <strong>1 hour</strong>. If you did not request a password reset, you can safely ignore this email — your account remains secure.</p>
</div>
</body>
</html>
HTML;

    $plainText = "Hello {$toName},\n\nWe received a request to reset your password for your BOLSO {$roleName} account.\n\nTo reset your password, please open the following link in your browser:\n{$resetLink}\n\nThis link will expire in 1 hour.\nIf you did not request this, please ignore this email.\n\n- BOLSO Fabric Art Studio";

    return bolso_send_mail($toEmail, $toName, $subject, $html, $plainText, null, $userType === 'admin' ? 'admin' : 'customer');
}

