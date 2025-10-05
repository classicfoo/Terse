<?php
require_once __DIR__ . '/auth.php';
require_login();

$title = get_blog_title();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    if ($title) {
        set_blog_title($title);
        header('Location: index.php');
        exit();
    } else {
        $message = 'Title is required';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Edit Title</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>
<h1>Edit Title</h1>
<?php if ($message): ?>
<p><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>
<form method="post">
<label for="title">Title</label><br>
<input type="text" name="title" id="title" value="<?php echo htmlspecialchars($title); ?>"><br>
<button type="submit">Save</button>
</form>
<p><a href="index.php">Back to Index</a></p>
</body>
</html>
