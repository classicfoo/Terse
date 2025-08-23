<?php
require_once __DIR__ . '/auth.php';
$db = get_db();

$posts = $db->query("SELECT id, title, content, created_at FROM posts ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Blog</title>
</head>
<body>
<h1>Blog</h1>
<?php if (is_logged_in()): ?>
<p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> | <a href="new_post.php">New Post</a> | <a href="logout.php">Logout</a></p>
<?php else: ?>
<p><a href="login.php">Login</a></p>
<?php endif; ?>
<?php foreach ($posts as $post): ?>
<article>
<h2><?php echo htmlspecialchars($post['title']); ?></h2>
<p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
<small><?php echo htmlspecialchars($post['created_at']); ?></small>
</article>
<hr>
<?php endforeach; ?>
</body>
</html>
