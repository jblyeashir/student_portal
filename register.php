<?php
session_start();
require_once 'functions.php';

$error = ""; // <-- Initialize here

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    if ($password !== $confirm) {
        $error = "Passwords do not match!";
    } elseif (getUserByEmail($email)) {
        $error = "Email already registered!";
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $db->prepare("INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)")
           ->execute([$name,$email,$hash,'student']);
        $newId = $db->lastInsertId();
        $db->prepare("INSERT INTO students (user_id) VALUES (?)")->execute([$newId]);

        $_SESSION['user_id'] = $newId;
        $_SESSION['role'] = "student";
        header("Location: student_dashboard.php");
        exit;
    }
}
?>


<!DOCTYPE html>
<html>
<head>
  <title>Register - Student Portal</title>
  <link rel="stylesheet" href="style.css">
  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-green-400 to-blue-500 flex items-center justify-center min-h-screen font-sans">

  <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-8 md:p-12 fade-in transform transition-transform hover:-translate-y-1">
    <h2 class="text-3xl font-bold text-center mb-6 text-green-600">Create Your Account</h2>
    <p class="text-center text-gray-500 mb-6">Sign up to manage your applications</p>

    <?php if ($error): ?>
      <div class="bg-red-100 text-red-700 p-3 rounded mb-4 shadow-sm animate-pulse">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="post" class="space-y-5">
      <div>
        <label class="block text-gray-700 font-medium mb-1">Name</label>
        <input type="text" name="name" placeholder="John Doe"
               class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400 transition">
      </div>
      <div>
        <label class="block text-gray-700 font-medium mb-1">Email</label>
        <input type="email" name="email" placeholder="you@example.com"
               class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400 transition">
      </div>
      <div>
        <label class="block text-gray-700 font-medium mb-1">Password</label>
        <input type="password" name="password" placeholder="********"
               class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400 transition">
      </div>
      <div>
        <label class="block text-gray-700 font-medium mb-1">Confirm Password</label>
        <input type="password" name="confirm" placeholder="********"
               class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400 transition">
      </div>
      <button type="submit"
              class="w-full bg-green-600 text-white font-semibold p-3 rounded-lg hover:bg-green-700 transition-all shadow hover:shadow-lg">
        Register
      </button>
    </form>

    <div class="text-center mt-6 text-gray-500">
      Already have an account? 
      <a href="index.php" class="text-green-600 font-semibold hover:underline">Login</a>
    </div>
  </div>
</body>

</html>
