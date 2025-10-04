<?php
require_once __DIR__ . '/auth.php';
require_login();

$db = get_db();
$blog_title = get_blog_title();

$id = intval($_GET['id'] ?? 0);
$stmt = $db->prepare("SELECT title, parent_id, template FROM sections WHERE id = ?");
$stmt->execute([$id]);
$section = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$section) {
    header('Location: index.php');
    exit();
}

$title = $section['title'];
$template = $section['template'] ?? '';
$parent_id = (int)$section['parent_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $title = ucwords(strtolower($title));
    $template = $_POST['template'] ?? '';
    if ($title) {
        $update = $db->prepare("UPDATE sections SET title = ?, template = ? WHERE id = ?");
        $update->execute([$title, $template, $id]);
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
</head>
<body>
<h1><a href="index.php"><?php echo htmlspecialchars($blog_title); ?></a></h1>
<h2>Edit Section</h2>
<?php if ($message): ?><p><?php echo htmlspecialchars($message); ?></p><?php endif; ?>
<form method="post">
<label for="title">Title</label><br>
<input type="text" name="title" id="title" value="<?php echo htmlspecialchars($title); ?>"><br>
<label for="template">Template</label><br>
<textarea name="template" id="template" rows="8" cols="60"><?php echo htmlspecialchars($template); ?></textarea><br>
<button type="submit">Update</button>
</form>
<p><a href="view_section.php?id=<?php echo $id; ?>">Back to <?php echo htmlspecialchars($title); ?></a></p>
</body>
</html>
