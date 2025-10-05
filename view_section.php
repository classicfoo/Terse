<?php
require_once __DIR__ . '/auth.php';
$db = get_db();
$blog_title = get_blog_title();

$id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT id, title, parent_id FROM sections WHERE id = ?");
$stmt->execute([$id]);
$section = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$section) {
    http_response_code(404);
    echo "<p>Section not found.</p>\n";
    exit();
}

$parent = null;
if ($section['parent_id']) {
    $parentStmt = $db->prepare("SELECT id, title FROM sections WHERE id = ?");
    $parentStmt->execute([$section['parent_id']]);
    $parent = $parentStmt->fetch(PDO::FETCH_ASSOC);
}

$subStmt = $db->prepare("SELECT id, title FROM sections WHERE parent_id = ? ORDER BY title");
$subStmt->execute([$id]);
$subsections = $subStmt->fetchAll(PDO::FETCH_ASSOC);

$postStmt = $db->prepare(
    is_logged_in()
        ? "SELECT id, title FROM posts WHERE section_id = ? ORDER BY created_at DESC"
        : "SELECT id, title FROM posts WHERE section_id = ? AND is_public = 1 ORDER BY created_at DESC"
);
$postStmt->execute([$id]);
$posts = $postStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo htmlspecialchars($section['title']); ?> - <?php echo htmlspecialchars($blog_title); ?></title>
</head>
<body>
<h1><a href="index.php"><?php echo htmlspecialchars($blog_title); ?></a></h1>
<h2><?php echo htmlspecialchars($section['title']); ?></h2>
<?php if (is_logged_in()): ?>
<p><a href="new_section.php?parent_id=<?php echo $section['id']; ?>">New Subsection</a> | <a href="new_post.php?section_id=<?php echo $section['id']; ?>">New Post</a> | <a href="edit_section.php?id=<?php echo $section['id']; ?>">Edit Section</a> | <a href="delete_section.php?id=<?php echo $section['id']; ?>" onclick="return confirm('Delete this section?');">Delete Section</a></p>
<?php endif; ?>
<?php if (!empty($subsections)): ?>
<h3>Sections</h3>
<ul>
<?php foreach ($subsections as $sub): ?>
    <li><a href="view_section.php?id=<?php echo $sub['id']; ?>"><?php echo htmlspecialchars($sub['title']); ?></a></li>
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
<?php if ($parent): ?>
<p><a href="view_section.php?id=<?php echo $parent['id']; ?>">Back to <?php echo htmlspecialchars($parent['title']); ?></a></p>
<?php else: ?>
<p><a href="index.php">Back to index</a></p>
<?php endif; ?>
</body>
</html>
