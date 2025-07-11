<?php
session_start();
include 'dbconnect.php';

// Only allow logged-in students to recommend
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
// Get roll_no for this user
$stmt = $conn->prepare("SELECT Roll_No FROM Students WHERE Username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($roll_no);
$stmt->fetch();
$stmt->close();

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $reason = trim($_POST['reason']);
    if (empty($title)) {
        $error = "Book title is required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO recommendations (roll_no, title, author, reason) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $roll_no, $title, $author, $reason);
        if ($stmt->execute()) {
            $success = "Book recommended successfully!";
        } else {
            $error = "Error: " . $conn->error;
        }
        $stmt->close();
    }
}

// Fetch all recommendations
$recs = [];
$res = $conn->query("SELECT * FROM recommendations ORDER BY recommended_at DESC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $recs[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Recommended Books</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-yellow-50 to-yellow-100 min-h-screen flex flex-col items-center py-10">
    <div class="w-full max-w-2xl p-6 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl animate-fadeInUp mb-8">
        <h2 class="text-2xl font-bold text-yellow-700 dark:text-yellow-300 mb-4 text-center">Recommend a Book</h2>
        <?php if ($error): ?>
            <div class="mb-4 p-2 bg-red-100 text-red-700 rounded"> <?php echo htmlspecialchars($error); ?> </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="mb-4 p-2 bg-green-100 text-green-700 rounded"> <?php echo htmlspecialchars($success); ?> </div>
        <?php endif; ?>
        <form method="post" class="space-y-4">
            <div>
                <label class="block font-semibold mb-1">Book Title <span class="text-red-500">*</span></label>
                <input type="text" name="title" required class="w-full px-3 py-2 border-2 border-yellow-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 transition" />
            </div>
            <div>
                <label class="block font-semibold mb-1">Author</label>
                <input type="text" name="author" class="w-full px-3 py-2 border-2 border-yellow-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 transition" />
            </div>
            <div>
                <label class="block font-semibold mb-1">Why do you recommend this book?</label>
                <textarea name="reason" rows="2" class="w-full px-3 py-2 border-2 border-yellow-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-400 transition"></textarea>
            </div>
            <button type="submit" class="w-full py-2 px-4 bg-gradient-to-r from-yellow-500 to-yellow-400 text-white font-semibold rounded-lg shadow hover:from-yellow-600 hover:to-yellow-500 transition text-lg tracking-wide">Recommend Book</button>
        </form>
    </div>
    <div class="w-full max-w-3xl p-6 bg-yellow-50 dark:bg-gray-700 rounded-2xl shadow-xl animate-fadeInUp">
        <h2 class="text-xl font-bold text-yellow-700 dark:text-yellow-300 mb-4 text-center">All Recommended Books</h2>
        <div class="grid gap-4">
            <?php if (count($recs)): foreach ($recs as $rec): ?>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <div class="font-semibold text-lg text-yellow-800 dark:text-yellow-200"><?php echo htmlspecialchars($rec['title']); ?></div>
                        <div class="text-yellow-600 dark:text-yellow-300 text-sm mb-1">by <?php echo htmlspecialchars($rec['author']); ?></div>
                        <div class="text-gray-600 dark:text-gray-300 text-sm mb-1"><?php echo htmlspecialchars($rec['reason']); ?></div>
                    </div>
                    <div class="text-right text-xs text-gray-400 mt-2 md:mt-0">
                        Recommended by Roll No: <span class="font-bold text-yellow-700 dark:text-yellow-300"><?php echo htmlspecialchars($rec['roll_no']); ?></span><br>
                        <span><?php echo htmlspecialchars($rec['recommended_at']); ?></span>
                    </div>
                </div>
            <?php endforeach; else: ?>
                <div class="text-gray-500 dark:text-gray-400 text-center">No recommendations yet.</div>
            <?php endif; ?>
        </div>
        <div class="text-center mt-8">
            <a href="student_dashboard.php" class="text-yellow-700 dark:text-yellow-300 font-semibold hover:underline">Back to Dashboard</a>
        </div>
    </div>
</body>
</html> 