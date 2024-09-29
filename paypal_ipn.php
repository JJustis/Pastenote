<?php
require_once 'db_connect.php';

// Read POST data from PayPal
$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();
foreach ($raw_post_array as $keyval) {
    $keyval = explode('=', $keyval);
    if (count($keyval) == 2) {
        $myPost[$keyval[0]] = urldecode($keyval[1]);
    }
}

// Verify IPN with PayPal
$req = 'cmd=_notify-validate';
foreach ($myPost as $key => $value) {
    $value = urlencode($value);
    $req .= "&$key=$value";
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://www.paypal.com/cgi-bin/webscr');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
$response = curl_exec($ch);
curl_close($ch);

if (strcmp($response, 'VERIFIED') == 0) {
    // IPN is verified
    $paste_id = $_POST['item_number'];
    $payment_amount = floatval($_POST['mc_gross']);
    
    $stmt = $pdo->prepare('SELECT payment_goal, current_payment FROM pastes WHERE id = ? FOR UPDATE');
    $stmt->execute([$paste_id]);
    $paste = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$paste) {
        die('Paste not found');
    }

    // Update the current payment amount
    $new_current_payment = $paste['current_payment'] + $payment_amount;
    if ($new_current_payment > $paste['payment_goal']) {
        $new_current_payment = $paste['payment_goal'];
    }

    $stmt = $pdo->prepare('UPDATE pastes SET current_payment = ? WHERE id = ?');
    $stmt->execute([$new_current_payment, $paste_id]);

    // If payment goal is met, unlock the paste
    if ($new_current_payment >= $paste['payment_goal']) {
        $stmt = $pdo->prepare('UPDATE pastes SET is_locked = 0 WHERE id = ?');
        $stmt->execute([$paste_id]);
    }
} else {
    // IPN is not verified
    die('Invalid IPN');
}
?>
