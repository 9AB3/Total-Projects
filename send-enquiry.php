<?php
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('success' => false, 'message' => 'Method not allowed.'));
    exit;
}

// Honeypot: legitimate users never fill this field.
$website = isset($_POST['website']) ? $_POST['website'] : '';
if (!empty($website)) {
    echo json_encode(array('success' => true));
    exit;
}

function clean_line($value) {
    return trim(str_replace(array("\r", "\n"), ' ', (string)$value));
}

$name = clean_line(isset($_POST['name']) ? $_POST['name'] : '');
$company = clean_line(isset($_POST['company']) ? $_POST['company'] : '');
$emailRaw = trim(isset($_POST['email']) ? $_POST['email'] : '');
$email = filter_var($emailRaw, FILTER_VALIDATE_EMAIL);
$phone = clean_line(isset($_POST['phone']) ? $_POST['phone'] : '');
$message = trim((string)(isset($_POST['message']) ? $_POST['message'] : ''));

if ($name === '' || !$email || $message === '') {
    http_response_code(422);
    echo json_encode(array('success' => false, 'message' => 'Please complete your name, email and enquiry.'));
    exit;
}

if (strlen($name) > 120 || strlen($company) > 160 || strlen($phone) > 60 || strlen($message) > 5000) {
    http_response_code(422);
    echo json_encode(array('success' => false, 'message' => 'One or more fields are too long.'));
    exit;
}

$to = 'pat@totalprojects.com.au, lesley@totalprojects.com.au';
$subject = 'Website quote request - ' . $name;
$body = "A new quote request has been submitted through totalprojects.com.au\n\n"
      . "Name: {$name}\n"
      . "Company: " . ($company !== '' ? $company : 'Not provided') . "\n"
      . "Email: {$email}\n"
      . "Phone: " . ($phone !== '' ? $phone : 'Not provided') . "\n\n"
      . "Enquiry:\n{$message}\n\n"
      . "Submitted: " . date('Y-m-d H:i:s T') . "\n";

$headers = "From: Total Projects Website <pat@totalprojects.com.au>\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

$sent = @mail($to, $subject, $body, $headers);

if (!$sent) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'message' => 'Mail delivery failed.'));
    exit;
}

echo json_encode(array('success' => true));
