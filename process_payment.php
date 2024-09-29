<?php
session_start();
require_once 'paypal_config.php';
require_once 'db_connect.php';

$paste_id = $_POST['paste_id'] ?? '';
$item_amount = floatval($_POST['amount'] ?? 0);

if (empty($paste_id) || $item_amount <= 0) {
    die('Invalid input');
}

$stmt = $pdo->prepare('SELECT paypal_email FROM pastes WHERE id = ?');
$stmt->execute([$paste_id]);
$paste = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paste) {
    die('Paste not found');
}

$paypal_email = $paste['paypal_email'];

// Create PayPal payment URL
$query_string = http_build_query(array(
    'business' => $paypal_email,
    'cmd' => '_xclick',
    'item_name' => 'Unlock Paste',
    'item_number' => $paste_id,
    'amount' => $item_amount,
    'currency_code' => PAYPAL_CURRENCY,
    'return' => PAYPAL_RETURN_URL . '?paste_id=' . $paste_id,
    'cancel_return' => PAYPAL_CANCEL_URL,
    'notify_url' => PAYPAL_NOTIFY_URL,
));

$paypal_url = PAYPAL_URL . '?' . $query_string;

// Redirect to PayPal
header('Location: ' . $paypal_url);
exit();
?>
