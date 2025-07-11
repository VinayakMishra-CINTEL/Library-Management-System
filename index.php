<?php
session_start();
include 'dbconnect.php';

$admin_error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['admin_id']) && isset($_POST['password'])) {
    $admin_id = $conn->real_escape_string($_POST['admin_id']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM Admins WHERE admin_id = '$admin_id'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows == 1) {
        $row = $result->fetch_assoc();
        // Compare plain text password (for development only)
        if ($password === $row['password']) {
            $_SESSION['admin_id'] = $row['admin_id'];
            header("Location: admin/admin_dashboard.php");
            exit();
        } else {
            $admin_error = "Invalid Admin ID or password.";
        }
    } else {
        $admin_error = "Invalid Admin ID or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Library Management System - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-100 to-cyan-100 min-h-screen flex flex-col dark:bg-gray-900 dark:text-gray-100 transition-colors">
    <div class="flex-1 flex flex-col items-center justify-center py-8">
        <!-- Hero Section -->
        <div class="w-full max-w-3xl mx-auto mb-8 p-6 md:p-10 bg-white/80 dark:bg-gray-800/80 rounded-2xl shadow-2xl animate-fadeInUp relative">
            <button onclick="document.body.classList.toggle('dark')" title="Toggle dark mode" class="absolute top-6 right-6 text-cyan-500 hover:text-blue-600 text-2xl focus:outline-none">
                <i class="fas fa-moon"></i>
            </button>
            <div class="flex flex-col items-center mb-6">
                <span class="text-6xl text-blue-500 dark:text-cyan-400 mb-2"><i class="fas fa-book-open-reader"></i></span>
                <h1 class="text-4xl md:text-5xl font-extrabold text-center text-blue-700 dark:text-cyan-400 mb-2 tracking-tight">Library Management System</h1>
                <p class="text-lg text-gray-600 dark:text-gray-300 text-center max-w-xl mb-2">Effortlessly manage your library, issue and return books, and stay updated with notifications. Fast, secure, and user-friendly for both students and admins.</p>
            </div>
            <!-- Feature Highlights -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <div class="flex flex-col items-center bg-blue-50 dark:bg-gray-700 rounded-xl p-4 shadow">
                    <span class="text-2xl text-blue-500 mb-2"><i class="fas fa-user-shield"></i></span>
                    <span class="font-semibold text-blue-700 dark:text-cyan-300">Admin Panel</span>
                    <span class="text-xs text-gray-500 dark:text-gray-300 text-center">Manage books, students, and notifications</span>
                </div>
                <div class="flex flex-col items-center bg-green-50 dark:bg-gray-700 rounded-xl p-4 shadow">
                    <span class="text-2xl text-green-500 mb-2"><i class="fas fa-users"></i></span>
                    <span class="font-semibold text-green-700 dark:text-green-300">Student Portal</span>
                    <span class="text-xs text-gray-500 dark:text-gray-300 text-center">Browse, request, and track books easily</span>
                </div>
                <div class="flex flex-col items-center bg-yellow-50 dark:bg-gray-700 rounded-xl p-4 shadow">
                    <span class="text-2xl text-yellow-500 mb-2"><i class="fas fa-bell"></i></span>
                    <span class="font-semibold text-yellow-700 dark:text-yellow-300">Smart Notifications</span>
                    <span class="text-xs text-gray-500 dark:text-gray-300 text-center">Stay updated on requests and returns</span>
                </div>
            </div>
            <!-- Login Cards -->
            <div class="flex flex-col md:flex-row gap-8 md:gap-12 justify-center">
                <!-- Admin Login -->
                <div class="flex-1 bg-blue-50 dark:bg-gray-700 rounded-xl p-6 shadow-md">
                    <h2 class="text-xl font-semibold text-blue-600 dark:text-cyan-300 mb-4 text-center flex items-center justify-center gap-2"><i class="fas fa-user-shield"></i> Admin Login</h2>
                    <form action="index.php" method="post" class="space-y-4">
                        <input type="text" name="admin_id" placeholder="Admin Username" required class="w-full px-4 py-2 border-2 border-blue-200 dark:border-cyan-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 dark:bg-gray-800 dark:text-gray-100 transition" />
                        <input type="password" name="password" placeholder="Password" required class="w-full px-4 py-2 border-2 border-blue-200 dark:border-cyan-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 dark:bg-gray-800 dark:text-gray-100 transition" />
                        <button type="submit" class="w-full py-2 bg-gradient-to-r from-blue-500 to-cyan-500 text-white font-semibold rounded-lg shadow hover:from-blue-600 hover:to-cyan-600 transition text-lg tracking-wide flex items-center justify-center gap-2"><i class="fas fa-sign-in-alt"></i> Login as Admin</button>
                    </form>
                    <?php if ($admin_error): ?>
                        <div class="mt-4 p-2 bg-red-100 text-red-700 rounded-lg text-center font-semibold dark:bg-red-900 dark:text-red-200">
                            <?php echo htmlspecialchars($admin_error); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- User Login -->
                <div class="flex-1 bg-green-50 dark:bg-gray-700 rounded-xl p-6 shadow-md">
                    <h2 class="text-xl font-semibold text-green-600 dark:text-green-300 mb-4 text-center flex items-center justify-center gap-2"><i class="fas fa-user"></i> Student Login</h2>
                    <form action="login.php" method="post" class="space-y-4">
                        <input type="text" name="username" placeholder="User ID" required class="w-full px-4 py-2 border-2 border-green-200 dark:border-green-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400 dark:bg-gray-800 dark:text-gray-100 transition" />
                        <input type="password" name="password" placeholder="Password" required class="w-full px-4 py-2 border-2 border-green-200 dark:border-green-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-400 dark:bg-gray-800 dark:text-gray-100 transition" />
                        <button type="submit" class="w-full py-2 bg-gradient-to-r from-green-500 to-emerald-500 text-white font-semibold rounded-lg shadow hover:from-green-600 hover:to-emerald-600 transition text-lg tracking-wide flex items-center justify-center gap-2"><i class="fas fa-sign-in-alt"></i> Login as Student</button>
                    </form>
                </div>
            </div>
            <a href="signup.php" class="block mt-10 mx-auto w-max py-3 px-8 bg-gradient-to-r from-green-500 to-emerald-500 text-white font-bold rounded-lg shadow hover:from-green-600 hover:to-emerald-600 transition text-lg tracking-wide text-center"><i class="fas fa-user-plus mr-2"></i>Student Signup</a>
        </div>
    </div>
    <footer class="w-full text-center py-4 text-gray-500 dark:text-gray-400 text-xs mt-8">
        &copy; <?php echo date('Y'); ?> Library Management System. All rights reserved.
    </footer>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"></script>
    <style>
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(40px);} to { opacity: 1; transform: translateY(0);} }
        .animate-fadeInUp { animation: fadeInUp 0.7s; }
    </style>
</body>
</html> 