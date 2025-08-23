<?php
require_once __DIR__ . '/auth.php';
require_login();

$id = intval($_GET['id'] ?? 0);
if ($id) {
    $db = get_db();
    $stmt = $db->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: index.php');
exit();
?>
