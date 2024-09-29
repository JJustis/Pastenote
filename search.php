<?php
require_once 'db_connect.php';

$tag = $_GET['tag'] ?? '';

if (empty($tag)) {
    die('No tag provided');
}

$stmt = $pdo->prepare('SELECT p.* FROM pastes p JOIN tags t ON p.id = t.paste_id WHERE t.tag = ? AND (p.expires_at IS NULL OR p.expires_at > NOW()) ORDER BY p.created_at DESC');
$stmt->execute([$tag]);
$pastes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - CodeShare</title>
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
        <h2>Search Results for Tag: <?php echo htmlspecialchars($tag); ?></h2>
        <?php if (empty($pastes)): ?>
            <p>No pastes found with this tag.</p>
        <?php else: ?>
            <ul class="paste-list">
                <?php foreach ($pastes as $paste): ?>
                    <li>
                        <a href="view_paste.php?id=<?php echo $paste['id']; ?>">
                            <?php echo htmlspecialchars($paste['title'] ?: 'Untitled'); ?>
                        </a>
                        <span class="paste-info">
                            Created: <?php echo htmlspecialchars($paste['created_at']); ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </main>
    <footer>
        <p>&copy; 2024 CodeShare - Pastebin Clone</p>
    </footer>
    <script src="script.js"></script>
</body>
</html>