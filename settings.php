<?php
require_once __DIR__ . '/auth.php';
require_login();

$default_visibility = get_default_post_visibility();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected = $_POST['default_visibility'] ?? 'public';
    $is_public = $selected === 'public';
    set_default_post_visibility($is_public);
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
            <input type="radio" name="default_visibility" value="public" <?php echo $default_visibility ? 'checked' : ''; ?>>
            Public
        </label>
        <label>
            <input type="radio" name="default_visibility" value="private" <?php echo !$default_visibility ? 'checked' : ''; ?>>
            Private
        </label>
    </fieldset>
    <button type="submit">Save</button>
</form>
<p><a href="index.php">Back to Index</a></p>
</body>
</html>
