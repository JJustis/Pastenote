<?php
require_once 'db_connect.php';

$id = $_GET['id'] ?? '';

if (empty($id)) {
    die('No paste ID provided');
}

$stmt = $pdo->prepare('SELECT * FROM pastes WHERE id = ?');
$stmt->execute([$id]);
$paste = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paste) {
    die('Paste not found');
}

$stmt = $pdo->prepare('SELECT tag FROM tags WHERE paste_id = ?');
$stmt->execute([$id]);
$tags = $stmt->fetchAll(PDO::FETCH_COLUMN);

$is_unlocked = $paste['current_payment'] >= $paste['payment_goal'];

$paste_url = 'http://betahut.bounceme.net/pastenote/view_paste.php?id=' . $id;
$embed_code = '<iframe src="' . $paste_url . '" width="600" height="400"></iframe>';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($paste['title'] ?: 'Untitled'); ?> - CodeShare</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.5.1/styles/default.min.css">
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
        <h2><?php echo htmlspecialchars($paste['title'] ?: 'Untitled'); ?></h2>
        <p>Paste ID: <?php echo htmlspecialchars($paste['id']); ?></p>
        <p>Created at: <?php echo htmlspecialchars($paste['created_at']); ?></p>
        <?php if ($paste['expires_at']): ?>
            <p>Expires at: <?php echo htmlspecialchars($paste['expires_at']); ?></p>
        <?php endif; ?>
        <div class="tags">
            <?php foreach ($tags as $tag): ?>
                <span class="tag"><?php echo htmlspecialchars($tag); ?></span>
            <?php endforeach; ?>
        </div>

        <?php if ($paste['is_locked'] && !$is_unlocked): ?>
            <div class="payment-info">
                <h3>This paste is locked</h3>
                <p>Payment goal: $<?php echo number_format($paste['payment_goal'], 2); ?></p>
                <p>Current payment: $<?php echo number_format($paste['current_payment'], 2); ?></p>
                <progress value="<?php echo $paste['current_payment']; ?>" max="<?php echo $paste['payment_goal']; ?>"></progress>
                <form action="process_payment.php" method="post">
                    <input type="hidden" name="paste_id" value="<?php echo $paste['id']; ?>">
                    <input type="number" name="amount" step="0.01" min="0.01" placeholder="Enter amount" required>
                    <button type="submit">Contribute</button>
                </form>
            </div>
            <p>The content of this paste is locked. Please contribute to the payment goal to unlock it.</p>
        <?php else: ?>
            <pre><code class="language-<?php echo htmlspecialchars($paste['syntax']); ?>"><?php echo htmlspecialchars($paste['content']); ?></code></pre>
        <?php endif; ?>
        
        <div class="share-section">
            <h3>Share this Paste</h3>
            <input type="text" id="embedCode" value='<?php echo htmlspecialchars($embed_code); ?>' readonly>
            <button onclick="copyEmbedCode()">Copy Embed Code</button>
            <div class="social-buttons">
                <a href="https://twitter.com/share?url=<?php echo urlencode($paste_url); ?>" target="_blank">
                    <i class="fab fa-twitter"></i> Twitter
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($paste_url); ?>" target="_blank">
                    <i class="fab fa-facebook"></i> Facebook
                </a>
                <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo urlencode($paste_url); ?>" target="_blank">
                    <i class="fab fa-linkedin"></i> LinkedIn
                </a>
            </div>
        </div>
    </main>
    <footer>
        <p>&copy; 2024 CodeShare - Pastebin Clone</p>
    </footer>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.5.1/highlight.min.js"></script>
    <script>hljs.highlightAll();</script>
    <script>
        function copyEmbedCode() {
            var embedCodeInput = document.getElementById("embedCode");
            embedCodeInput.select();
            embedCodeInput.setSelectionRange(0, 99999);
            document.execCommand("copy");
            alert("Embed code copied to clipboard");
        }
    </script>
</body>
</html>
