<?php
// functions.php
require_once 'conn.php';

/**
 * Get user by email
 */
function getUserByEmail($email){
    global $db;
    $stmt = $db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Create a user
 */
function createUser($name, $email, $password, $role='student'){
    global $db;
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $db->prepare('INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)');
    $stmt->execute([$name,$email,$hash,$role]);
    return $db->lastInsertId();
}

/**
 * Send email (PHPMailer placeholder)
 */
function sendEmail($to, $subject, $body){
    $config = require __DIR__ . '/config.php';
    // PHPMailer example (needs PHPMailer installed via Composer)
    require_once __DIR__ . '/vendor/PHPMailer/src/Exception.php';
    require_once __DIR__ . '/vendor/PHPMailer/src/PHPMailer.php';
    require_once __DIR__ . '/vendor/PHPMailer/src/SMTP.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = $config['mail']['host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['mail']['username'];
        $mail->Password = $config['mail']['password'];
        $mail->SMTPSecure = 'tls';
        $mail->Port = $config['mail']['port'];
        $mail->setFrom($config['mail']['from_email'], $config['mail']['from_name']);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mail error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Send SMS (Twilio placeholder)
 */
function sendSMS($to, $message){
    $config = require __DIR__ . '/config.php';
    $sid = $config['sms']['account_sid'];
    $token = $config['sms']['auth_token'];
    $from = $config['sms']['from'];
    $url = "https://api.twilio.com/2010-04-01/Accounts/$sid/Messages.json";
    $data = http_build_query([
        'From' => $from,
        'To' => $to,
        'Body' => $message
    ]);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_USERPWD, "$sid:$token");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) {
        error_log('SMS error: ' . $err);
        return false;
    }
    return $result !== false;
}

/**
 * Fetch dynamic stages
 */
function getStages(){
    global $db;
    $stmt = $db->query('SELECT * FROM stages ORDER BY position ASC');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Fetch dynamic document types
 */
function getDocTypes(){
    global $db;
    $stmt = $db->query('SELECT * FROM doc_types ORDER BY required DESC, id ASC');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get user by ID
 */
function getUserById($id){
    global $db;
    $stmt = $db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Update user (reusable helper)
 * Accepts an array of fields => values
 */
function updateUser($id, $data){
    global $db;

    $fields = [];
    $values = [];
    foreach ($data as $key => $value) {
        $fields[] = "$key = ?";
        $values[] = $value;
    }
    $values[] = $id;

    $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    return $stmt->execute($values);
}

