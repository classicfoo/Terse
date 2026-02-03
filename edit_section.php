<?php
require_once __DIR__ . '/auth.php';
require_login();

$db = get_db();
$blog_title = get_blog_title();

$id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT title, parent_id, template, is_public FROM sections WHERE id = ?");
$stmt->execute([$id]);
$section = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$section) {
    header('Location: index.php');
    exit();
}

$title = $section['title'];
$template = $section['template'] ?? '';
$parent_id = (int)$section['parent_id'];
$is_public = (int)($section['is_public'] ?? 1);
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $title = ucwords(strtolower($title));
    $template = $_POST['template'] ?? '';
    $visibility = $_POST['visibility'] ?? ($is_public ? 'public' : 'private');
    $is_public = $visibility === 'public' ? 1 : 0;
    if ($title) {
        $update = $db->prepare("UPDATE sections SET title = ?, template = ?, is_public = ? WHERE id = ?");
        $update->execute([$title, $template, $is_public, $id]);
        header('Location: view_section.php?id=' . $id);
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
<title>Edit Section</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>
<h1><a href="index.php"><?php echo htmlspecialchars($blog_title); ?></a></h1>
<h2>Edit Section</h2>
<?php if ($message): ?><p><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
<form method="post">
    <div class="form-field">
        <label for="title">Title</label>
        <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($title); ?>">
    </div>
    <div class="form-field">
        <label for="template">Template</label>
        <textarea name="template" id="template" rows="8" cols="60"><?php echo htmlspecialchars($template); ?></textarea>
    </div>
    <fieldset>
        <legend>Visibility</legend>
        <label>
            <input type="radio" name="visibility" value="public" <?php echo $is_public ? 'checked' : ''; ?>>
            Public
        </label>
        <label>
            <input type="radio" name="visibility" value="private" <?php echo !$is_public ? 'checked' : ''; ?>>
            Private
        </label>
    </fieldset>
    <button type="submit">Update</button>
</form>
<p><a href="view_section.php?id=<?php echo $id; ?>">Back to <?php echo htmlspecialchars($title); ?></a></p>
</body>
</html>
