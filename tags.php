<?php
require_once 'db_connect.php';

$stmt = $pdo->query('SELECT tag, COUNT(*) as count FROM tags GROUP BY tag ORDER BY count DESC, tag ASC');
$tags = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getTagSize($count, $min, $max) {
    $minSize = 1;
    $maxSize = 3;
    if ($min == $max) {
        return $minSize;
    }
    return $minSize + ($count - $min) / ($max - $min) * ($maxSize - $minSize);
}

$minCount = min(array_column($tags, 'count'));
$maxCount = max(array_column($tags, 'count'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tags - CodeShare</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <i class="fas fa-code"></i> CodeShare
            </div>
            <ul>
                <li><a href="index.html">Home</a></li>
                <li><a href="tags.php">Tags</a></li>
                <li><a href="#" id="darkModeToggle"><i class="fas fa-moon"></i></a></li>
            </ul>
        </nav>
    </header>
    <main>
        <h2>Tags</h2>
        <div class="tag-cloud">
            <?php foreach ($tags as $tag): ?>
                <a href="search.php?tag=<?php echo urlencode($tag['tag']); ?>" style="font-size: <?php echo getTagSize($tag['count'], $minCount, $maxCount); ?>em;">
                    <?php echo htmlspecialchars($tag['tag']); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </main>
    <footer>
        <p>&copy; 2024 CodeShare - Pastebin Clone</p>
    </footer>
    <script src="script.js"></script>
</body>
</html>