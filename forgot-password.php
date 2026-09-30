<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';
require 'includes/PHPMailer/src/Exception.php';

require_once 'includes/db.php';
require_once 'includes/auth.php';

// $sqlfortable = ALTER TABLE users 
// ADD COLUMN otp_code VARCHAR(6) NULL,
// ADD COLUMN otp_expires DATETIME NULL;

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (!$email) {
        $error = "Please enter your email.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $error = "Email not found.";
        } else {
            $otp = rand(100000, 999999);
            $expiry = date("Y-m-d H:i:s", strtotime("+10 minutes"));

            $stmt = $pdo->prepare("UPDATE users SET otp_code=?, otp_expires=? WHERE email=?");
            $stmt->execute([$otp, $expiry, $email]);

            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'pantrychefweb@gmail.com'; // your email
                $mail->Password   = 'qqwc mwfl vxwi huzj';   // app password
                $mail->SMTPSecure = 'tls';
                $mail->Port       = 587;
            
                $mail->setFrom('pantrychefweb@gmail.com', 'PantryChef');
                $mail->addAddress($email);
            
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset OTP';
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
            
                header("Location: reset-password.php?email=" . urlencode($email));
                exit;
            
            } catch (Exception $e) {
                $error = "Mailer Error: " . $mail->ErrorInfo;
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
<title>Forgot Password — PantryChef</title>
<link rel="stylesheet" href="<?= ASSETS ?>/css/main.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">
      <span class="logo-icon">🔑</span>
      <span class="logo-text">Pantry<strong>Chef</strong></span>
      <p>Reset your password</p>
    </div>

    <?php if ($error): ?>
      <div class="alert alert--error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-input" required>
      </div>

      <button class="btn btn-primary btn-lg" style="width:100%">
        Send OTP →
      </button>
    </form>

    <div class="auth-footer">
      <a href="<?= BASE_URL ?>login.php">Back to Login</a>
    </div>
  </div>
</div>
</body>
</html>