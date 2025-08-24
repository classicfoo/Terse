<?php
require_once __DIR__ . '/auth.php';
require_login();

$id = intval($_GET['id'] ?? 0);
$section_id = 0;
if ($id) {
    $db = get_db();
    $stmt = $db->prepare("SELECT section_id FROM posts WHERE id = ?");
    $stmt->execute([$id]);
    $section_id = (int)$stmt->fetchColumn();
    $stmt = $db->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$id]);
}

if ($section_id) {
    header('Location: view_section.php?id=' . $section_id);
} else {
    header('Location: index.php');
}
exit();
?>
