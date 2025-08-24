<?php
require_once __DIR__ . '/auth.php';
$db = get_db();
$blog_title = get_blog_title();

$sections = $db->query("SELECT id, title FROM sections WHERE parent_id IS NULL ORDER BY title")->fetchAll(PDO::FETCH_ASSOC);
$posts = $db->query("SELECT id, title FROM posts WHERE section_id IS NULL ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
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
<p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> | <a href="new_section.php">New Section</a> | <a href="edit_title.php">Edit Title</a> | <a href="edit_user.php">Edit Account</a> | <a href="logout.php">Logout</a></p>
<?php else: ?>
<p><a href="login.php">Login</a></p>
<?php endif; ?>
<ul>
<?php foreach ($sections as $section): ?>
    <li><a href="view_section.php?id=<?php echo $section['id']; ?>"><?php echo htmlspecialchars($section['title']); ?></a><?php if (is_logged_in()): ?> - <a href="edit_section.php?id=<?php echo $section['id']; ?>">Edit</a> | <a href="delete_section.php?id=<?php echo $section['id']; ?>" onclick="return confirm('Delete this section?');">Delete</a><?php endif; ?></li>
<?php endforeach; ?>
</ul>
<ul>
<?php foreach ($posts as $post): ?>
    <li><a href="view_post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></li>
<?php endforeach; ?>
</ul>
</body>
</html>
