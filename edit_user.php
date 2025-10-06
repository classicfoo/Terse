<?php
require_once __DIR__ . '/auth.php';
require_login();

$db = get_db();
$current_username = $_SESSION['username'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username'] ?? '');

    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_username === '') {
        $message = 'Username is required';
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
<title>Edit Account</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>
<h1>Edit Account</h1>
<?php if ($message): ?>
<p><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>
<form method="post">
    <div class="form-field">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($current_username); ?>">
    </div>
    <div class="form-field">
        <label for="new_password">New Password</label>
        <input type="password" name="new_password" id="new_password">
    </div>
    <div class="form-field">
        <label for="confirm_password">Confirm New Password</label>
        <input type="password" name="confirm_password" id="confirm_password">
    </div>
    <button type="submit">Save</button>
</form>
<p><a href="index.php">Back to Index</a></p>

</body>
</html>
