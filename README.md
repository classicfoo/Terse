# Terse

Minimal blogging, maximum clarity.

## Usage

This repository contains a simple blogging engine written in PHP with SQLite storage.

1. Run the built-in PHP server:
   ```sh
   php -S localhost:8000
   ```
2. Visit `http://localhost:8000/index.php` in your browser.
3. Log in with the default credentials:
   - **Username:** `admin`
   - **Password:** `password`
4. Create new posts from the "New Post" link.

The database file `blog.db` will be created automatically in the project root.

## Collections

Posts can now be organized into named collections. When creating or editing a post,
specify a **Collection** and **Slug**. Each collection has its own listing page, and
individual posts are rendered using a template matching the collection name from the
`templates/` directory (falling back to `templates/default.php`). Example URLs:

- `collection.php?name=personal_blog`
- `post.php?collection=recipes&slug=chicken_soup`

Adding a file like `templates/personal_blog.php` allows subtle style differences for
that collection.
