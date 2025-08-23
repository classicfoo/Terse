<?php
function get_db() {
    static $db = null;
    if ($db === null) {
        $db = new PDO('sqlite:' . __DIR__ . '/blog.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT UNIQUE NOT NULL, password TEXT NOT NULL)");
        $db->exec("CREATE TABLE IF NOT EXISTS posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            content TEXT NOT NULL,
            collection TEXT NOT NULL DEFAULT 'general',
            slug TEXT NOT NULL DEFAULT '',
            created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
        )");
        // Ensure collection and slug columns exist for older databases
        $columns = $db->query("PRAGMA table_info(posts)")->fetchAll(PDO::FETCH_COLUMN, 1);
        if (!in_array('collection', $columns)) {
            $db->exec("ALTER TABLE posts ADD COLUMN collection TEXT NOT NULL DEFAULT 'general'");
        }
        if (!in_array('slug', $columns)) {
            $db->exec("ALTER TABLE posts ADD COLUMN slug TEXT NOT NULL DEFAULT ''");
        }
        $db->exec("CREATE TABLE IF NOT EXISTS settings (key TEXT PRIMARY KEY, value TEXT NOT NULL)");
        $stmt = $db->prepare("SELECT COUNT(*) AS count FROM settings WHERE key = 'blog_title'");
        $stmt->execute();
        if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] == 0) {
            $insert = $db->prepare("INSERT INTO settings (key, value) VALUES ('blog_title', 'Blog')");
            $insert->execute();
        }
        $stmt = $db->query("SELECT COUNT(*) as count FROM users");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        if ($count == 0) {
            $username = 'admin';
            $password = password_hash('password', PASSWORD_DEFAULT);
            $insert = $db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $insert->execute([$username, $password]);
        }
    }
    return $db;
}

function get_blog_title() {
    $db = get_db();
    $stmt = $db->prepare("SELECT value FROM settings WHERE key = 'blog_title'");
    $stmt->execute();
    $title = $stmt->fetchColumn();
    return $title !== false ? $title : 'Blog';
}

function set_blog_title($title) {
    $db = get_db();
    $stmt = $db->prepare("INSERT OR REPLACE INTO settings (key, value) VALUES ('blog_title', ?)");
    $stmt->execute([$title]);
}
?>
