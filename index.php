<?php
require_once __DIR__ . '/auth.php';
$db = get_db();
$blog_title = get_blog_title();

$posts = $db->query("SELECT id, title, content, created_at FROM posts ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo htmlspecialchars($blog_title); ?></title>
</head>
<body>
<h1><?php echo htmlspecialchars($blog_title); ?></h1>
<?php if (is_logged_in()): ?>
<p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> | <a href="new_post.php">New Post</a> | <a href="edit_title.php">Edit Title</a> | <a href="logout.php">Logout</a></p>
<?php else: ?>
<p><a href="login.php">Login</a></p>
<?php endif; ?>
<?php foreach ($posts as $post): ?>
<article>
<h2><?php echo htmlspecialchars($post['title']); ?></h2>
<p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
<?php $utc = gmdate('c', strtotime($post['created_at'])); ?>
<small>
    <time datetime="<?php echo $utc; ?>"><?php echo $utc; ?></time>
    <?php if (is_logged_in()): ?> | <a href="edit_post.php?id=<?php echo $post['id']; ?>">Edit</a> | <a href="delete_post.php?id=<?php echo $post['id']; ?>" onclick="return confirm('Delete this post?');">Delete</a><?php endif; ?>
</small>
</article>
<hr>
<?php endforeach; ?>
</body>
<script>
document.querySelectorAll('time[datetime]').forEach(el => {
    const utcValue = el.getAttribute('datetime');
    const date = new Date(utcValue);
    if (!isNaN(date)) {
        el.textContent = date.toLocaleString();
    }
});
</script>
</html>
