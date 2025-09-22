<?php
session_start();
require_once 'functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get student_id
$stmt = $db->prepare("SELECT id FROM students WHERE user_id=?");
$stmt->execute([$user_id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);
$student_id = $student['id'];

if ($_SERVER['REQUEST_METHOD']==='POST') {
    $university = trim($_POST['university']);
    $program    = trim($_POST['program']);

    $stmt = $db->prepare("INSERT INTO applications (student_id,university,program) VALUES (?,?,?)");
    $stmt->execute([$student_id,$university,$program]);

    header("Location: student_dashboard.php?app=added");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head><title>New Application</title></head>
<body>
<h2>Create New Application</h2>
<form method="post">
  <label>University:</label><br>
  <input type="text" name="university" required><br>

  <label>Program:</label><br>
  <input type="text" name="program" required><br>

  <button type="submit">Submit</button>
</form>
<a href="student_dashboard.php">Back</a>
</body>
</html>
