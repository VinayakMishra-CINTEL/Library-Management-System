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
    $stmt->close();
    die("Student record not found.");
}
$stmt->close();

$error = '';
$success = '';

// Handle renewal request submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['issued_id'])) {
    $issued_id = (int)$_POST['issued_id'];

    // Check if renewal request already exists for this issued book and is pending or renewed
    $stmt = $conn->prepare("SELECT * FROM Renewal WHERE issued_id = ? AND status IN ('pending', 'renewed')");
    $stmt->bind_param("i", $issued_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $error = "You already have a pending or approved renewal request for this book.";
        $stmt->close();
    } else {
        $stmt->close();
        // Insert renewal request with status 'pending'
        $stmt = $conn->prepare("INSERT INTO Renewal (student_id, book_id, issued_id, renewal_date, status) VALUES (?, (SELECT book_id FROM IssuedBooks WHERE issued_id = ?), ?, NOW(), 'pending')");
        $stmt->bind_param("iii", $student_id, $issued_id, $issued_id);
        if ($stmt->execute()) {
            $success = "Renewal request submitted successfully. Please wait for admin approval.";
        } else {
            $error = "Error submitting renewal request: " . $conn->error;
        }
        $stmt->close();
    }
}

// Fetch issued books for the student that are eligible for renewal (e.g., issued more than 7 days ago)
$sql = "SELECT ib.issued_id, c.book_id, c.title, c.author, c.publisher, c.year, ib.issue_date, ib.due_date
        FROM IssuedBooks ib
        JOIN Catalog c ON ib.book_id = c.book_id
        WHERE ib.student_id = ? AND ib.due_date <= DATE_ADD(NOW(), INTERVAL 7 DAY)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book Renewals - Student</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        h2 {
            text-align: center;
            margin-top: 30px;
            color: #343a40;
            font-weight: 700;
        }
        table {
            border-collapse: collapse;
            width: 90%;
            margin: 20px auto 40px auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            border-bottom: 1px solid #dee2e6;
            padding: 12px 20px;
            text-align: left;
            color: #495057;
        }
        th {
            background-color: #007bff;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        tr:hover {
            background-color: #e9f5ff;
        }
        tr:last-child td {
            border-bottom: none;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .renew-button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        .renew-button:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }
        .message {
            max-width: 600px;
            margin: 20px auto;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }
        .error {
            background-color: #f8d7da;
            color: #842029;
        }
        .success {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        .back-link {
            display: block;
            width: 90%;
            margin: 0 auto 30px auto;
            text-align: right;
        }
        .back-link a {
            text-decoration: none;
            color: #007bff;
            font-weight: bold;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container" style="background-color: #007bff; color: white; border-radius: 8px; padding: 20px; max-width: 960px; margin: 20px auto;">
        <h2>Renew Book Requests</h2>
        <?php if ($error): ?>
            <div class="message error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="message success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <div class="back-link" style="text-align: right; margin-bottom: 20px;">
            <a href="student_dashboard.php" style="color: white; font-weight: 600; text-decoration: none;">Back to Dashboard</a>
        </div>
        <table style="background-color: white; color: black; border-radius: 8px; overflow: hidden; width: 100%;">
            <thead>
                <tr>
                    <th>Issued ID</th>
                    <th>Book ID</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Publisher</th>
                    <th>Year</th>
                    <th>Issue Date</th>
                    <th>Due Date</th>
                    <th>Request Renewal</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['issued_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['book_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo htmlspecialchars($row['author']); ?></td>
                            <td><?php echo htmlspecialchars($row['publisher']); ?></td>
                            <td><?php echo htmlspecialchars($row['year']); ?></td>
                            <td><?php echo htmlspecialchars($row['issue_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['due_date']); ?></td>
                            <td>
                                <form method="post" action="renewals.php">
                                    <input type="hidden" name="issued_id" value="<?php echo $row['issued_id']; ?>">
                                    <button type="submit" class="renew-button">Request Renewal</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align:center;">No issued books eligible for renewal.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
