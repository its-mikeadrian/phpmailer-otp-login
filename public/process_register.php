<?php
require_once __DIR__ . '/../src/security.php';
secure_session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = 'Invalid request method.';
    header('Location: register.php');
    exit;
}

if (!csrf_validate()) {
    $_SESSION['error'] = 'Invalid request. Please refresh and try again.';
    header('Location: register.php');
    exit;
}

require_once __DIR__ . '/../src/db.php';

// Collect and validate inputs
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

if ($username === '' || $email === '' || $password === '' || $confirm_password === '') {
    $_SESSION['error'] = 'All fields are required.';
    header('Location: register.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Invalid email address.';
    header('Location: register.php');
    exit;
}

if ($password !== $confirm_password) {
    $_SESSION['error'] = 'Passwords do not match.';
    header('Location: register.php');
    exit;
}

if (strlen($username) < 3) {
    $_SESSION['error'] = 'Username must be at least 3 characters.';
    header('Location: register.php');
    exit;
}

if (strlen($password) < 6) {
    $_SESSION['error'] = 'Password must be at least 6 characters.';
    header('Location: register.php');
    exit;
}

// Ensure users table exists (minimal schema for this app)
$createUsersSql = "CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (!$conn->query($createUsersSql)) {
    error_log('Failed to ensure users table exists: ' . $conn->error);
    $_SESSION['error'] = 'Server error. Please try again later.';
    header('Location: register.php');
    exit;
}

// Check for duplicate username
$dupUserStmt = $conn->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
$dupUserStmt->bind_param('s', $username);
$dupUserStmt->execute();
$dupUserStmt->store_result();
if ($dupUserStmt->num_rows > 0) {
    $_SESSION['error'] = 'Username is already taken.';
    $dupUserStmt->close();
    header('Location: register.php');
    exit;
}
$dupUserStmt->close();

// Check for duplicate email
$dupEmailStmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$dupEmailStmt->bind_param('s', $email);
$dupEmailStmt->execute();
$dupEmailStmt->store_result();
if ($dupEmailStmt->num_rows > 0) {
    $_SESSION['error'] = 'Email is already registered.';
    $dupEmailStmt->close();
    header('Location: register.php');
    exit;
}
$dupEmailStmt->close();

$hashed = password_hash($password, PASSWORD_DEFAULT);

$insertStmt = $conn->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
$insertStmt->bind_param('sss', $username, $email, $hashed);

if (!$insertStmt->execute()) {
    error_log('Failed to insert user: ' . $insertStmt->error);
    $_SESSION['error'] = 'Failed to create account. Please try again.';
    $insertStmt->close();
    header('Location: register.php');
    exit;
}

$insertStmt->close();

$_SESSION['success'] = 'Account created successfully. You can now log in.';
header('Location: index.php');
exit;
