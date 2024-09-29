<?php
// PayPal configuration
define('PAYPAL_ID', 'Vikerus1@gmail.com'); // Business Email
$mode = 'test'; // 'test' or 'live'
define('PAYPAL_RETURN_URL', 'http://betahut.bounceme.net/pastenote/success.php');
define('PAYPAL_CANCEL_URL', 'http://betahut.bounceme.net/pastenote/cancel.php');
define('PAYPAL_NOTIFY_URL', 'http://betahut.bounceme.net/pastenote/ipn.php');
define('PAYPAL_CURRENCY', 'USD');

// PayPal URL based on mode
if ($mode == 'live') {
    define('PAYPAL_URL', 'https://www.paypal.com/cgi-bin/webscr');
} else {
    define('PAYPAL_URL', 'https://www.sandbox.paypal.com/cgi-bin/webscr');
}
?>
