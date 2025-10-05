<?php
require_once __DIR__ . '/auth.php';
require_login();

$db = get_db();
$blog_title = get_blog_title();

$parent_id = isset($_GET['parent_id']) ? intval($_GET['parent_id']) : intval($_POST['parent_id'] ?? 0);
$parent = null;
if ($parent_id) {
    $stmt = $db->prepare("SELECT id, title FROM sections WHERE id = ?");
    $stmt->execute([$parent_id]);
    $parent = $stmt->fetch(PDO::FETCH_ASSOC);
}
$title = '';
$template = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $title = ucwords(strtolower($title));
    $template = $_POST['template'] ?? '';
    if ($title) {
        $stmt = $db->prepare("INSERT INTO sections (title, parent_id, template) VALUES (?, ?, ?)");
        $stmt->execute([$title, $parent_id ?: null, $template]);
        if ($parent_id) {
            header('Location: view_section.php?id=' . $parent_id);
        } else {
            header('Location: index.php');
        }
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
<title>New Section</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>
<h1><a href="index.php"><?php echo htmlspecialchars($blog_title); ?></a></h1>
<h2>New Section</h2>
<?php if ($message): ?><p><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
<form method="post">
<label for="title">Title</label><br>
<input type="text" name="title" id="title" value="<?php echo htmlspecialchars($title); ?>"><br>
<label for="template">Template</label><br>
<textarea name="template" id="template" rows="8" cols="60"><?php echo htmlspecialchars($template); ?></textarea><br>
<input type="hidden" name="parent_id" value="<?php echo htmlspecialchars($parent_id); ?>">
<button type="submit">Create</button>
</form>
<?php if ($parent): ?>
<p><a href="view_section.php?id=<?php echo $parent['id']; ?>">Back to <?php echo htmlspecialchars($parent['title']); ?></a></p>
<?php else: ?>
<p><a href="index.php">Back to index</a></p>
<?php endif; ?>
</body>
</html>
