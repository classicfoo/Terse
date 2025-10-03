<?php
function get_db() {
    static $db = null;
    if ($db === null) {
        $db = new PDO('sqlite:' . __DIR__ . '/blog.db');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->exec("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT UNIQUE NOT NULL, password TEXT NOT NULL, email TEXT UNIQUE)");
        $db->exec("CREATE TABLE IF NOT EXISTS posts (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL, content TEXT NOT NULL, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, section_id INTEGER)");
        $db->exec("CREATE TABLE IF NOT EXISTS sections (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL, parent_id INTEGER REFERENCES sections(id))");
        $db->exec("CREATE TABLE IF NOT EXISTS settings (key TEXT PRIMARY KEY, value TEXT NOT NULL)");

        $db->exec("CREATE TABLE IF NOT EXISTS password_resets (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE, token TEXT NOT NULL UNIQUE, expires_at INTEGER NOT NULL)");

        // Ensure the email column exists for older installations
        $userColumns = $db->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_COLUMN, 1);
        if (!in_array('email', $userColumns)) {
            $db->exec("ALTER TABLE users ADD COLUMN email TEXT");
        }
        $db->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_users_email ON users(email) WHERE email IS NOT NULL");

        // Ensure the section_id column exists for older installations
        $columns = $db->query("PRAGMA table_info(posts)")->fetchAll(PDO::FETCH_COLUMN, 1);
        if (!in_array('section_id', $columns)) {
            $db->exec("ALTER TABLE posts ADD COLUMN section_id INTEGER");
        }
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
