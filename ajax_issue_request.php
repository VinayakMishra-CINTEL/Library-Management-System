<?php
session_start();
header('Content-Type: application/json');
include 'dbconnect.php';

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    echo json_encode(['success' => false, 'error' => 'You must be logged in as a student to request a book.']);
    exit();
}

$username = $_SESSION['username'];
$book_id = isset($_POST['book_id']) ? (int)$_POST['book_id'] : 0;

if (!$book_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid book ID.']);
    exit();
}

// Get student_id
$stmt = $conn->prepare("SELECT Roll_No FROM Students WHERE Username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($student_id);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Student not found.']);
    $stmt->close();
    exit();
}
$stmt->close();

// Check if book exists and copies are available
$stmt = $conn->prepare("SELECT copies_available FROM Catalog WHERE book_id = ?");
$stmt->bind_param("i", $book_id);
$stmt->execute();
$stmt->bind_result($copies_available);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Book not found.']);
    $stmt->close();
    exit();
}
$stmt->close();

if ($copies_available < 1) {
    echo json_encode(['success' => false, 'error' => 'No copies available for this book.']);
    exit();
}

// Check for existing pending/approved request
$stmt = $conn->prepare("SELECT * FROM IssueRequest WHERE student_id = ? AND book_id = ? AND status IN ('pending', 'approved')");
$stmt->bind_param("ii", $student_id, $book_id);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'You already have a pending or approved request for this book.']);
    $stmt->close();
    exit();
}
$stmt->close();

// Insert new issue request
$stmt = $conn->prepare("INSERT INTO IssueRequest (student_id, book_id, request_date, status) VALUES (?, ?, NOW(), 'pending')");
$stmt->bind_param("ii", $student_id, $book_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error submitting request.']);
}
$stmt->close(); 