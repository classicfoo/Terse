<?php
require_once __DIR__ . '/db.php';

$db = get_db();
$message = '';
$token = $_GET['token'] ?? ($_POST['token'] ?? '');
$token = trim($token);
$showForm = true;

if ($token === '') {
    $message = 'Invalid password reset token.';
    $showForm = false;
} else {
    $stmt = $db->prepare('SELECT pr.user_id, pr.expires_at, u.email FROM password_resets pr JOIN users u ON pr.user_id = u.id WHERE pr.token = ?');
    $stmt->execute([$token]);
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset || $reset['expires_at'] < time()) {
        $message = 'This password reset link is invalid or has expired.';
        $showForm = false;
        if ($reset) {
            $db->prepare('DELETE FROM password_resets WHERE token = ?')->execute([$token]);
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if ($new_password === '') {
            $message = 'Please enter a new password.';
        } elseif ($new_password !== $confirm_password) {
            $message = 'Passwords do not match.';
        } else {
            $update = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
            $update->execute([password_hash($new_password, PASSWORD_DEFAULT), $reset['user_id']]);

            $db->prepare('DELETE FROM password_resets WHERE user_id = ?')->execute([$reset['user_id']]);

            $message = 'Your password has been reset. You can now log in.';
            $showForm = false;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reset Password</title>
</head>
<body>
<h1>Reset Password</h1>
<?php if ($message): ?>
<p><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>
<?php if ($showForm): ?>
<form method="post">
<input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
<label for="new_password">New Password</label><br>
<input type="password" name="new_password" id="new_password" required><br>
<label for="confirm_password">Confirm Password</label><br>
<input type="password" name="confirm_password" id="confirm_password" required><br>
<button type="submit">Reset Password</button>
</form>
<?php endif; ?>
<p><a href="login.php">Return to Login</a></p>
</body>
</html>
