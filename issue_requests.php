<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in and role is 'user'
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch user student_id from Students table
$stmt = $conn->prepare("SELECT Roll_No FROM Students WHERE Username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($student_id);
if (!$stmt->fetch()) {
    // Student not found
    $stmt->close();
    die("Student record not found.");
}
$stmt->close();

// Handle issue request submission
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_id'])) {
    $book_id = (int)$_POST['book_id'];

    // Check if book exists and copies are available
    $stmt = $conn->prepare("SELECT copies_available FROM Catalog WHERE book_id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $stmt->bind_result($copies_available);
    if (!$stmt->fetch()) {
        $error = "Book not found.";
        $stmt->close();
    } else {
        $stmt->close();
        if ($copies_available < 1) {
            $error = "No copies available for this book.";
        } else {
            // Check if there is already a pending or approved request for this book by this student
            $stmt = $conn->prepare("SELECT * FROM IssueRequest WHERE student_id = ? AND book_id = ? AND status IN ('pending', 'approved')");
            $stmt->bind_param("ii", $student_id, $book_id);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $error = "You already have a pending or approved request for this book.";
                $stmt->close();
            } else {
                $stmt->close();
                // Insert new issue request with status 'pending'
                $stmt = $conn->prepare("INSERT INTO IssueRequest (student_id, book_id, request_date, status) VALUES (?, ?, NOW(), 'pending')");
                $stmt->bind_param("ii", $student_id, $book_id);
                if ($stmt->execute()) {
                    $success = "Issue request submitted successfully. Please wait for admin approval.";
                } else {
                    $error = "Error submitting request: " . $conn->error;
                }
                $stmt->close();
            }
        }
    }
}

// Fetch all books
$sql = "SELECT * FROM Catalog ORDER BY title ASC";
$result = $conn->query($sql);

