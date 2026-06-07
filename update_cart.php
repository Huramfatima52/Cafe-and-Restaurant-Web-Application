<?php
session_start();

$id = (int)$_GET['id'];
$qty = (int)$_GET['qty'];

if($qty <= 0){
    unset($_SESSION['cart'][$id]);
} else {
    $_SESSION['cart'][$id] = $qty;
}

header("Location: Cart.php");
exit();
?>