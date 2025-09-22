<?php
session_start();
require_once 'functions.php'; // DB connection & helper functions

// Ensure student is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit;
}

// Fetch student profile
$stmt = $db->prepare("
    SELECT u.name, u.email, s.phone, s.country, s.subject
    FROM students s
    JOIN users u ON s.user_id = u.id
    WHERE s.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

// Fallback if profile not found
if (!$student) {
    $student = [
        'name' => '',
        'email' => '',
        'phone' => '',
        'country' => '',
        'subject' => ''
    ];
}

// Fetch applications
$stmt = $db->prepare("
    SELECT a.id, a.university, a.program, s.title AS stage_name
    FROM applications a
    LEFT JOIN stages s ON a.current_stage_id = s.id
    WHERE a.student_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$applications) $applications = [];

// Fetch documents
$stmt = $db->prepare("
    SELECT d.id, d.file_path, d.status, dt.title AS doc_title
    FROM documents d
    JOIN doc_types dt ON d.doc_type_id = dt.id
    WHERE d.student_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$documents) $documents = [];

// Fetch notifications
$stmt = $db->prepare("
    SELECT * FROM notifications 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 10
");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!$notifications) $notifications = [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Student Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen font-sans">
<div class="max-w-7xl mx-auto p-6">

  <!-- Header -->
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-blue-700">Student Dashboard</h1>
    <a href="logout.php" class="text-red-600 font-semibold hover:underline">Logout</a>
  </div>

  <!-- Main Grid -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

    <!-- Profile Card -->
    <div class="bg-white p-6 rounded-xl shadow-lg">
      <h2 class="text-xl font-bold mb-4 text-indigo-600">My Profile</h2>
      <p><b>Name:</b> <?= htmlspecialchars($student['name']) ?></p>
      <p><b>Email:</b> <?= htmlspecialchars($student['email']) ?></p>
      <p><b>Phone:</b> <?= htmlspecialchars($student['phone'] ?? '-') ?></p>
      <p><b>Country:</b> <?= htmlspecialchars($student['country'] ?? '-') ?></p>
      <p><b>Subject:</b> <?= htmlspecialchars($student['subject'] ?? '-') ?></p>
      <a href="edit_profile.php" class="mt-3 inline-block bg-indigo-600 text-white p-2 rounded-lg hover:bg-indigo-700 transition">Edit Profile</a>
    </div>

    <!-- Applications Card -->
    <div class="bg-white p-6 rounded-xl shadow-lg">
      <h2 class="text-xl font-bold mb-4 text-indigo-600">Applications</h2>
      <a href="add_application.php" class="mb-4 inline-block bg-green-600 text-white p-2 rounded-lg hover:bg-green-700 transition">+ New Application</a>
      <div class="overflow-x-auto">
        <table class="w-full text-left border border-gray-200 rounded">
          <thead class="bg-gray-200">
            <tr>
              <th class="px-4 py-2">University</th>
              <th class="px-4 py-2">Program</th>
              <th class="px-4 py-2">Stage</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($applications): ?>
              <?php foreach ($applications as $app): ?>
                <tr class="border-b hover:bg-gray-50">
                  <td class="px-4 py-2"><?= htmlspecialchars($app['university']) ?></td>
                  <td class="px-4 py-2"><?= htmlspecialchars($app['program']) ?></td>
                  <td class="px-4 py-2"><?= htmlspecialchars($app['stage_name'] ?? 'Not started') ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
                <tr>
                  <td colspan="3" class="px-4 py-2 text-gray-500 text-center">No applications yet.</td>
                </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>

  <!-- Documents -->
  <div class="mt-6 bg-white p-6 rounded-xl shadow-lg">
    <h2 class="text-xl font-bold mb-4 text-indigo-600">Documents</h2>
    <ul class="list-disc pl-5 space-y-1">
      <?php if ($documents): ?>
        <?php foreach ($documents as $doc): ?>
          <li>
            <?= htmlspecialchars($doc['doc_title']) ?> - 
            <a href="<?= htmlspecialchars($doc['file_path']) ?>" class="text-blue-600 hover:underline" target="_blank">View</a> 
            (<?= htmlspecialchars($doc['status']) ?>)
          </li>
        <?php endforeach; ?>
      <?php else: ?>
        <li class="text-gray-500">No documents uploaded yet.</li>
      <?php endif; ?>
    </ul>
  </div>

  <!-- Notifications -->
  <div class="mt-6 bg-white p-6 rounded-xl shadow-lg">
    <h2 class="text-xl font-bold mb-4 text-indigo-600">Notifications</h2>
    <ul class="list-disc pl-5 space-y-1">
      <?php if ($notifications): ?>
        <?php foreach ($notifications as $note): ?>
          <li>[<?= strtoupper($note['type']) ?>] <?= htmlspecialchars($note['message']) ?> - <?= $note['created_at'] ?></li>
        <?php endforeach; ?>
      <?php else: ?>
        <li class="text-gray-500">No notifications yet.</li>
      <?php endif; ?>
    </ul>
  </div>

</div>
</body>
</html>
