<?php
require_once __DIR__ . '/auth.php';
require_login();

$id = intval($_GET['id'] ?? 0);
if ($id) {
    $db = get_db();

    $stmt = $db->prepare("SELECT parent_id FROM sections WHERE id = ?");
    $stmt->execute([$id]);
    $parent_id = (int)$stmt->fetchColumn();

    $delete_section = function($db, $id) use (&$delete_section) {
        $childStmt = $db->prepare("SELECT id FROM sections WHERE parent_id = ?");
        $childStmt->execute([$id]);
        $children = $childStmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($children as $child) {
            $delete_section($db, $child);
        }
        $delPosts = $db->prepare("DELETE FROM posts WHERE section_id = ?");
        $delPosts->execute([$id]);
        $delSection = $db->prepare("DELETE FROM sections WHERE id = ?");
        $delSection->execute([$id]);
    };

    $delete_section($db, $id);

    if ($parent_id) {
        header('Location: view_section.php?id=' . $parent_id);
    } else {
        header('Location: index.php');
    }
    exit();
}

header('Location: index.php');
exit();
?>
