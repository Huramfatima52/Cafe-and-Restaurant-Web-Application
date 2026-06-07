<?php
session_start();

$id = (int)$_GET['id'];

if(isset($_SESSION['cart'][$id])){
    unset($_SESSION['cart'][$id]);
}

header("Location: Cart.php");
exit();
?>