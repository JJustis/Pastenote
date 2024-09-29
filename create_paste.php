<?php
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $syntax = $_POST['syntax'] ?? 'plaintext';
    $expiration = $_POST['expiration'] ?? 'never';
    $tags = $_POST['tags'] ?? '';
    $is_locked = isset($_POST['is_locked']) ? 1 : 0;
    $payment_goal = floatval($_POST['payment_goal'] ?? 0);
    $paypal_email = $_POST['paypal_email'] ?? '';

    if (empty($content)) {
        http_response_code(400);
        echo json_encode(['error' => 'Content is required']);
        exit;
    }

    $id = bin2hex(random_bytes(4));
    $created_at = date('Y-m-d H:i:s');

    switch ($expiration) {
        case '10m':
            $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            break;
        case '1h':
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            break;
        case '1d':
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 day'));
            break;
        case '1w':
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 week'));
            break;
        case '1m':
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 month'));
            break;
        default:
            $expires_at = null;
    }

    $is_locked = ($payment_goal > 0) ? 1 : 0;

    $stmt = $pdo->prepare('INSERT INTO pastes (id, title, content, syntax, created_at, expires_at, is_locked, payment_goal, paypal_email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$id, $title, $content, $syntax, $created_at, $expires_at, $is_locked, $payment_goal, $paypal_email]);

    $tags_array = array_map('trim', explode(',', $tags));
    foreach ($tags_array as $tag) {
        if (!empty($tag)) {
            $stmt = $pdo->prepare('INSERT INTO tags (paste_id, tag) VALUES (?, ?)');
            $stmt->execute([$id, $tag]);
        }
    }

    // Check if the payment goal is reached to unlock the paste
    if ($payment_goal > 0) {
        $stmt = $pdo->prepare('UPDATE pastes SET is_locked = 0 WHERE id = ? AND current_payment >= ?');
        $stmt->execute([$id, $payment_goal]);
    }

    echo json_encode(['id' => $id]);
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
?>
