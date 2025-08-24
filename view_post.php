<?php
require_once __DIR__ . '/auth.php';
$db = get_db();
$blog_title = get_blog_title();
$id = (int)($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT id, title, content, created_at, section_id FROM posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$post) {
    http_response_code(404);
    echo "<p>Post not found.</p>\n";
    exit();
}

$section = null;
if ($post['section_id']) {
    $secStmt = $db->prepare("SELECT id, title FROM sections WHERE id = ?");
    $secStmt->execute([$post['section_id']]);
    $section = $secStmt->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo htmlspecialchars($post['title']); ?> - <?php echo htmlspecialchars($blog_title); ?></title>
</head>
<body>
<h1><a href="index.php"><?php echo htmlspecialchars($blog_title); ?></a></h1>
<article>
<h2><?php echo htmlspecialchars($post['title']); ?></h2>
<p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
<?php
    $iso = gmdate('c', strtotime($post['created_at']));
    $display = gmdate('j M Y g:i a', strtotime($post['created_at']));
?>
<small>
    <time datetime="<?php echo $iso; ?>"><?php echo $display; ?></time>
    <?php if (is_logged_in()): ?> | <a href="edit_post.php?id=<?php echo $post['id']; ?>">Edit</a> | <a href="delete_post.php?id=<?php echo $post['id']; ?>" onclick="return confirm('Delete this post?');">Delete</a><?php endif; ?>
</small>
</article>
<?php if ($section): ?>
<p><a href="view_section.php?id=<?php echo $section['id']; ?>">Back to <?php echo htmlspecialchars($section['title']); ?></a></p>
<?php else: ?>
<p><a href="index.php">Back to posts</a></p>
<?php endif; ?>
<script>
document.querySelectorAll('time[datetime]').forEach(el => {
    const isoValue = el.getAttribute('datetime');
    const date = new Date(isoValue);
    if (!isNaN(date)) {
        const options = {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        };
        el.textContent = date
            .toLocaleString('en-GB', options)
            .replace(',', '');
    }
});
</script>
</body>
</html>
