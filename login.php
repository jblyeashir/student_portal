<?php
session_start();
require_once 'functions.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $user = getUserByEmail($email);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        // redirect based on role
        if ($user['role'] === 'student') {
            header("Location: student_dashboard.php");
        } else {
            header("Location: admin_dashboard.php");
        }
        exit;
    } else {
        $errors[] = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Student Portal</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-50 via-white to-indigo-100">
  <div class="w-full max-w-md bg-white shadow-2xl rounded-2xl p-8 transform transition duration-500 hover:scale-[1.01]">
    <div class="text-center mb-8">
      <h1 class="text-3xl font-bold text-indigo-600">Student Portal</h1>
      <p class="text-gray-500 mt-2">Sign in to continue</p>
    </div>

    <?php if (!empty($errors)): ?>
      <div class="bg-red-100 text-red-600 text-sm p-3 rounded mb-4">
        <?= implode("<br>", $errors) ?>
      </div>
    <?php endif; ?>

    <form method="post" class="space-y-5">
      <div>
        <input type="email" name="email" placeholder="Email"
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none" required>
      </div>
      <div>
        <input type="password" name="password" placeholder="Password"
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none" required>
      </div>
      <button type="submit"
              class="w-full bg-indigo-600 text-white font-semibold py-3 rounded-lg shadow-md hover:bg-indigo-700 transition duration-300">
        Login
      </button>
    </form>

    <p class="mt-6 text-center text-gray-600 text-sm">
      Don’t have an account? 
      <a href="register.php" class="text-indigo-600 font-medium hover:underline">Register here</a>
    </p>
  </div>
</body>
</html>
