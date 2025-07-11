<?php
session_start();
include 'dbconnect.php';

// Check if student is logged in
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch student details
$stmt = $conn->prepare("SELECT Roll_No, Name FROM Students WHERE Username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($roll_no, $name);
$stmt->fetch();
$stmt->close();

// First, let's see what columns exist in the Penalty table
$test_query = "DESCRIBE Penalty";
$test_result = $conn->query($test_query);
$penalty_columns = [];
if ($test_result) {
    while ($row = $test_result->fetch_assoc()) {
        $penalty_columns[] = $row['Field'];
    }
}

// Handle payment submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['issued_id'])) {
    $issued_id = (int)$_POST['issued_id'];
    
    // Update penalty status to paid - we'll use the correct column name once we know it
    $stmt = $conn->prepare("UPDATE Penalty SET status = 'Paid' WHERE issued_id = ?");
    $stmt->bind_param("i", $issued_id);
    $stmt->execute();
    $stmt->close();
    
    $success = "Payment processed successfully!";
}

// Fetch penalty records for this student - using a simple query first
$penalties = [];
if (!empty($penalty_columns)) {
    // Use the first column that looks like it could be a student identifier
    $student_column = null;
    foreach ($penalty_columns as $col) {
        if (strpos(strtolower($col), 'student') !== false || strpos(strtolower($col), 'user') !== false || strpos(strtolower($col), 'roll') !== false) {
            $student_column = $col;
            break;
        }
    }
    
    if ($student_column) {
        $stmt = $conn->prepare("SELECT * FROM Penalty WHERE $student_column = ?");
        $stmt->bind_param("i", $roll_no);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $penalties[] = $row;
        }
        $stmt->close();
    }
}

