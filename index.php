<?php
require_once __DIR__ . '/auth.php';
$db = get_db();
$blog_title = get_blog_title();

$sections = $db->query("SELECT id, title FROM sections WHERE parent_id IS NULL ORDER BY title")->fetchAll(PDO::FETCH_ASSOC);
if (is_logged_in()) {
    $postStmt = $db->prepare("SELECT id, title FROM posts WHERE section_id IS NULL ORDER BY created_at DESC");
    $postStmt->execute();
} else {
    $postStmt = $db->prepare("SELECT id, title FROM posts WHERE section_id IS NULL AND is_public = 1 ORDER BY created_at DESC");
    $postStmt->execute();
}
$posts = $postStmt->fetchAll(PDO::FETCH_ASSOC);
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
<h2>Index</h2>
<?php if (is_logged_in()): ?>
<p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> | <a href="new_section.php">New Section</a> | <a href="new_post.php">New Post</a> | <a href="edit_title.php">Edit Title</a> | <a href="edit_user.php">Edit Account</a> | <a href="logout.php">Logout</a></p>
<?php else: ?>
<p><a href="login.php">Login</a></p>
<?php endif; ?>
<?php if (!empty($sections)): ?>
<h3>Sections</h3>
<ul>
<?php foreach ($sections as $section): ?>
    <li><a href="view_section.php?id=<?php echo $section['id']; ?>"><?php echo htmlspecialchars($section['title']); ?></a></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
<?php if (!empty($posts)): ?>
<h3>Posts</h3>
<ul>
<?php foreach ($posts as $post): ?>
    <li><a href="view_post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
</body>
</html>
