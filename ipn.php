<?php
require_once 'paypal_config.php';
require_once 'db_connect.php';

// Read POST data
$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);
$myPost = array();
foreach ($raw_post_array as $keyval) {
    $keyval = explode('=', $keyval);
    if (count($keyval) == 2)
        $myPost[$keyval[0]] = urldecode($keyval[1]);
}

// Read the post from PayPal system and add 'cmd'
$req = 'cmd=_notify-validate';
foreach ($myPost as $key => $value) {
    $value = urlencode($value);
    $req .= "&$key=$value";
}

// Post IPN data back to PayPal to validate
$ch = curl_init(PAYPAL_URL);
if ($ch == FALSE) {
    return FALSE;
}
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
curl_setopt($ch, CURLOPT_SSLVERSION, 6);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close', 'User-Agent: your-company-name'));
$res = curl_exec($ch);
if (strcmp($res, "VERIFIED") == 0) {
    // The IPN is verified, process it
    $txn_id = $_POST['txn_id'];
    $payment_gross = $_POST['mc_gross'];
    $item_number = $_POST['item_number']; // paste_id
    $payment_status = $_POST['payment_status'];

    if ($payment_status == 'Completed') {
        // Check if payment is already processed
        $stmt = $pdo->prepare("SELECT count(*) FROM payments WHERE txnid = :txnid");
        $stmt->bindParam(':txnid', $txn_id, PDO::PARAM_STR);
        $stmt->execute();
        $pay_count = $stmt->fetchColumn();

        if ($pay_count == 0) {
            // Insert payment data into the database
            $stmt = $pdo->prepare("INSERT INTO payments (txnid, payment_amount, payment_status, item_number) VALUES (:txnid, :payment_amount, :payment_status, :item_number)");
            $stmt->bindParam(':txnid', $txn_id, PDO::PARAM_STR);
            $stmt->bindParam(':payment_amount', $payment_gross, PDO::PARAM_STR);
            $stmt->bindParam(':payment_status', $payment_status, PDO::PARAM_STR);
            $stmt->bindParam(':item_number', $item_number, PDO::PARAM_STR);
            $stmt->execute();

            // Update the current payment for the paste
            $stmt = $pdo->prepare('UPDATE pastes SET current_payment = current_payment + :payment_amount WHERE id = :paste_id');
            $stmt->bindParam(':payment_amount', $payment_gross, PDO::PARAM_STR);
            $stmt->bindParam(':paste_id', $item_number, PDO::PARAM_STR);
            $stmt->execute();
        }
    }
} else {
    // IPN validation failed
    error_log("IPN validation failed");
}

curl_close($ch);
?>
