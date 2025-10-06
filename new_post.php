<?php
require_once __DIR__ . '/auth.php';
require_login();

$db = get_db();
$title = '';
$content = '';
$message = '';
$section_id = isset($_GET['section_id']) ? intval($_GET['section_id']) : intval($_POST['section_id'] ?? 0);
$is_public = isset($_POST['is_public']) ? (int)($_POST['is_public'] === '1') : 1;

$section = null;
if ($section_id) {
    $stmt = $db->prepare("SELECT title, template FROM sections WHERE id = ?");
    $stmt->execute([$section_id]);
    $section = $stmt->fetch(PDO::FETCH_ASSOC);
}

if ($section && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $content = $section['template'] ?? '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $title = ucwords(strtolower($title));
    $content = trim($_POST['content'] ?? '');
    if ($title && $content) {
        $is_public = isset($_POST['is_public']) && $_POST['is_public'] === '0' ? 0 : 1;
        $stmt = $db->prepare("INSERT INTO posts (title, content, section_id, is_public) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $content, $section_id ?: null, $is_public]);
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
<link rel="stylesheet" href="styles.css">
</head>
<body>
<h1>New Post</h1>
<?php if ($message): ?>
<p><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>
<form method="post">
    <div class="form-field">
        <label for="title">Title</label>
        <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($title); ?>">
    </div>
    <div class="form-field">
        <label for="content">Content (Markdown supported)</label>
        <textarea name="content" id="content" class="editor-field" rows="10"><?php echo htmlspecialchars($content); ?></textarea>
    </div>
    <input type="hidden" name="section_id" value="<?php echo htmlspecialchars($section_id); ?>">
    <fieldset>
        <legend>Visibility</legend>
        <label><input type="radio" name="is_public" value="1" <?php echo $is_public ? 'checked' : ''; ?>> Public</label>
        <label><input type="radio" name="is_public" value="0" <?php echo !$is_public ? 'checked' : ''; ?>> Private</label>
    </fieldset>
    <button type="submit">Publish</button>
</form>
<?php if ($section): ?>
<p><a href="view_section.php?id=<?php echo $section_id; ?>">Back to <?php echo htmlspecialchars($section['title']); ?></a></p>
<?php else: ?>
<p><a href="index.php">Back to index</a></p>
<?php endif; ?>
</body>
</html>
