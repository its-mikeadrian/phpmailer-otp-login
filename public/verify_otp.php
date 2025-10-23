<?php
require_once __DIR__ . '/../src/security.php';
secure_session_start();
require_once __DIR__ . '/../src/db.php';
require_once __DIR__ . '/../config/env.php';

if (!isset($_SESSION['pending_user_id'])) {
    header('Location: index.php');
    exit;
}

$userId = (int) $_SESSION['pending_user_id'];
$username = isset($_SESSION['pending_username']) ? $_SESSION['pending_username'] : '';
$email = isset($_SESSION['pending_email']) ? $_SESSION['pending_email'] : '';

$feedback = '';

// Compute resend cooldown remaining seconds (server-side) using DB time to avoid TZ mismatch
$cooldownRemaining = 0;
$cooldownWindow = 60; // seconds
$cooldownStmt = $conn->prepare("SELECT TIMESTAMPDIFF(SECOND, created_at, NOW()) AS elapsed FROM login_otp WHERE user_id = ? AND is_used = 0 ORDER BY id DESC LIMIT 1");
$cooldownStmt->bind_param('i', $userId);
$cooldownStmt->execute();
$cooldownRes = $cooldownStmt->get_result();
$cooldownRow = $cooldownRes ? $cooldownRes->fetch_assoc() : null;
$cooldownStmt->close();
if ($cooldownRow && isset($cooldownRow['elapsed'])) {
    $elapsed = (int) $cooldownRow['elapsed'];
    if ($elapsed < 0) { $elapsed = 0; }
    $cooldownRemaining = max(0, $cooldownWindow - $elapsed);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation
    if (!csrf_validate()) {
        $feedback = 'Invalid request. Please refresh and try again.';
    } else {
        $otpInput = isset($_POST['otp']) ? preg_replace('/\D+/', '', trim($_POST['otp'])) : '';
        if ($otpInput === '') {
            $feedback = 'Please enter the OTP sent to your email.';
        } else {
            // Find matching, unused, unexpired OTP
            $sql = "SELECT id, otp, expires_at, is_used, attempt_count FROM login_otp WHERE user_id = ? AND is_used = 0 ORDER BY id DESC LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $res = $stmt->get_result();
            $otpRow = $res ? $res->fetch_assoc() : null;
            $stmt->close();

            if (!$otpRow) {
                $feedback = 'No active OTP found. Please login again to request a new code.';
            } else {
                $now = time();
                $expiresTs = strtotime($otpRow['expires_at']);
                if ((int)$otpRow['attempt_count'] >= 5) {
                    $feedback = 'Too many incorrect attempts. Please login again to request a new code.';
                } elseif ($expiresTs !== false && $expiresTs < $now) {
                    $feedback = 'OTP has expired. Please login again to request a new code.';
                } elseif (!password_verify($otpInput, $otpRow['otp'])) {
                    // Increment attempt count
                    $attempts = (int) $otpRow['attempt_count'] + 1;
                    $upd = $conn->prepare('UPDATE login_otp SET attempt_count = ? WHERE id = ?');
                    $upd->bind_param('ii', $attempts, $otpRow['id']);
                    $upd->execute();
                    $upd->close();
                    $feedback = 'Incorrect OTP. Please try again.';
                } else {
                    // Mark used and finalize login
                    $upd = $conn->prepare('UPDATE login_otp SET is_used = 1 WHERE id = ?');
                    $upd->bind_param('i', $otpRow['id']);
                    $upd->execute();
                    $upd->close();

                    // Regenerate session ID to prevent fixation, set auth and clear pending
                    session_regenerate_id(true);
                    $_SESSION['auth_user_id'] = $userId;
                    unset($_SESSION['pending_user_id'], $_SESSION['pending_username'], $_SESSION['pending_email']);
                    if (!empty($_SESSION['pending_remember_me'])) {
                        remember_me_set($userId);
                        unset($_SESSION['pending_remember_me']);
                    }

                    $_SESSION['success'] = 'Login successful!';
                    header('Location: welcome.php');
                    exit;
                }
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h2 class="card-title mb-4">Verify OTP</h2>

                        <?php if (!empty($feedback)): ?>
                            <div class="alert alert-info" role="alert">
                                <?php echo htmlspecialchars($feedback); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo htmlspecialchars($_SESSION['success']);
                                unset($_SESSION['success']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($_SESSION['error']);
                                unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" id="otpForm" class="mb-3" action="verify_otp.php">
                            <?php echo csrf_input(); ?>
                            <div class="mb-3">
                                <label for="otp" class="form-label">One-Time Password</label>
                                <input type="text" name="otp" id="otp" class="form-control" maxlength="6" required placeholder="Enter the 6-digit code">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Verify</button>
                        </form>

                        <form method="POST" action="resend_otp.php" class="mt-2">
                            <?php echo csrf_input(); ?>
                            <button type="submit" id="resendBtn" class="btn btn-link p-0" <?php echo ($cooldownRemaining > 0) ? 'disabled' : ''; ?>>Resend OTP</button>
                            <div>
                                <small id="resendCountdown" data-remaining="<?php echo (int)$cooldownRemaining; ?>" class="text-muted" style="<?php echo ($cooldownRemaining > 0) ? '' : 'display:none;'; ?>">
                                    Resend available in <?php echo (int)$cooldownRemaining; ?> seconds
                                </small>
                            </div>
                        </form>

                        <div class="mt-3">
                            <a href="index.php">Back to Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('resendBtn');
        var countdownEl = document.getElementById('resendCountdown');
        var remaining = 0;
        if (countdownEl) {
            var d = countdownEl.getAttribute('data-remaining');
            remaining = parseInt(d || '0', 10);
            if (isNaN(remaining) || remaining < 0) remaining = 0;
        }

        function render() {
            if (!btn || !countdownEl) return;
            if (remaining > 0) {
                btn.setAttribute('disabled', 'disabled');
                countdownEl.style.display = '';
                countdownEl.textContent = 'Resend available in ' + remaining + ' seconds';
            } else {
                btn.removeAttribute('disabled');
                countdownEl.style.display = 'none';
            }
        }

        render();
        if (remaining > 0) {
            var timer = setInterval(function () {
                remaining = remaining - 1;
                if (remaining <= 0) {
                    remaining = 0;
                    clearInterval(timer);
                }
                render();
            }, 1000);
        }
    });
    </script>
</body>

</html>