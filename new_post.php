<?php
require_once __DIR__ . '/auth.php';
require_login();

$db = get_db();
$title = '';
$content = '';
$message = '';
$section_id = isset($_GET['section_id']) ? intval($_GET['section_id']) : intval($_POST['section_id'] ?? 0);

$section = null;
if ($section_id) {
    $stmt = $db->prepare("SELECT title FROM sections WHERE id = ?");
    $stmt->execute([$section_id]);
    $section = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $title = ucwords(strtolower($title));
    $content = trim($_POST['content'] ?? '');
    if ($title && $content) {
        $stmt = $db->prepare("INSERT INTO posts (title, content, section_id) VALUES (?, ?, ?)");
        $stmt->execute([$title, $content, $section_id ?: null]);
        if ($section_id) {
            header('Location: view_section.php?id=' . $section_id);
        } else {
            header('Location: index.php');
        }
        exit();
    } else {
        $message = 'Title and content are required';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>New Post</title>
</head>
<body>
<h1>New Post</h1>
<?php if ($message): ?>
<p><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>
<form method="post">
<label for="title">Title</label><br>
<input type="text" name="title" id="title" value="<?php echo htmlspecialchars($title); ?>"><br>
<label for="content">Content (Markdown supported)</label><br>
<textarea name="content" id="content" rows="10" cols="50"><?php echo htmlspecialchars($content); ?></textarea><br>
<input type="hidden" name="section_id" value="<?php echo htmlspecialchars($section_id); ?>">
<button type="submit">Publish</button>
</form>
<?php if ($section): ?>
<p><a href="view_section.php?id=<?php echo $section_id; ?>">Back to <?php echo htmlspecialchars($section['title']); ?></a></p>
<?php else: ?>
<p><a href="index.php">Back to index</a></p>
<?php endif; ?>
</body>
</html>