// Calculate total pending amount
$total_pending = 0;
foreach ($penalties as $penalty) {
    if (strtolower($penalty['status'] ?? '') === 'pending') {
        $total_pending += $penalty['amount'] ?? 0;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Penalties - Library DBMS</title>
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
                <span class="text-4xl text-red-500 dark:text-red-400 mb-2"><i class="fas fa-exclamation-triangle"></i></span>
                <h1 class="text-3xl font-bold text-red-700 dark:text-red-400 mb-2 text-center">My Penalties</h1>
                <p class="text-gray-600 dark:text-gray-300 text-center">View and pay your overdue book fines</p>
            </div>

            <?php if (isset($success)): ?>
                <div class="mb-6 p-4 bg-green-100 text-green-700 rounded-lg text-center font-semibold dark:bg-green-900 dark:text-green-200">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

           

            <!-- Summary Card -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-blue-50 dark:bg-gray-700 rounded-xl p-6 shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-blue-600 dark:text-blue-300">Total Penalties</h3>
                            <p class="text-2xl font-bold text-blue-700 dark:text-blue-400"><?php echo count($penalties); ?></p>
                        </div>
                        <span class="text-3xl text-blue-500"><i class="fas fa-list"></i></span>
                    </div>
                </div>
                
                <div class="bg-yellow-50 dark:bg-gray-700 rounded-xl p-6 shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-yellow-600 dark:text-yellow-300">Pending Amount</h3>
                            <p class="text-2xl font-bold text-yellow-700 dark:text-yellow-400">₹<?php echo $total_pending; ?></p>
                        </div>
                        <span class="text-3xl text-yellow-500"><i class="fas fa-clock"></i></span>
                    </div>
                </div>
                
                <div class="bg-green-50 dark:bg-gray-700 rounded-xl p-6 shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-green-600 dark:text-green-300">Paid Amount</h3>
                            <p class="text-2xl font-bold text-green-700 dark:text-green-400">
                                ₹<?php echo array_sum(array_column(array_filter($penalties, function($p) { return strtolower($p['status'] ?? '') === 'paid'; }), 'amount')); ?>
                            </p>
                        </div>
                        <span class="text-3xl text-green-500"><i class="fas fa-check-circle"></i></span>
                    </div>
                </div>
            </div>

            <!-- Penalties Table -->
            <div class="overflow-x-auto rounded-xl shadow mb-8">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-red-600 dark:bg-red-700 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Fine ID</th>
                            <th class="px-4 py-3 text-left font-semibold">User ID</th>
                            <th class="px-4 py-3 text-left font-semibold">Book Info</th>
                            <th class="px-4 py-3 text-left font-semibold">Amount</th>
                            <th class="px-4 py-3 text-left font-semibold">Status</th>
                            <th class="px-4 py-3 text-left font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        <?php if (count($penalties) > 0): ?>
                            <?php foreach ($penalties as $penalty): ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                    <td class="px-4 py-3 font-semibold"><?php echo htmlspecialchars($penalty['issued_id'] ?? 'N/A'); ?></td>
                                    <td class="px-4 py-3"><?php echo htmlspecialchars($penalty['student_id'] ?? $penalty['user_id'] ?? $penalty['roll_no'] ?? 'N/A'); ?></td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-col">
                                            <span class="font-semibold">Book ID: <?php echo htmlspecialchars($penalty['issued_id'] ?? 'N/A'); ?></span>
                                            <span class="text-sm text-gray-500 dark:text-gray-400">Issued Book Reference</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="font-semibold text-red-600 dark:text-red-400">₹<?php echo htmlspecialchars($penalty['amount'] ?? '0'); ?></span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php if (strtolower($penalty['status'] ?? '') === 'pending'): ?>
                                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold dark:bg-yellow-900 dark:text-yellow-200">
                                                Pending
                                            </span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold dark:bg-green-900 dark:text-green-200">
                                                Paid
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php if (strtolower($penalty['status'] ?? '') === 'pending'): ?>
                                            <form method="post" action="penalty.php" onsubmit="return confirm('Are you sure you want to pay ₹<?php echo $penalty['amount'] ?? 0; ?> for this fine?');" class="inline">
                                                <input type="hidden" name="issued_id" value="<?php echo $penalty['issued_id'] ?? ''; ?>">
                                                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-semibold px-4 py-2 rounded-lg shadow transition flex items-center gap-2">
                                                    <i class="fas fa-credit-card"></i> Pay Now
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-green-600 dark:text-green-400 font-semibold flex items-center gap-2">
                                                <i class="fas fa-check-circle"></i> Paid
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-8 text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col items-center">
                                        <span class="text-4xl mb-2">🎉</span>
                                        <p class="text-lg font-semibold">No penalties found!</p>
                                        <p class="text-sm">You're all caught up with your library obligations.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Payment Information -->
            <?php if ($total_pending > 0): ?>
                <div class="bg-yellow-50 dark:bg-gray-700 rounded-xl p-6 shadow-md">
                    <h3 class="text-lg font-semibold text-yellow-700 dark:text-yellow-300 mb-3 flex items-center gap-2">
                        <i class="fas fa-info-circle"></i> Payment Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-gray-600 dark:text-gray-300 mb-2">
                                <strong>Total Pending Amount:</strong> ₹<?php echo $total_pending; ?>
                            </p>
                            <p class="text-gray-600 dark:text-gray-300">
                                • Click "Pay Now" next to each penalty to pay individually<br>
                                • Payments are processed immediately<br>
                                • Keep your payment confirmation for records
                            </p>
                        </div>
                        <div>
                            <p class="text-gray-600 dark:text-gray-300 mb-2">
                                <strong>Payment Methods:</strong>
                            </p>
                            <p class="text-gray-600 dark:text-gray-300">
                                • Online payment (simulated)<br>
                                • Cash payment at library counter<br>
                                • Contact library staff for assistance
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/js/all.min.js"></script>
    <style>
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(40px);} to { opacity: 1; transform: translateY(0);} }
        .animate-fadeInUp { animation: fadeInUp 0.7s; }
    </style>
</body>
</html> 