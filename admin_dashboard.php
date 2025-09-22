<?php
session_start();
require_once 'functions.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['super_admin','admin','counsellor'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role    = $_SESSION['role'];

// Fetch all students
$stmt = $db->query("SELECT u.id as user_id, u.name, u.email, s.id as student_id, s.phone, s.country, s.subject 
                    FROM users u 
                    JOIN students s ON u.id = s.user_id 
                    ORDER BY u.id DESC");
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all applications with current stage
$stmt = $db->query("SELECT a.*, u.name as student_name, s.title as stage_name 
                    FROM applications a 
                    JOIN students st ON a.student_id = st.id
                    JOIN users u ON st.user_id = u.id
                    LEFT JOIN stages s ON a.current_stage_id = s.id
                    ORDER BY a.created_at DESC");
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all stages
$stages = getStages();

// Fetch all doc types
$docTypes = getDocTypes();

// Handle add stage
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_stage'])) {
    $title = trim($_POST['new_stage']);
    $stmt = $db->prepare("INSERT INTO stages (title, position) VALUES (?, ?)");
    $stmt->execute([$title, count($stages)+1]);
    header("Location: admin_dashboard.php?stage=added");
    exit;
}

// Handle add doc type
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_doc_type'])) {
    $title = trim($_POST['new_doc_type']);
    $required = isset($_POST['required']) ? 1 : 0;
    $stmt = $db->prepare("INSERT INTO doc_types (title, required) VALUES (?, ?)");
    $stmt->execute([$title, $required]);
    header("Location: admin_dashboard.php?doctype=added");
    exit;
}

// Handle stage update for application
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stage'])) {
    $appId = $_POST['app_id'];
    $stageId = $_POST['stage_id'];

    $stmt = $db->prepare("UPDATE applications SET current_stage_id=? WHERE id=?");
    $stmt->execute([$stageId,$appId]);

    // Log
    $stmt = $db->prepare("INSERT INTO application_stage_logs (application_id, stage_id, status, updated_by) VALUES (?,?,?,?)");
    $stmt->execute([$appId, $stageId, 'updated', $user_id]);

    header("Location: admin_dashboard.php?stage=updated");
    exit;
}

// Handle notification sending
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notify_user'])) {
    $toUser = $_POST['notify_user'];
    $type   = $_POST['notify_type'];
    $message = trim($_POST['notify_message']);

    $stmt = $db->prepare("INSERT INTO notifications (user_id, type, message) VALUES (?,?,?)");
    $stmt->execute([$toUser,$type,$message]);

    // Send actual email or SMS
    if ($type === 'email') {
        $user = $db->query("SELECT email FROM users WHERE id=$toUser")->fetch(PDO::FETCH_ASSOC);
        if ($user) sendEmail($user['email'], "Notification from NR Consultancy", $message);
    } else {
        $student = $db->query("SELECT phone FROM students WHERE user_id=$toUser")->fetch(PDO::FETCH_ASSOC);
        if ($student) sendSMS($student['phone'], $message);
    }

    header("Location: admin_dashboard.php?notified=1");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="style.css">
</head>
<body class="bg-gray-100 min-h-screen p-6 font-sans">
  <div class="max-w-7xl mx-auto">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-blue-700">Admin Dashboard</h1>
      <a href="logout.php" class="text-red-600 font-semibold hover:underline">Logout</a>
    </div>

    <!-- Students Table -->
    <div class="bg-white p-6 rounded-xl shadow-lg mb-6 overflow-x-auto">
      <h2 class="text-xl font-bold mb-4 text-indigo-600">All Students</h2>
      <table class="w-full text-left border border-gray-200 rounded">
        <thead class="bg-gray-200">
          <tr>
            <th class="px-4 py-2">Name</th>
            <th class="px-4 py-2">Email</th>
            <th class="px-4 py-2">Phone</th>
            <th class="px-4 py-2">Country</th>
            <th class="px-4 py-2">Subject</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($students as $st): ?>
            <tr class="border-b hover:bg-gray-50">
              <td class="px-4 py-2"><?= htmlspecialchars($st['name']) ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($st['email']) ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($st['phone'] ?? '-') ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($st['country'] ?? '-') ?></td>
              <td class="px-4 py-2"><?= htmlspecialchars($st['subject'] ?? '-') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Reports & Analytics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white p-6 rounded-xl shadow-lg">
        <h3 class="text-lg font-bold mb-4 text-indigo-600">Stage Progress</h3>
        <canvas id="stageChart"></canvas>
      </div>
      <div class="bg-white p-6 rounded-xl shadow-lg">
        <h3 class="text-lg font-bold mb-4 text-indigo-600">Documents Uploaded</h3>
        <canvas id="docChart"></canvas>
      </div>
      <div class="bg-white p-6 rounded-xl shadow-lg">
        <h3 class="text-lg font-bold mb-4 text-indigo-600">Applications by Country</h3>
        <canvas id="countryChart"></canvas>
      </div>
    </div>

  </div>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
  fetch("reports_api.php")
    .then(res => res.json())
    .then(data => {
      // Stage Progress Chart
      new Chart(document.getElementById("stageChart"), {
        type: "bar",
        data: {
          labels: data.stages.labels,
          datasets: [{
            label: "Students per Stage",
            data: data.stages.counts,
            backgroundColor: "rgba(99, 102, 241,0.7)"
          }]
        }
      });
      // Document Upload Chart
      new Chart(document.getElementById("docChart"), {
        type: "doughnut",
        data: {
          labels: data.docs.labels,
          datasets: [{
            data: data.docs.counts,
            backgroundColor: ["#6366F1","#10B981","#F59E0B","#EF4444"]
          }]
        }
      });
      // Country Chart
      new Chart(document.getElementById("countryChart"), {
        type: "pie",
        data: {
          labels: data.countries.labels,
          datasets: [{
            data: data.countries.counts,
            backgroundColor: ["#3B82F6","#F97316","#22C55E","#E11D48"]
          }]
        }
      });
    });
  </script>
</body>
</html>
