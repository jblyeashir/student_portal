<?php
session_start();
require_once 'functions.php';

// Ensure student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit;
}

$userId = $_SESSION['user_id'];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name']);
    $email   = trim($_POST['email']);
    $phone   = trim($_POST['phone']);
    $country = trim($_POST['country']);
    $subject = trim($_POST['subject']);

    // Update users table
    $stmt = $db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
    $stmt->execute([$name, $email, $userId]);

    // Update students table
    $stmt = $db->prepare("UPDATE students SET phone = ?, country = ?, subject = ? WHERE user_id = ?");
    $stmt->execute([$phone, $country, $subject, $userId]);

    // Redirect with success flag
    header("Location: student_dashboard.php?updated=1");
    exit;
}

// Fetch current profile
$stmt = $db->prepare("
    SELECT u.name, u.email, s.phone, s.country, s.subject
    FROM users u
    LEFT JOIN students s ON u.id = s.user_id
    WHERE u.id = ?
");
$stmt->execute([$userId]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

// Fallback defaults
if (!$student) {
    $student = ['name' => '', 'email' => '', 'phone' => '', 'country' => '', 'subject' => ''];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center font-sans">

<div class="bg-white shadow-xl rounded-xl w-full max-w-lg p-8">
    <h1 class="text-2xl font-bold text-indigo-600 mb-6">Edit Profile</h1>

    <form method="POST" class="space-y-4">
        <!-- Name -->
        <div>
            <label class="block text-gray-700 mb-1">Full Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($student['name']) ?>"
                   class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <!-- Email -->
        <div>
            <label class="block text-gray-700 mb-1">Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($student['email']) ?>"
                   class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <!-- Phone -->
        <div>
            <label class="block text-gray-700 mb-1">Phone</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($student['phone']) ?>"
                   class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <!-- Country -->
        <div>
            <label class="block text-gray-700 mb-1">Country</label>
            <input type="text" name="country" value="<?= htmlspecialchars($student['country']) ?>"
                   class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <!-- Subject -->
        <div>
            <label class="block text-gray-700 mb-1">Subject</label>
            <input type="text" name="subject" value="<?= htmlspecialchars($student['subject']) ?>"
                   class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <!-- Buttons -->
        <div class="flex justify-between items-center mt-6">
            <a href="student_dashboard.php"
               class="text-gray-600 hover:text-gray-900">← Back</a>
            <button type="submit"
                    class="bg-indigo-600 text-white px-5 py-2 rounded-lg hover:bg-indigo-700 transition">
                Save Changes
            </button>
        </div>
    </form>
</div>

</body>
</html>
