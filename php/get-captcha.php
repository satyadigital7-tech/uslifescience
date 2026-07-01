<?php
/**
 * US Lifescience - Security Captcha & CSRF API
 * Generates and returns random math security variables and session tokens for static HTML forms.
 */

session_start();

header('Content-Type: application/json');

// Initialize CSRF Token if not present in session
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Generate random integers between 1 and 9
$num1 = rand(1, 9);
$num2 = rand(1, 9);

// Set the correct answer key in user session
$_SESSION['captcha_answer'] = $num1 + $num2;

// Output variables for JS retrieval
echo json_encode([
    'csrf_token' => $_SESSION['csrf_token'],
    'num1' => $num1,
    'num2' => $num2
]);
exit;
