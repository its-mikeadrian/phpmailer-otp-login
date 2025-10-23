<?php
require_once __DIR__ . '/../src/security.php';
secure_session_start();
if (!empty($_SESSION['auth_user_id'])) {
    header('Location: welcome.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SteelSync</title>
    <link rel="stylesheet" href="assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
</head>

<body class="bg-light">
    <!-- navbar removed -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h3 class="mb-4">Login</h3>
                        <?php if (!empty($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']);
                                                            unset($_SESSION['error']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']);
                                                                unset($_SESSION['success']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($_SESSION['info'])): ?>
                            <div class="alert alert-info"><?php echo htmlspecialchars($_SESSION['info']);
                                                            unset($_SESSION['info']); ?></div>
                        <?php endif; ?>
                        <form method="post" action="process_login.php" autocomplete="off">
                            <?= csrf_input(); ?>
                            <div class="form-floating mb-3">
                                <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                                <label for="username">Username</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                                <label for="password">Password</label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" value="1" id="remember_me" name="remember_me">
                                <label class="form-check-label" for="remember_me">Remember me for 30 days</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                        <div class="mt-3 text-center">
                            <a href="register.php">Create an account</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/bootstrap/bootstrap.bundle.min.js"></script>
</body>

</html>