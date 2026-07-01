<?php
/**
 * US Lifescience - Mailer Integration
 * Manages SMTP email dispatch via PHPMailer with native PHP mail() fallback.
 */

require_once __DIR__ . '/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Flags to determine if PHPMailer loaded successfully
$mailAutoloaded = false;

// 1. Try to load PHPMailer via Composer Autoloader
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    $mailAutoloaded = true;
} 
// 2. Try to load PHPMailer via manual structure under php/PHPMailer/
elseif (file_exists(__DIR__ . '/PHPMailer/src/PHPMailer.php')) {
    require_once __DIR__ . '/PHPMailer/src/Exception.php';
    require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer/src/SMTP.php';
    $mailAutoloaded = true;
}

/**
 * Dispatches an email using SMTP (PHPMailer) or native mail()
 * 
 * @param string $to Recipient Email
 * @param string $subject Email Subject
 * @param string $htmlContent HTML Body
 * @param string $textContent Plain Text Fallback
 * @param string $fromName Sender Name
 * @param string $fromEmail Sender Email
 * @return bool Success Status
 */
function sendEnquiryMail($to, $subject, $htmlContent, $textContent, $fromName, $fromEmail) {
    global $mailAutoloaded;
    
    // Check if PHPMailer is loaded and SMTP details are configured
    if ($mailAutoloaded && !empty(SMTP_PASS)) {
        $mail = new PHPMailer(true);
        try {
            // Server configuration
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USER;
            $mail->Password   = SMTP_PASS;
            $mail->SMTPSecure = (SMTP_SECURE === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = SMTP_PORT;

            // Timeout configuration
            $mail->Timeout    = 10; 

            // Sender & Receiver
            $mail->setFrom(SMTP_USER, SITE_NAME . ' Website');
            $mail->addAddress($to);
            $mail->addReplyTo($fromEmail, $fromName);

            // Email Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlContent;
            $mail->AltBody = $textContent;

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Log PHPMailer exception details and execute native fallback
            error_log("PHPMailer Exception encountered: " . $mail->ErrorInfo . " | Reason: " . $e->getMessage());
            return fallbackNativeMail($to, $subject, $htmlContent, $fromName, $fromEmail);
        }
    } else {
        // Log lack of PHPMailer load and run fallback
        error_log("PHPMailer autoload/credentials missing. Falling back to native php mail().");
        return fallbackNativeMail($to, $subject, $htmlContent, $fromName, $fromEmail);
    }
}

/**
 * Standard native PHP mail() function fallback
 */
function fallbackNativeMail($to, $subject, $htmlContent, $fromName, $fromEmail) {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . SITE_NAME . " <" . CONTACT_EMAIL . ">\r\n";
    $headers .= "Reply-To: " . $fromName . " <" . $fromEmail . ">\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();

    return mail($to, $subject, $htmlContent, $headers);
}
