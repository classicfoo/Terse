<?php
require_once __DIR__ . '/auth.php';
$db = get_db();
$collection = $_GET['collection'] ?? '';
$slug = $_GET['slug'] ?? '';
$stmt = $db->prepare("SELECT title, content, created_at, collection FROM posts WHERE collection = ? AND slug = ?");
$stmt->execute([$collection, $slug]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$post) {
    http_response_code(404);
    echo 'Post not found';
    exit();
}
$template = __DIR__ . '/templates/' . $collection . '.php';
if (!file_exists($template)) {
    $template = __DIR__ . '/templates/default.php';
}
include $template;
?>
