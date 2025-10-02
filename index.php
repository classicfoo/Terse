<?php
require_once __DIR__ . '/auth.php';
$db = get_db();
$blog_title = get_blog_title();

$sections = $db->query("SELECT id, title FROM sections WHERE parent_id IS NULL ORDER BY title")->fetchAll(PDO::FETCH_ASSOC);
if (is_logged_in()) {
    $postsStmt = $db->query("SELECT id, title, is_public FROM posts WHERE section_id IS NULL ORDER BY created_at DESC");
} else {
    $postsStmt = $db->prepare("SELECT id, title, is_public FROM posts WHERE section_id IS NULL AND is_public = 1 ORDER BY created_at DESC");
    $postsStmt->execute();
}
$posts = $postsStmt->fetchAll(PDO::FETCH_ASSOC);
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
<ul>
<?php foreach ($sections as $section): ?>
    <li><a href="view_section.php?id=<?php echo $section['id']; ?>"><?php echo htmlspecialchars($section['title']); ?></a></li>
<?php endforeach; ?>
</ul>
<ul>
<?php foreach ($posts as $post): ?>
    <li>
        <a href="view_post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a>
        <?php if (is_logged_in() && !$post['is_public']): ?>
            <em>(Private)</em>
        <?php endif; ?>
    </li>
<?php endforeach; ?>
</ul>
</body>
</html>
