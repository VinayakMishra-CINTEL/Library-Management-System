<?php
session_start();
include 'dbconnect.php';

if (isset($_SESSION['admin_id'])) {
    // Admin already logged in, redirect to admin dashboard
    header("Location: admin/admin_dashboard.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($name) || empty($phone) || empty($username) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if username already exists in users table
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $error = "Username already exists. Please choose another.";
        } else {
            // Insert into Students table
            $stmt_student = $conn->prepare("INSERT INTO Students (name, phone, username) VALUES (?, ?, ?)");
            $stmt_student->bind_param("sss", $name, $phone, $username);
            if ($stmt_student->execute()) {
                // Insert into users table
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt_user = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
                $stmt_user->bind_param("ss", $username, $hashed_password);
                if ($stmt_user->execute()) {
                    $success = "Student account created successfully. You can now log in.";
                } else {
                    $error = "Error creating user login: " . $conn->error;
                }
                $stmt_user->close();
            } else {
                $error = "Error creating student: " . $conn->error;
            }
            $stmt_student->close();
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Sign Up - Library DBMS</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-100 to-cyan-100 min-h-screen flex items-center justify-center dark:bg-gray-900 dark:text-gray-100 transition-colors">
    <div class="w-full max-w-md p-8 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl animate-fadeInUp">
        <h2 class="text-2xl font-bold text-blue-700 dark:text-cyan-400 mb-2 text-center">Student Sign Up</h2>
        <p class="text-gray-500 dark:text-gray-300 text-sm mb-4 text-center">Fill in the details to create your student account.</p>
        <?php if ($error): ?>
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg text-center font-semibold dark:bg-red-900 dark:text-red-200"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg text-center font-semibold dark:bg-green-900 dark:text-green-200"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <form method="post" action="signup.php" class="space-y-4">
            <div>
                <label for="name" class="block font-semibold mb-1">Student Name:</label>
                <input type="text" id="name" name="name" required class="w-full px-3 py-2 border-2 border-blue-200 dark:border-cyan-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 dark:bg-gray-700 dark:text-gray-100 transition" />
            </div>
            <div>
                <label for="phone" class="block font-semibold mb-1">Phone Number:</label>
                <input type="text" id="phone" name="phone" required class="w-full px-3 py-2 border-2 border-blue-200 dark:border-cyan-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 dark:bg-gray-700 dark:text-gray-100 transition" />
            </div>
            <div>
                <label for="username" class="block font-semibold mb-1">Username:</label>
                <input type="text" id="username" name="username" required class="w-full px-3 py-2 border-2 border-blue-200 dark:border-cyan-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 dark:bg-gray-700 dark:text-gray-100 transition" />
            </div>
            <div>
                <label for="password" class="block font-semibold mb-1">Password:</label>
                <input type="password" id="password" name="password" required class="w-full px-3 py-2 border-2 border-blue-200 dark:border-cyan-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 dark:bg-gray-700 dark:text-gray-100 transition" />
            </div>
            <div>
                <label for="confirm_password" class="block font-semibold mb-1">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required class="w-full px-3 py-2 border-2 border-blue-200 dark:border-cyan-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 dark:bg-gray-700 dark:text-gray-100 transition" />
            </div>
            <button type="submit" class="w-full py-3 bg-gradient-to-r from-blue-500 to-cyan-500 text-white font-semibold rounded-lg shadow hover:from-blue-600 hover:to-cyan-600 transition text-lg tracking-wide flex items-center justify-center gap-2">
                <i class="fas fa-user-plus"></i> Sign Up
            </button>
        </form>
        <div class="text-center mt-6">
            <a href="index.php" class="text-blue-600 dark:text-cyan-400 font-semibold hover:underline">Back to Login</a>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"></script>
    <style>
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(40px);} to { opacity: 1; transform: translateY(0);} }
        .animate-fadeInUp { animation: fadeInUp 0.7s; }
    </style>
</body>
</html>
