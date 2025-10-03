<?php
require_once __DIR__ . '/auth.php';
require_login();

$db = get_db();

$stmt = $db->prepare('SELECT username, email FROM users WHERE id = ?');
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    logout();
    header('Location: login.php');
    exit();
}

$current_username = $user['username'];
$current_email = $user['email'] ?? '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username'] ?? '');
    $new_email_input = trim($_POST['email'] ?? '');
    $new_email = $new_email_input === '' ? null : $new_email_input;

    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_username === '') {
        $message = 'Username is required';
    } elseif ($new_email_input !== '' && !filter_var($new_email_input, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address';
    } elseif ($new_password !== '' && $new_password !== $confirm_password) {
        $message = 'New passwords do not match';
    } else {
        $updates = [];
        $params = [];
        if ($new_username !== $current_username) {
            $check = $db->prepare('SELECT COUNT(*) FROM users WHERE username = ? AND id != ?');
            $check->execute([$new_username, $_SESSION['user_id']]);
            if ($check->fetchColumn() > 0) {
                $message = 'Username already taken';
            } else {
                $updates[] = 'username = ?';
                $params[] = $new_username;
            }
        }
        if ($new_email !== ($user['email'] ?? null)) {
            if ($new_email === null) {
                $updates[] = 'email = NULL';
            } else {
                $check = $db->prepare('SELECT COUNT(*) FROM users WHERE email = ? AND id != ?');
                $check->execute([$new_email, $_SESSION['user_id']]);
                if ($check->fetchColumn() > 0) {
                    $message = 'Email address already in use';
                } else {
                    $updates[] = 'email = ?';
                    $params[] = $new_email;
                }
            }
        }
        if ($new_password !== '') {
            $updates[] = 'password = ?';
            $params[] = password_hash($new_password, PASSWORD_DEFAULT);
        }
        if (!$message) {
            if (count($updates) > 0) {
                $params[] = $_SESSION['user_id'];
                $sql = 'UPDATE users SET ' . implode(', ', $updates) . ' WHERE id = ?';
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $_SESSION['username'] = $new_username;
                $current_username = $new_username;
                $current_email = $new_email_input;
                header('Location: index.php');
                exit();
            } else {
                $message = 'No changes made';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset='utf-8'>
<meta name='viewport' content='width=device-width, initial-scale=1'>
<title>Account Settings</title>
</head>
<body>
<h1>Account Settings</h1>
<?php if ($message): ?>
<p><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>
<form method='post'>
<label for='username'>Username</label><br>
<input type='text' name='username' id='username' value='<?php echo htmlspecialchars($current_username); ?>'><br>
<label for='email'>Email</label><br>
<input type='email' name='email' id='email' value='<?php echo htmlspecialchars($current_email); ?>'><br>
<label for='new_password'>New Password</label><br>
<input type='password' name='new_password' id='new_password'><br>
<label for='confirm_password'>Confirm New Password</label><br>
<input type='password' name='confirm_password' id='confirm_password'><br>
<button type='submit'>Save</button>
</form>
<p><a href='index.php'>Back to Index</a></p>

</body>
</html>
