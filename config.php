<?php
// config.php
return [
'db' => [
'host' => '127.0.0.1',
'dbname' => 'student_portal',
'user' => 'root',
'pass' => ''
],
// Email (PHPMailer) settings - fill with your SMTP
'mail' => [
'host' => 'smtp.example.com',
'username' => 'user@example.com',
'password' => 'emailpassword',
'port' => 587,
'from_email' => 'no-reply@example.com',
'from_name' => 'NR Consultancy'
],
// SMS (Twilio) placeholders
'sms' => [
'account_sid' => 'TWILIO_SID',
'auth_token' => 'TWILIO_TOKEN',
'from' => '+1234567890'
],
// Upload folder
'upload_dir' => __DIR__ . '/uploads'
];