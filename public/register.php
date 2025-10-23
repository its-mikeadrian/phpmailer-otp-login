<?php
require_once __DIR__ . '/../src/security.php';
secure_session_start();
if (!empty($_SESSION['auth_user_id'])) {
    header('Location: welcome.php');
    exit;
}

$old = $_SESSION['old'] ?? [];
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['old'], $_SESSION['errors']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h3 class="mb-4">Create Account</h3>
                        <?php if (!empty($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']);
                                                            unset($_SESSION['error']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']);
                                                                unset($_SESSION['success']); ?></div>
                        <?php endif; ?>
                        <form method="post" action="process_register.php" autocomplete="off">
                            <?= csrf_input(); ?>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="username" name="username" placeholder="Username" required value="<?php echo htmlspecialchars(isset($errors['username']) ? '' : ($old['username'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                <label for="username">Username</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required value="<?php echo htmlspecialchars(isset($errors['email']) ? '' : ($old['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                <label for="email">Email</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required value="<?php echo htmlspecialchars(isset($errors['password']) ? '' : ($old['password'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                <label for="password">Password</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required value="<?php echo htmlspecialchars(isset($errors['confirm_password']) ? '' : ($old['confirm_password'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                <label for="confirm_password">Confirm Password</label>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Create Account</button>
                        </form>
                        <div class="mt-3 text-center">
                            <a href="index.php">Already have an account? Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/bootstrap/bootstrap.bundle.min.js"></script>
</body>

</html>