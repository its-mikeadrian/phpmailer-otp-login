<?php
require_once __DIR__ . '/../src/security.php';
secure_session_start();
if (!isset($_SESSION['auth_user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - SteelSync</title>
    <link rel="stylesheet" href="assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/custom.css">
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h3 class="mb-3">Welcome!</h3>
                        <?php if (!empty($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']);
                                                                unset($_SESSION['success']); ?></div>
                        <?php endif; ?>
                        <?php if (!empty($_SESSION['info'])): ?>
                            <div class="alert alert-info"><?php echo htmlspecialchars($_SESSION['info']);
                                                            unset($_SESSION['info']); ?></div>
                        <?php endif; ?>
                        <p>You are now logged in with OTP verification.</p>
                        <a class="btn btn-outline-secondary" href="logout.php">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/bootstrap/bootstrap.bundle.min.js"></script>
    <?= session_timeout_warn_script(60); ?>
</body>

</html>