<?php
if (isset($_GET['paste_id'])) {
    $paste_id = $_GET['paste_id'];
    header("Location: view_paste.php?id=$paste_id");
    exit();
} else {
    echo "Payment successful, but no paste ID provided.";
}
?>
