<?php
require_once __DIR__ . '/auth.php';
require_login();

$default_post_visibility = get_default_post_visibility();
$default_section_visibility = get_default_section_visibility();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_visibility = $_POST['default_post_visibility'] ?? 'public';
    $section_visibility = $_POST['default_section_visibility'] ?? 'public';
    set_default_post_visibility($post_visibility === 'public');
    set_default_section_visibility($section_visibility === 'public');
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Settings</title>
<link rel="stylesheet" href="styles.css">
</head>
<body>
<h1>Settings</h1>
<form method="post">
    <fieldset>
        <legend>Default Post Visibility</legend>
        <label>
            <input type="radio" name="default_post_visibility" value="public" <?php echo $default_post_visibility ? 'checked' : ''; ?>>
            Public
        </label>
        <label>
            <input type="radio" name="default_post_visibility" value="private" <?php echo !$default_post_visibility ? 'checked' : ''; ?>>
            Private
        </label>
    </fieldset>
    <fieldset>
        <legend>Default Section Visibility</legend>
        <label>
            <input type="radio" name="default_section_visibility" value="public" <?php echo $default_section_visibility ? 'checked' : ''; ?>>
            Public
        </label>
        <label>
            <input type="radio" name="default_section_visibility" value="private" <?php echo !$default_section_visibility ? 'checked' : ''; ?>>
            Private
        </label>
    </fieldset>
    <button type="submit">Save</button>
</form>
<p><a href="index.php">Back to Index</a></p>
</body>
</html>
