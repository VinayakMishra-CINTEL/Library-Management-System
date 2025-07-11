<?php
session_start();
include 'dbconnect.php';

// Check if user is logged in and role is 'user'
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Fetch all books from Catalog table
$sql = "SELECT * FROM Catalog ORDER BY title ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Books - Library DBMS</title>
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
                <span class="text-4xl text-green-500 dark:text-green-400 mb-2"><i class="fas fa-book"></i></span>
                <h1 class="text-3xl font-bold text-green-700 dark:text-green-400 mb-2 text-center">Books Catalog</h1>
                <p class="text-gray-600 dark:text-gray-300 text-center">Browse all available books in the library</p>
            </div>

            <!-- Summary Card -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-green-50 dark:bg-gray-700 rounded-xl p-6 shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-green-600 dark:text-green-300">Total Books</h3>
                            <p class="text-2xl font-bold text-green-700 dark:text-green-400"><?php echo $result ? $result->num_rows : 0; ?></p>
                        </div>
                        <span class="text-3xl text-green-500"><i class="fas fa-books"></i></span>
                    </div>
                </div>
                
                <div class="bg-blue-50 dark:bg-gray-700 rounded-xl p-6 shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-blue-600 dark:text-blue-300">Available Copies</h3>
                            <p class="text-2xl font-bold text-blue-700 dark:text-blue-400">
                                <?php 
                                $total_copies = 0;
                                if ($result) {
                                    $result->data_seek(0);
                                    while ($row = $result->fetch_assoc()) {
                                        $total_copies += $row['copies_available'];
                                    }
                                    $result->data_seek(0);
                                }
                                echo $total_copies;
                                ?>
                            </p>
                        </div>
                        <span class="text-3xl text-blue-500"><i class="fas fa-layer-group"></i></span>
                    </div>
                </div>
                
                <div class="bg-purple-50 dark:bg-gray-700 rounded-xl p-6 shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-purple-600 dark:text-purple-300">Categories</h3>
                            <p class="text-2xl font-bold text-purple-700 dark:text-purple-400">
                                <?php 
                                $publishers = [];
                                if ($result) {
                                    $result->data_seek(0);
                                    while ($row = $result->fetch_assoc()) {
                                        if (!in_array($row['publisher'], $publishers)) {
                                            $publishers[] = $row['publisher'];
                                        }
                                    }
                                    $result->data_seek(0);
                                }
                                echo count($publishers);
                                ?>
                            </p>
                        </div>
                        <span class="text-3xl text-purple-500"><i class="fas fa-tags"></i></span>
                    </div>
                </div>
            </div>

            <!-- Books Table -->
            <div class="overflow-x-auto rounded-xl shadow mb-8">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-green-600 dark:bg-green-700 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Book ID</th>
                            <th class="px-4 py-3 text-left font-semibold">Title</th>
                            <th class="px-4 py-3 text-left font-semibold">Author</th>
                            <th class="px-4 py-3 text-left font-semibold">Publisher</th>
                            <th class="px-4 py-3 text-left font-semibold">Year</th>
                            <th class="px-4 py-3 text-left font-semibold">Copies Available</th>
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
                                        <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold dark:bg-green-900 dark:text-green-200">
                                            <?php echo htmlspecialchars($row['copies_available']); ?> copies
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-8 text-gray-500 dark:text-gray-400">
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

            <!-- Search and Filter Options -->
            <div class="bg-blue-50 dark:bg-gray-700 rounded-xl p-6 shadow-md">
                <h3 class="text-lg font-semibold text-blue-700 dark:text-blue-300 mb-3 flex items-center gap-2">
                    <i class="fas fa-search"></i> Quick Actions
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center">
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-2">Want to borrow a book?</p>
                        <a href="issue_requests.php" class="inline-block px-4 py-2 bg-blue-500 text-white font-semibold rounded-lg shadow hover:bg-blue-600 transition">
                            <i class="fas fa-hand-holding-heart"></i> Request Book
                        </a>
                    </div>
                    <div class="text-center">
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-2">Check your current loans</p>
                        <a href="student_dashboard.php" class="inline-block px-4 py-2 bg-green-500 text-white font-semibold rounded-lg shadow hover:bg-green-600 transition">
                            <i class="fas fa-list"></i> My Dashboard
                        </a>
                    </div>
                    <div class="text-center">
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-2">Recommend a book</p>
                        <a href="recommended_books.php" class="inline-block px-4 py-2 bg-yellow-500 text-white font-semibold rounded-lg shadow hover:bg-yellow-600 transition">
                            <i class="fas fa-star"></i> Recommendations
                        </a>
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
