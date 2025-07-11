<?php
session_start();
include 'dbconnect.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch student details (including photo)
$stmt = $conn->prepare("SELECT Roll_No, Name, photo FROM Students WHERE Username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($roll_no, $name, $photo);
$stmt->fetch();
$stmt->close();

// Fetch notifications
$notifications = [];
$stmt = $conn->prepare("SELECT Message, sent_date FROM Notifications WHERE Roll_No = ? ORDER BY sent_date DESC LIMIT 5");
$stmt->bind_param("i", $roll_no);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
$stmt->close();

// Fetch currently issued books
$issued_books = [];
$stmt = $conn->prepare("SELECT c.title, c.author, ib.issue_date, ib.due_date FROM IssuedBooks ib JOIN Catalog c ON ib.book_id = c.book_id WHERE ib.student_id = ? AND ib.due_date >= NOW()");
$stmt->bind_param("i", $roll_no);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $issued_books[] = $row;
}
$stmt->close();

// Fetch previously borrowed books
$prev_books = [];
$stmt = $conn->prepare("SELECT c.title, c.author, ib.issue_date, ib.due_date FROM IssuedBooks ib JOIN Catalog c ON ib.book_id = c.book_id WHERE ib.student_id = ? AND ib.due_date < NOW()");
$stmt->bind_param("i", $roll_no);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $prev_books[] = $row;
}
$stmt->close();

// Penalty automation: add penalty for overdue books if not already present
$penalty_amount = 50; // Default penalty, can be set by admin
$overdue = $conn->query("SELECT issued_id, book_id FROM IssuedBooks WHERE student_id = $roll_no AND due_date < NOW()");
if ($overdue) {
    while ($row = $overdue->fetch_assoc()) {
        $issued_id = $row['issued_id'];
        // Check if penalty already exists for this issued book
        $check = $conn->prepare("SELECT 1 FROM Penalty WHERE issued_id = ? AND student_id = ?");
        $check->bind_param("ii", $issued_id, $roll_no);
        $check->execute();
        $check->store_result();
        if ($check->num_rows == 0) {
            // Insert penalty
            $insert = $conn->prepare("INSERT INTO Penalty (student_id, issued_id, amount, status) VALUES (?, ?, ?, 'Pending')");
            $insert->bind_param("iii", $roll_no, $issued_id, $penalty_amount);
            $insert->execute();
            $insert->close();
        }
        $check->close();
    }
}

