<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo htmlspecialchars($post['title']); ?></title>
<style>
body { font-family: sans-serif; background-color: #f0f8ff; }
</style>
</head>
<body>
<h1><?php echo htmlspecialchars($post['title']); ?></h1>
<p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
<small><?php echo htmlspecialchars($post['created_at']); ?> in <?php echo htmlspecialchars($post['collection']); ?></small>
<p><a href="collection.php?name=<?php echo urlencode($post['collection']); ?>">Back to <?php echo htmlspecialchars($post['collection']); ?></a></p>
</body>
</html>
