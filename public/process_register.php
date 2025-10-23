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

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

$errors = [];

// Required checks (flag each empty field individually)
if ($username === '') {
    $errors['username'] = 'Username is required.';
}
if ($email === '') {
    $errors['email'] = 'Email is required.';
}
if ($password === '') {
    $errors['password'] = 'Password is required.';
}
if ($confirm_password === '') {
    $errors['confirm_password'] = 'Confirm Password is required.';
}

// Format/consistency checks
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Invalid email address.';
}
if ($password !== '' && $confirm_password !== '' && $password !== $confirm_password) {
    $errors['password'] = 'Passwords do not match.';
    $errors['confirm_password'] = 'Passwords do not match.';
}

// Length checks
if ($username !== '' && strlen($username) < 3) {
    $errors['username'] = 'Username must be at least 3 characters.';
}
if ($password !== '' && strlen($password) < 6) {
    $errors['password'] = 'Password must be at least 6 characters.';
}

// If any validation errors, store old inputs and field errors and redirect
if (!empty($errors)) {
    $_SESSION['old'] = [
        'username' => $username,
        'email' => $email,
        'password' => $password,
        'confirm_password' => $confirm_password,
    ];
    $_SESSION['errors'] = $errors;
    // Keep the original single error message for the alert
    $_SESSION['error'] = reset($errors) ?: 'Please fix the highlighted fields.';
    header('Location: register.php');
    exit;
}

// Check for duplicate username
$dupUserStmt = $conn->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
$dupUserStmt->bind_param('s', $username);
$dupUserStmt->execute();
$dupUserStmt->store_result();
if ($dupUserStmt->num_rows > 0) {
    $_SESSION['old'] = [
        'username' => $username,
        'email' => $email,
        'password' => $password,
        'confirm_password' => $confirm_password,
    ];
    $_SESSION['errors'] = ['username' => 'Username is already taken.'];
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
    $_SESSION['old'] = [
        'username' => $username,
        'email' => $email,
        'password' => $password,
        'confirm_password' => $confirm_password,
    ];
    $_SESSION['errors'] = ['email' => 'Email is already registered.'];
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
    $_SESSION['old'] = [
        'username' => $username,
        'email' => $email,
        'password' => $password,
        'confirm_password' => $confirm_password,
    ];
    $_SESSION['errors'] = [];
    $_SESSION['error'] = 'Failed to create account. Please try again.';
    $insertStmt->close();
    header('Location: register.php');
    exit;
}

$insertStmt->close();

// Clear any retained form state on success
unset($_SESSION['old'], $_SESSION['errors']);

$_SESSION['success'] = 'Account created successfully. You can now log in.';
header('Location: index.php');
exit;