// Fetch all books count
$books_count = 0;
$res = $conn->query("SELECT COUNT(*) as cnt FROM Catalog");
if ($row = $res->fetch_assoc()) $books_count = $row['cnt'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard - Library DBMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-100 to-cyan-100 min-h-screen text-gray-800 dark:bg-gray-900 dark:text-gray-100 transition-colors">
    <div class="flex flex-col items-center min-h-screen py-10">
        <div class="w-full max-w-4xl p-8 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl animate-fadeInUp">
            <div class="flex flex-col items-center mb-6">
                <div class="relative mb-4">
                    <img src="<?php echo $photo ? htmlspecialchars($photo) : 'https://ui-avatars.com/api/?name=' . urlencode($name ?: $username); ?>" alt="User Photo" class="w-32 h-32 rounded-full object-cover border-4 border-blue-300 dark:border-cyan-700 shadow-lg" />
                    <form method="post" action="upload_photo.php" enctype="multipart/form-data" class="absolute bottom-0 right-0">
                        <label class="cursor-pointer bg-blue-500 hover:bg-blue-600 text-white rounded-full px-3 py-1 text-xs font-semibold shadow transition">
                            Edit Photo
                            <input type="file" name="photo" accept="image/*" class="hidden" onchange="this.form.submit()" />
                        </label>
                    </form>
                </div>
                <h1 class="text-3xl font-bold text-blue-700 dark:text-cyan-400 mb-1">Welcome, <?php echo htmlspecialchars($name ?: $username); ?>!</h1>
                <button onclick="window.location.href='edit_profile.php'" class="mt-2 px-4 py-2 bg-gradient-to-r from-blue-500 to-cyan-500 text-white font-semibold rounded-lg shadow hover:from-blue-600 hover:to-cyan-600 transition">Edit Details</button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <!-- Notifications -->
                <div class="bg-blue-50 dark:bg-gray-700 rounded-xl p-6 shadow-md">
                    <h2 class="text-lg font-semibold text-blue-600 dark:text-cyan-300 mb-3">Notifications</h2>
                    <ul class="space-y-2 max-h-40 overflow-y-auto">
                        <?php if (count($notifications)): foreach ($notifications as $n): ?>
                            <li class="p-2 bg-white dark:bg-gray-800 rounded shadow text-sm flex flex-col">
                                <span><?php echo htmlspecialchars($n['Message']); ?></span>
                                <span class="text-xs text-gray-400 mt-1"><?php echo htmlspecialchars($n['sent_date']); ?></span>
                            </li>
                        <?php endforeach; else: ?>
                            <li class="text-gray-500 dark:text-gray-400">No notifications.</li>
                        <?php endif; ?>
                    </ul>
                </div>
                <!-- All Books & Recommendations -->
                <div class="flex flex-col gap-4">
                    <div class="bg-green-50 dark:bg-gray-700 rounded-xl p-6 shadow-md flex-1">
                        <h2 class="text-lg font-semibold text-green-600 dark:text-green-300 mb-3">All Books</h2>
                        <div class="text-3xl font-bold text-green-700 dark:text-green-400 mb-2"><?php echo $books_count; ?></div>
                        <a href="view_books.php" class="inline-block mt-2 px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-500 text-white font-semibold rounded-lg shadow hover:from-green-600 hover:to-emerald-600 transition">Browse Books</a>
                    </div>
                    <a href="recommended_books.php" class="block bg-yellow-50 dark:bg-gray-700 rounded-xl p-6 shadow-md flex-1 hover:bg-yellow-100 dark:hover:bg-gray-600 transition">
                        <h2 class="text-lg font-semibold text-yellow-600 dark:text-yellow-300 mb-3">Recommended Books</h2>
                        <div class="text-gray-500 dark:text-gray-400 text-sm">See and recommend books</div>
                    </a>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <!-- Currently Issued Books -->
                <div class="bg-cyan-50 dark:bg-gray-700 rounded-xl p-6 shadow-md">
                    <h2 class="text-lg font-semibold text-cyan-600 dark:text-cyan-300 mb-3">Currently Issued Books</h2>
                    <ul class="space-y-2 max-h-40 overflow-y-auto">
                        <?php if (count($issued_books)): foreach ($issued_books as $b): ?>
                            <li class="p-2 bg-white dark:bg-gray-800 rounded shadow text-sm flex flex-col">
                                <span class="font-semibold"><?php echo htmlspecialchars($b['title']); ?></span>
                                <span class="text-xs text-gray-400">By <?php echo htmlspecialchars($b['author']); ?> | Due: <?php echo htmlspecialchars($b['due_date']); ?></span>
                            </li>
                        <?php endforeach; else: ?>
                            <li class="text-gray-500 dark:text-gray-400">No currently issued books.</li>
                        <?php endif; ?>
                    </ul>
                </div>
                <!-- Previously Borrowed Books -->
                <div class="bg-purple-50 dark:bg-gray-700 rounded-xl p-6 shadow-md">
                    <h2 class="text-lg font-semibold text-purple-600 dark:text-purple-300 mb-3">Previously Borrowed Books</h2>
                    <ul class="space-y-2 max-h-40 overflow-y-auto">
                        <?php if (count($prev_books)): foreach ($prev_books as $b): ?>
                            <li class="p-2 bg-white dark:bg-gray-800 rounded shadow text-sm flex flex-col">
                                <span class="font-semibold"><?php echo htmlspecialchars($b['title']); ?></span>
                                <span class="text-xs text-gray-400">By <?php echo htmlspecialchars($b['author']); ?> | Returned: <?php echo htmlspecialchars($b['due_date']); ?></span>
                            </li>
                        <?php endforeach; else: ?>
                            <li class="text-gray-500 dark:text-gray-400">No previously borrowed books.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                <!-- Fees/Payment -->
                <div class="bg-red-50 dark:bg-gray-700 rounded-xl p-6 shadow-md">
                    <h2 class="text-lg font-semibold text-red-600 dark:text-red-300 mb-3">Fees / Payment</h2>
                    <div class="text-gray-500 dark:text-gray-400 text-sm">(Feature coming soon)</div>
                    <a href="penalty.php" class="inline-block mt-2 px-4 py-2 bg-gradient-to-r from-red-500 to-pink-500 text-white font-semibold rounded-lg shadow hover:from-red-600 hover:to-pink-600 transition">View Penalties</a>
                </div>
            </div>
            <div class="text-center mt-6">
                <a href="logout.php" class="text-blue-600 dark:text-cyan-400 font-semibold hover:underline">Logout</a>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"></script>
    <style>
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(40px);} to { opacity: 1; transform: translateY(0);} }
        .animate-fadeInUp { animation: fadeInUp 0.7s; }
    </style>
</body>
</html>
