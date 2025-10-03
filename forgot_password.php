<?php
require_once __DIR__ . '/db.php';

$message = '';
$showForm = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if ($email === '') {
        $message = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
    } else {
        $db = get_db();
        $db->prepare('DELETE FROM password_resets WHERE expires_at < ?')->execute([time()]);

        $stmt = $db->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $db->prepare('DELETE FROM password_resets WHERE user_id = ?')->execute([$user['id']]);

            $token = bin2hex(random_bytes(32));
            $expiresAt = time() + 3600; // 1 hour

            $insert = $db->prepare('INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)');
            $insert->execute([$user['id'], $token, $expiresAt]);

            $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
            $resetLink = $scheme . '://' . $host . ($basePath ? $basePath . '/' : '/') . 'reset_password.php?token=' . urlencode($token);

            $subject = 'Password Reset Request';
            $body = "A password reset was requested for your account.\n\n" .
                "Use the link below to set a new password. This link will expire in one hour.\n\n" .
                $resetLink . "\n\n" .
                "If you did not request a reset, you can ignore this email.";

            @mail($email, $subject, $body);
        }

        $message = 'If an account with that email exists, a password reset link has been sent.';
        $showForm = false;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Forgot Password</title>
</head>
<body>
<h1>Forgot Password</h1>
<?php if ($message): ?>
<p><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>
<?php if ($showForm): ?>
<form method="post">
<label for="email">Email</label><br>
<input type="email" name="email" id="email" required><br>
<button type="submit">Send Reset Link</button>
</form>
<?php endif; ?>
<p><a href="login.php">Back to Login</a></p>
</body>
</html>
