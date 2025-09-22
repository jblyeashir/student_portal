<?php
session_start();
require_once 'functions.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: " . ($_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'student_dashboard.php'));
    exit;
}

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $user = getUserByEmail($email);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        header("Location: " . ($user['role'] === 'admin' ? 'admin_dashboard.php' : 'student_dashboard.php'));
        exit;
    } else {
        $error = "Invalid email or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Student Consultancy Portal - Login</title>
  <!-- Tailwind CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* Subtle fade-in animation for the card */
    .fade-in {
      animation: fadeIn 0.8s ease forwards;
    }
    @keyframes fadeIn {
      0% { opacity: 0; transform: translateY(-20px); }
      100% { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body class="bg-gradient-to-r from-blue-500 to-indigo-600 flex items-center justify-center min-h-screen font-sans">

  <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl p-8 md:p-12 fade-in transform transition-transform hover:-translate-y-1">
    <h2 class="text-3xl font-bold text-center mb-6 text-indigo-700">Welcome Back</h2>
    <p class="text-center text-gray-500 mb-6">Login to access your student consultancy portal</p>

    <?php if ($error): ?>
      <div class="bg-red-100 text-red-700 p-3 rounded mb-4 shadow-sm animate-pulse">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="post" class="space-y-5">
      <div>
        <label class="block text-gray-700 font-medium mb-1">Email</label>
        <input type="email" name="email" required placeholder="you@example.com"
               class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400 transition">
      </div>
      <div>
        <label class="block text-gray-700 font-medium mb-1">Password</label>
        <input type="password" name="password" required placeholder="********"
               class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400 transition">
      </div>
      <button type="submit"
              class="w-full bg-indigo-600 text-white font-semibold p-3 rounded-lg hover:bg-indigo-700 transition-all shadow hover:shadow-lg">
        Login
      </button>
    </form>

    <div class="text-center mt-6 text-gray-500">
      Don’t have an account? 
      <a href="register.php" class="text-indigo-600 font-semibold hover:underline">Register here</a>
    </div>

    <div class="mt-8 text-center text-gray-400 text-sm">
      &copy; <?= date('Y') ?> NR Consultancy. All rights reserved.
    </div>
  </div>

</body>
</html>
