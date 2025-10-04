<?php
require_once __DIR__ . '/auth.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit();
}

$message = '';
$error = '';
$showForm = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if ($email === '') {
        $error = 'Please enter the email associated with the account.';
    } else {
        $token = bin2hex(random_bytes(32));
        $host = $_SERVER['HTTP_HOST'] ?? php_uname('n');
        if (!$host) {
            $host = 'localhost.localdomain';
        }
        $host = preg_replace('/[^A-Za-z0-9.\-]/', '', $host);
        if ($host === '') {
            $host = 'localhost.localdomain';
        }
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $resetLink = sprintf('%s://%s/reset_password.php?token=%s', $scheme, $host, $token);
        $subject = 'Password Reset Request';
        $body = "Hello,\n\n" .
            "If a Terse account is associated with this address, a password reset has been requested.\n" .
            "Use the following link to reset the password:\n\n" .
            $resetLink . "\n\n" .
            "If you did not request this, you can ignore this message.";
        $fromAddress = sprintf('no-reply@%s', $host);
        $headers = sprintf("From: %s\r\nReply-To: %s", $fromAddress, $fromAddress);

        $mailSent = mail($email, $subject, $body, $headers);
        if ($mailSent) {
            $message = 'If an account matches the provided details, a reset message has been sent.';
            $showForm = false;
        } else {
            $error = 'Email delivery failed. Please try again or contact support to provide assistance.';
            error_log(sprintf('Password reset token for %s: %s', $email, $token));
        }
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
    <p><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>
<?php if ($error): ?>
    <p><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
<?php endif; ?>
<?php if ($showForm): ?>
<form method="post">
    <label for="email">Email</label><br>
    <input type="email" name="email" id="email" value="<?php echo isset($email) ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8') : ''; ?>"><br>
    <button type="submit">Send Reset Link</button>
</form>
<?php endif; ?>
</body>
</html>
