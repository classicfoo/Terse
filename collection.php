<?php
require_once __DIR__ . '/auth.php';
$db = get_db();
$name = $_GET['name'] ?? '';
$stmt = $db->prepare("SELECT title, slug, created_at FROM posts WHERE collection = ? ORDER BY created_at DESC");
$stmt->execute([$name]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
$blog_title = get_blog_title() . ' - ' . $name;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo htmlspecialchars($blog_title); ?></title>
</head>
<body>
<h1><?php echo htmlspecialchars($name); ?></h1>
<?php foreach ($posts as $post): ?>
<article>
<h2><a href="post.php?collection=<?php echo urlencode($name); ?>&slug=<?php echo urlencode($post['slug']); ?>"><?php echo htmlspecialchars($post['title']); ?></a></h2>
<small><?php echo htmlspecialchars($post['created_at']); ?></small>
</article>
<?php endforeach; ?>
<p><a href="index.php">Back to posts</a></p>
</body>
</html>