// Fetch user's current requests
$stmt = $conn->prepare("SELECT ir.*, c.title, c.author FROM IssueRequest ir JOIN Catalog c ON ir.book_id = c.book_id WHERE ir.student_id = ? ORDER BY ir.request_date DESC");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$user_requests = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Issue Book Requests - Library DBMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-blue-100 to-cyan-100 min-h-screen text-gray-800 dark:bg-gray-900 dark:text-gray-100 transition-colors">
    <div class="flex flex-col items-center min-h-screen py-10">
        <div class="w-full max-w-6xl p-8 bg-white dark:bg-gray-800 rounded-2xl shadow-2xl animate-fadeInUp">
            <div class="flex justify-between items-center mb-6">
                <a href="student_dashboard.php" class="inline-flex items-center gap-2 text-blue-600 dark:text-cyan-400 font-semibold hover:underline px-4 py-2 rounded-lg bg-blue-50 dark:bg-cyan-900 hover:bg-blue-100 dark:hover:bg-cyan-800 transition">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <button onclick="document.body.classList.toggle('dark')" title="Toggle dark mode" class="text-blue-400 hover:text-blue-600 text-xl focus:outline-none px-3 py-2 rounded-lg bg-blue-50 dark:bg-cyan-900 hover:bg-blue-100 dark:hover:bg-cyan-800 transition">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
            
            <!-- Header Section -->
            <div class="flex flex-col items-center mb-8">
                <span class="text-4xl text-purple-500 dark:text-purple-400 mb-2"><i class="fas fa-hand-holding-heart"></i></span>
                <h1 class="text-3xl font-bold text-purple-700 dark:text-purple-400 mb-2 text-center">Issue Book Requests</h1>
                <p class="text-gray-600 dark:text-gray-300 text-center">Request books from the library catalog</p>
            </div>

            <!-- Messages -->
            <?php if ($error): ?>
                <div class="mb-6 p-4 bg-red-100 text-red-700 rounded-lg text-center font-semibold dark:bg-red-900 dark:text-red-200">
                    <i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-lg text-center font-semibold dark:bg-green-900 dark:text-green-200">
                    <i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-purple-50 dark:bg-gray-700 rounded-xl p-6 shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-purple-600 dark:text-purple-300">Total Books</h3>
                            <p class="text-2xl font-bold text-purple-700 dark:text-purple-400"><?php echo $result ? $result->num_rows : 0; ?></p>
                        </div>
                        <span class="text-3xl text-purple-500"><i class="fas fa-books"></i></span>
                    </div>
                </div>
                
                <div class="bg-blue-50 dark:bg-gray-700 rounded-xl p-6 shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-blue-600 dark:text-blue-300">Available Books</h3>
                            <p class="text-2xl font-bold text-blue-700 dark:text-blue-400">
                                <?php 
                                $available_books = 0;
                                if ($result) {
                                    $result->data_seek(0);
                                    while ($row = $result->fetch_assoc()) {
                                        if ($row['copies_available'] > 0) {
                                            $available_books++;
                                        }
                                    }
                                    $result->data_seek(0);
                                }
                                echo $available_books;
                                ?>
                            </p>
                        </div>
                        <span class="text-3xl text-blue-500"><i class="fas fa-check-circle"></i></span>
                    </div>
                </div>
                
                <div class="bg-yellow-50 dark:bg-gray-700 rounded-xl p-6 shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-yellow-600 dark:text-yellow-300">My Requests</h3>
                            <p class="text-2xl font-bold text-yellow-700 dark:text-yellow-400"><?php echo $user_requests->num_rows; ?></p>
                        </div>
                        <span class="text-3xl text-yellow-500"><i class="fas fa-list"></i></span>
                    </div>
                </div>
            </div>

            <!-- My Current Requests -->
            <?php if ($user_requests->num_rows > 0): ?>
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
                        <i class="fas fa-clock text-yellow-500"></i> My Current Requests
                    </h2>
                    <div class="overflow-x-auto rounded-xl shadow">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-yellow-600 dark:bg-yellow-700 text-white">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold">Book Title</th>
                                    <th class="px-4 py-3 text-left font-semibold">Author</th>
                                    <th class="px-4 py-3 text-left font-semibold">Request Date</th>
                                    <th class="px-4 py-3 text-left font-semibold">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <?php while ($request = $user_requests->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                        <td class="px-4 py-3 font-semibold"><?php echo htmlspecialchars($request['title']); ?></td>
                                        <td class="px-4 py-3"><?php echo htmlspecialchars($request['author']); ?></td>
                                        <td class="px-4 py-3"><?php echo htmlspecialchars($request['request_date']); ?></td>
                                        <td class="px-4 py-3">
                                            <?php if ($request['status'] === 'pending'): ?>
                                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold dark:bg-yellow-900 dark:text-yellow-200">
                                                    Pending
                                                </span>
                                            <?php elseif ($request['status'] === 'approved'): ?>
                                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold dark:bg-green-900 dark:text-green-200">
                                                    Approved
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold dark:bg-red-900 dark:text-red-200">
                                                    Rejected
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Available Books Table -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-4 flex items-center gap-2">
                    <i class="fas fa-book text-purple-500"></i> Available Books
                </h2>
                <div class="overflow-x-auto rounded-xl shadow">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-purple-600 dark:bg-purple-700 text-white">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">Book ID</th>
                                <th class="px-4 py-3 text-left font-semibold">Title</th>
                                <th class="px-4 py-3 text-left font-semibold">Author</th>
                                <th class="px-4 py-3 text-left font-semibold">Publisher</th>
                                <th class="px-4 py-3 text-left font-semibold">Year</th>
                                <th class="px-4 py-3 text-left font-semibold">Copies Available</th>
                                <th class="px-4 py-3 text-left font-semibold">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                        <td class="px-4 py-3 font-semibold"><?php echo htmlspecialchars($row['book_id']); ?></td>
                                        <td class="px-4 py-3">
                                            <div class="flex flex-col">
                                                <span class="font-semibold"><?php echo htmlspecialchars($row['title']); ?></span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3"><?php echo htmlspecialchars($row['author']); ?></td>
                                        <td class="px-4 py-3"><?php echo htmlspecialchars($row['publisher']); ?></td>
                                        <td class="px-4 py-3"><?php echo htmlspecialchars($row['year']); ?></td>
                                        <td class="px-4 py-3">
                                            <?php if ($row['copies_available'] > 0): ?>
                                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold dark:bg-green-900 dark:text-green-200">
                                                    <?php echo htmlspecialchars($row['copies_available']); ?> copies
                                                </span>
                                            <?php else: ?>
                                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-semibold dark:bg-red-900 dark:text-red-200">
                                                    Out of stock
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-3">
                                            <?php if ($row['copies_available'] > 0): ?>
                                                <form method="post" action="issue_requests.php" onsubmit="return confirm('Are you sure you want to request this book?');" class="inline">
                                                    <input type="hidden" name="book_id" value="<?php echo $row['book_id']; ?>">
                                                    <button type="submit" class="bg-purple-500 hover:bg-purple-600 text-white font-semibold px-4 py-2 rounded-lg shadow transition flex items-center gap-2">
                                                        <i class="fas fa-hand-holding-heart"></i> Request
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <button disabled class="bg-gray-400 text-white font-semibold px-4 py-2 rounded-lg cursor-not-allowed flex items-center gap-2">
                                                    <i class="fas fa-times"></i> Unavailable
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-gray-500 dark:text-gray-400">
                                        <div class="flex flex-col items-center">
                                            <span class="text-4xl mb-2">📚</span>
                                            <p class="text-lg font-semibold">No books found!</p>
                                            <p class="text-sm">The library catalog is currently empty.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Information Section -->
            <div class="bg-blue-50 dark:bg-gray-700 rounded-xl p-6 shadow-md">
                <h3 class="text-lg font-semibold text-blue-700 dark:text-blue-300 mb-3 flex items-center gap-2">
                    <i class="fas fa-info-circle"></i> How It Works
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <p class="text-gray-600 dark:text-gray-300 mb-2">
                            <strong>Step 1:</strong> Select a book
                        </p>
                        <p class="text-gray-600 dark:text-gray-300">
                            Browse the available books and click "Request" on the book you want to borrow.
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-600 dark:text-gray-300 mb-2">
                            <strong>Step 2:</strong> Wait for approval
                        </p>
                        <p class="text-gray-600 dark:text-gray-300">
                            Your request will be reviewed by the library admin. You'll be notified of the status.
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-600 dark:text-gray-300 mb-2">
                            <strong>Step 3:</strong> Collect your book
                        </p>
                        <p class="text-gray-600 dark:text-gray-300">
                            Once approved, you can collect your book from the library counter.
                        </p>
                    </div>
                </div>
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
