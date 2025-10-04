<?php
require_once __DIR__ . '/auth.php';

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

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
        $mailSent = false;
        $deliveryError = '';

        $phpMailerClass = '\\PHPMailer\\PHPMailer\\PHPMailer';
        if (class_exists($phpMailerClass)) {
            $mailer = new $phpMailerClass(true);
            try {
                $mailer->isSMTP();

                $smtpHost = getenv('SMTP_HOST') ?: '';
                if ($smtpHost === '') {
                    throw new RuntimeException('SMTP_HOST is not configured.');
                }

                $mailer->Host = $smtpHost;
                $mailer->Port = (int) (getenv('SMTP_PORT') ?: 587);

                $encryption = strtolower((string) getenv('SMTP_ENCRYPTION'));
                if ($encryption === 'ssl') {
                    $mailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
                } elseif ($encryption === 'none') {
                    $mailer->SMTPSecure = false;
                    $mailer->SMTPAutoTLS = false;
                } else {
                    $mailer->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                }

                $smtpUsername = getenv('SMTP_USERNAME');
                if ($smtpUsername !== false && $smtpUsername !== '') {
                    $mailer->SMTPAuth = true;
                    $mailer->Username = $smtpUsername;
                    $mailer->Password = (string) getenv('SMTP_PASSWORD');
                } else {
                    $mailer->SMTPAuth = false;
                }

                $mailer->CharSet = 'UTF-8';
                $mailer->setFrom($fromAddress, 'Terse');
                $mailer->addReplyTo($fromAddress, 'Terse Support');
                $mailer->addAddress($email);
                $mailer->Subject = $subject;
                $mailer->Body = $body;
                $mailer->AltBody = $body;
                $mailer->isHTML(false);

                $mailSent = $mailer->send();
            } catch (\PHPMailer\PHPMailer\Exception $mailerException) {
                $deliveryError = $mailerException->getMessage();
            } catch (\Throwable $mailerException) {
                $deliveryError = $mailerException->getMessage();
            }

            if ($deliveryError !== '') {
                error_log('Password reset email error: ' . $deliveryError);
            }
        } else {
            $fallbackSmtpHost = getenv('MAIL_SMTP_HOST');
            if ($fallbackSmtpHost !== false && $fallbackSmtpHost !== '') {
                ini_set('SMTP', $fallbackSmtpHost);
            }

            $fallbackSmtpPort = getenv('MAIL_SMTP_PORT');
            if ($fallbackSmtpPort !== false && $fallbackSmtpPort !== '') {
                ini_set('smtp_port', (string) $fallbackSmtpPort);
            }

            $configuredHost = trim((string) ini_get('SMTP'));
            $configuredPort = trim((string) ini_get('smtp_port'));
            $isWindows = stripos(PHP_OS, 'WIN') === 0;

            $canUseMail = true;
            if ($isWindows) {
                $lowerHost = strtolower($configuredHost);
                if ($lowerHost === '' || $lowerHost === 'localhost' || $lowerHost === '127.0.0.1') {
                    $canUseMail = false;
                }
            }

            if ($canUseMail) {
                $headers = sprintf("From: %s\r\nReply-To: %s", $fromAddress, $fromAddress);
                $mailSent = mail($email, $subject, $body, $headers);
                if (!$mailSent) {
                    $deliveryError = sprintf(
                        'mail() transport failed for %s:%s; install PHPMailer and configure SMTP settings.',
                        $configuredHost !== '' ? $configuredHost : 'unknown-host',
                        $configuredPort !== '' ? $configuredPort : 'unknown-port'
                    );
                    error_log('Password reset email error: ' . $deliveryError);
                }
            } else {
                $deliveryError = 'mail() transport is not configured. Install PHPMailer via Composer and supply SMTP_* environment variables.';
                error_log('Password reset email error: ' . $deliveryError);
            }
        }

        if ($mailSent) {
            $message = 'If an account matches the provided details, a reset message has been sent.';
            $showForm = false;
        } else {
            $error = 'Email delivery failed. Please try again or contact support to provide assistance.';
            if ($deliveryError !== '') {
                error_log('Password reset email delivery failure details: ' . $deliveryError);
            }
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
