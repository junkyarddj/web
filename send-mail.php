<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

/** ---------------- SMTP SETTINGS: EDIT THESE ---------------- */
$smtpHost = 'smtp.livemail.co.uk';
$smtpPort = 587;          // Usually 587 for TLS, or 465 for SSL
$smtpSecure = 'tls';      // 'tls' or 'ssl'
$smtpUsername = 'YOUR_EMAIL@YOURDOMAIN.CO.UK';
$smtpPassword = 'YOUR_PASSWORD';

$fromEmail = 'YOUR_EMAIL@YOURDOMAIN.CO.UK';
$fromName = 'Junkyard DJ Website';
$toEmail = 'YOUR_EMAIL@YOURDOMAIN.CO.UK';
$toName = 'Junkyard DJ';
/** ----------------------------------------------------------- */

function clean_input(?string $value): string {
    return trim((string)$value);
}

function safe_html(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

if (!empty($_POST['website'] ?? '')) {
    http_response_code(400);
    exit('Spam detected.');
}

$name       = clean_input($_POST['name'] ?? '');
$email      = clean_input($_POST['email'] ?? '');
$phone      = clean_input($_POST['phone'] ?? '');
$eventType  = clean_input($_POST['event_type'] ?? '');
$date       = clean_input($_POST['date'] ?? '');
$location   = clean_input($_POST['location'] ?? '');
$message    = clean_input($_POST['message'] ?? '');

$errors = [];
if ($name === '') $errors[] = 'Name is required.';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email address is required.';
if ($message === '') $errors[] = 'Please enter a message.';

if (!empty($errors)) {
    http_response_code(400);
    echo '<h1>There was a problem with your form submission</h1><ul>';
    foreach ($errors as $error) echo '<li>' . safe_html($error) . '</li>';
    echo '</ul><p><a href="contact.html">Go back to the contact form</a></p>';
    exit;
}

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = $smtpHost;
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtpUsername;
    $mail->Password   = $smtpPassword;
    $mail->Port       = $smtpPort;
    $mail->SMTPSecure = ($smtpSecure === 'ssl')
        ? PHPMailer::ENCRYPTION_SMTPS
        : PHPMailer::ENCRYPTION_STARTTLS;

    $mail->CharSet = 'UTF-8';
    $mail->setFrom($fromEmail, $fromName);
    $mail->addAddress($toEmail, $toName);

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mail->addReplyTo($email, $name);
    }

    $mail->Subject = 'New Junkyard DJ enquiry' . ($eventType !== '' ? ' - ' . $eventType : '');
    $mail->isHTML(true);

    $bodyRows = [
        'Name' => $name,
        'Email' => $email,
        'Phone' => $phone,
        'Booking type' => $eventType,
        'Preferred date' => $date,
        'Venue / location' => $location,
    ];

    $html = '<h2>New enquiry from the Junkyard DJ website</h2><table cellpadding="8" cellspacing="0" border="1" style="border-collapse:collapse;">';
    foreach ($bodyRows as $label => $value) {
        $html .= '<tr><th align="left">' . safe_html($label) . '</th><td>' . nl2br(safe_html($value)) . '</td></tr>';
    }
    $html .= '<tr><th align="left">Message</th><td>' . nl2br(safe_html($message)) . '</td></tr></table>';

    $plain = "New enquiry from the Junkyard DJ website\n\n";
    foreach ($bodyRows as $label => $value) $plain .= $label . ': ' . $value . "\n";
    $plain .= "\nMessage:\n" . $message . "\n";

    $mail->Body = $html;
    $mail->AltBody = $plain;
    $mail->send();

    header('Location: thank-you.html');
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo '<h1>Sorry — the enquiry could not be sent.</h1>';
    echo '<p>Please check your SMTP settings in <code>send-mail.php</code>, especially the mailbox, password, port and TLS/SSL setting.</p>';
    echo '<p>PHPMailer error: ' . safe_html($mail->ErrorInfo) . '</p>';
    echo '<p><a href="contact.html">Go back to the contact form</a></p>';
}
