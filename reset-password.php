<?php
require_once 'includes/db.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';
require 'includes/PHPMailer/src/Exception.php';

$email = $_GET['email'] ?? $_POST['email'] ?? '';
$error = '';
$success = '';

if (isset($_GET['resend']) && $email) {

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email=?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $otp = rand(100000, 999999);
        $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

        $stmt = $pdo->prepare("UPDATE users SET otp_code=?, otp_expires=? WHERE email=?");
        $stmt->execute([$otp, $expiry, $email]);

        // send mail
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'pantrychefweb@gmail.com';
            $mail->Password = 'qqwc mwfl vxwi huzj';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('pantrychefweb@gmail.com', 'PantryChef');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Resent OTP';
            $mail->Body = "
            <!DOCTYPE html>
            <html>
            <head>
            <meta charset='UTF-8'>
            </head>
            <body style='margin:0; padding:0; background:#4b5c09; font-family: Arial, sans-serif;'>

            <table width='100%' cellpadding='0' cellspacing='0' style='padding:40px 0;'>
                <tr>
                <td align='center'>

                    <table width='400' cellpadding='0' cellspacing='0' 
                        style='background:#ffffff; border-radius:16px; padding:30px; text-align:center; box-shadow:0 10px 30px rgba(0,0,0,0.2);'>

                    <!-- Logo -->
                    <tr>
                        <td style='font-size:22px; font-weight:bold; color:#333;'>
                        🍳 Pantry<span style='color:#273e06;'>Chef</span>
                        </td>
                    </tr>

                    <!-- Title -->
                    <tr>
                        <td style='padding-top:15px; font-size:18px; color:#555;'>
                        Password Reset Request
                        </td>
                    </tr>

                    <!-- OTP Box -->
                    <tr>
                        <td style='padding:25px 0;'>
                        <div style='display:inline-block; background:#fff4ef; color:#ff6b35; 
                                    font-size:28px; font-weight:bold; letter-spacing:4px; 
                                    padding:15px 25px; border-radius:12px; border:1px dashed #273e06;'>
                            $otp
                        </div>
                        </td>
                    </tr>

                    <!-- Message -->
                    <tr>
                        <td style='font-size:14px; color:#666; line-height:1.6;'>
                        Use this OTP to reset your password.<br>
                        This code will expire in <strong>10 minutes</strong>.
                        </td>
                    </tr>

                    <!-- Divider -->
                    <tr>
                        <td style='padding:20px 0;'>
                        <hr style='border:none; border-top:1px solid #eee;'>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style='font-size:12px; color:#999;'>
                        If you didn’t request this, you can safely ignore this email.
                        </td>
                    </tr>

                    </table>

                </td>
                </tr>
            </table>

            </body>
            </html>
            ";

            $mail->send();
            $success = "OTP resent successfully!";
        } catch (Exception $e) {
            $error = "Failed to resend OTP.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {

    $otp = $_POST['otp'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if (!$otp || !$password || !$confirm) {
        $error = "All fields are required.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT otp_code, otp_expires FROM users WHERE email=?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || $user['otp_code'] != $otp) {
            $error = "Invalid OTP.";
        } elseif (strtotime($user['otp_expires']) < time()) {
            $error = "OTP expired.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("UPDATE users SET password=?, otp_code=NULL, otp_expires=NULL WHERE email=?");
            $stmt->execute([$hashed, $email]);

            header("Location: login.php?reset=success");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password — PantryChef</title>
<link rel="stylesheet" href="<?= ASSETS ?>/css/main.css">
</head>
<body>

<div class="auth-page">
  <div class="auth-card">

    <div class="auth-logo">
      <span class="logo-icon">🔒</span>
      <span class="logo-text">Pantry<strong>Chef</strong></span>
      <p>Enter OTP & reset password</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert--error"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert" style="background:#e6ffed;color:#155724;border:1px solid #b7ebc6;">
        <?= $success ?>
      </div>
    <?php endif; ?>

    <form method="POST">

      <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>">

      <div class="form-group">
        <label class="form-label">OTP</label>
        <input type="text" name="otp" class="form-input" placeholder="Enter OTP" required>
      </div>

      <div style="text-align:center; margin-top:8px;">
        <a href="?email=<?= urlencode($email) ?>&resend=1"
           id="resendBtn"
           class="auth-link"
           style="font-size:13px;">
           Resend OTP
        </a>
        <p id="timer" style="font-size:12px; opacity:0.7;"></p>
      </div>

      <div class="form-group">
        <label class="form-label">New Password</label>
        <input type="password" name="password" class="form-input" required>
      </div>

      <div class="form-group">
        <label class="form-label">Confirm Password</label>
        <input type="password" name="confirm" class="form-input" required>
      </div>

      <button class="btn btn-primary btn-lg" style="width:100%">
        Reset Password →
      </button>

    </form>

    <div class="auth-footer">
      <a href="<?= BASE_URL ?>login.php">Back to Login</a>
    </div>

  </div>
</div>

<script>
let resendBtn = document.getElementById("resendBtn");
let timerText = document.getElementById("timer");

let seconds = 30;

function startTimer() {
    resendBtn.style.pointerEvents = "none";
    resendBtn.style.opacity = "0.5";

    let interval = setInterval(() => {
        seconds--;
        timerText.innerText = "Resend available in " + seconds + "s";

        if (seconds <= 0) {
            clearInterval(interval);
            resendBtn.style.pointerEvents = "auto";
            resendBtn.style.opacity = "1";
            timerText.innerText = "";
        }
    }, 1000);
}

startTimer();
</script>

</body>
</html>