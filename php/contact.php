<?php
/**
 * US Lifescience - AJAX Contact Handler
 * Validates security tokens, processes input sanitization, and issues the dispatch request.
 */

// Start session to access CSRF and Captcha validation tokens
session_start();

header('Content-Type: application/json');

// Ensure correct request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/mailer.php';

// 1. CSRF Token Verification
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['status' => 'error', 'message' => 'Security token verification failed. Please reload the page.']);
    exit;
}

// 2. Math Captcha Verification Bypassed (Removed by User Request)

// 3. Input Sanitization & Retrieval
$name = isset($_POST['name']) ? trim(htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8')) : '';
$hospital = isset($_POST['hospital']) ? trim(htmlspecialchars($_POST['hospital'], ENT_QUOTES, 'UTF-8')) : '';
$phone = isset($_POST['phone']) ? trim(htmlspecialchars($_POST['phone'], ENT_QUOTES, 'UTF-8')) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$subject = isset($_POST['subject']) ? trim(htmlspecialchars($_POST['subject'], ENT_QUOTES, 'UTF-8')) : '';
$message = isset($_POST['message']) ? trim(htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8')) : '';

// 4. Server-Side Validations
if (empty($name) || empty($phone) || empty($email) || empty($subject) || empty($message)) {
    echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Please provide a valid email address.']);
    exit;
}

// Match basic Indian/Global phone numbers
if (!preg_match('/^[0-9\-\+\s]{10,15}$/', $phone)) {
    echo json_encode(['status' => 'error', 'message' => 'Please provide a valid phone number.']);
    exit;
}

// 5. Generate Email Body (HTML & Plain Text)
$ip = $_SERVER['REMOTE_ADDR'];
$time = date('Y-m-d H:i:s');

// Premium Corporate HTML Template
$htmlBody = "
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Inter', Arial, sans-serif; line-height: 1.6; color: #1B1B1B; margin: 0; padding: 0; }
        .wrapper { background-color: #F7F9FC; padding: 40px 20px; }
        .card { max-width: 600px; margin: 0 auto; background-color: #FFFFFF; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border-top: 6px solid #0078D7; }
        .header { background-color: #0078D7; color: #FFFFFF; padding: 30px; text-align: center; }
        .header h2 { margin: 0; font-size: 24px; font-weight: 700; font-family: 'Poppins', sans-serif; }
        .header p { margin: 5px 0 0 0; opacity: 0.9; font-size: 14px; }
        .content { padding: 40px 30px; }
        .field-group { margin-bottom: 20px; border-bottom: 1px solid #E5E9F0; padding-bottom: 15px; }
        .field-group:last-child { border-bottom: none; }
        .label { font-weight: 700; font-size: 12px; color: #0078D7; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
        .value { font-size: 16px; color: #1B1B1B; }
        .footer { background-color: #E5E9F0; padding: 20px; text-align: center; font-size: 12px; color: #666666; }
    </style>
</head>
<body>
    <div class='wrapper'>
        <div class='card'>
            <div class='header'>
                <h2>New Website Enquiry</h2>
                <p>US Lifescience Pharmaceutical Portal</p>
            </div>
            <div class='content'>
                <div class='field-group'>
                    <div class='label'>Name</div>
                    <div class='value'>$name</div>
                </div>
                <div class='field-group'>
                    <div class='label'>Hospital / Clinic</div>
                    <div class='value'>" . ($hospital ? $hospital : 'Not Provided') . "</div>
                </div>
                <div class='field-group'>
                    <div class='label'>Phone Number</div>
                    <div class='value'>$phone</div>
                </div>
                <div class='field-group'>
                    <div class='label'>Email Address</div>
                    <div class='value'>$email</div>
                </div>
                <div class='field-group'>
                    <div class='label'>Subject</div>
                    <div class='value'>$subject</div>
                </div>
                <div class='field-group'>
                    <div class='label'>Message</div>
                    <div class='value'>" . nl2br($message) . "</div>
                </div>
            </div>
            <div class='footer'>
                Sent from IP: $ip at $time <br>
                &copy; " . date('Y') . " US Lifescience. All Rights Reserved.
            </div>
        </div>
    </div>
</body>
</html>
";

$textBody = "
New Enquiry Received from US Lifescience Website
------------------------------------------------
Name: $name
Hospital/Clinic: " . ($hospital ? $hospital : 'Not Provided') . "
Phone: $phone
Email: $email
Subject: $subject
Message:
$message

------------------------------------------------
Sent from IP: $ip at $time
";

// 6. Dispatch Email
$recipient = CONTACT_EMAIL;
$mailSubject = "Website Enquiry: " . $subject;

$isSent = sendEnquiryMail($recipient, $mailSubject, $htmlBody, $textBody, $name, $email);

if ($isSent) {
    echo json_encode(['status' => 'success', 'message' => 'Thank you! Your enquiry has been sent successfully. We will get back to you shortly.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to send your email. Please try again later or email us directly.']);
}
exit;
