<?php
session_start();
include('connection.php');

/* ================= FIX 1: SESSION CHECK ================= */
if(!isset($_SESSION['user_id'])){
    header("Location: Login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$cart = $_SESSION['cart'] ?? [];

if(empty($cart)){
    die("Cart empty");
}

/* ================= FORM DATA ================= */
$name = $_POST['name'];
$phone = $_POST['phone'];
$address = $_POST['address'];
$payment = $_POST['payment'];

/* ================= FIX 2: INITIALIZE SAFELY ================= */
$items = "";
$total = 0;

foreach($cart as $id => $qty){

    $id = (int)$id;

    $q = mysqli_query($conn,"SELECT * FROM products WHERE id=$id");

    /* FIX 3: SQL error handling safe */
    if(!$q){
        die("SQL Error: " . mysqli_error($conn));
    }

    $row = mysqli_fetch_assoc($q);

    if(!$row) continue;

    $items .= $row['item_name']." (x$qty), ";
    $total += $row['price'] * $qty;
}

/* ================= FIX 4: SAFE VALUES ================= */
$name = mysqli_real_escape_string($conn, $name);
$phone = mysqli_real_escape_string($conn, $phone);
$address = mysqli_real_escape_string($conn, $address);
$payment = mysqli_real_escape_string($conn, $payment);
$items = mysqli_real_escape_string($conn, $items);

/* ================= FIX 5: INSERT QUERY ================= */
$insert = mysqli_query($conn,"
INSERT INTO orders 
(c_name, items, total, status, order_date, payment_method, user_id, customer_id)
VALUES 
('$name', '$items', '$total', 'Pending', NOW(), '$payment', '$user_id', '$user_id')
");

/* FIX 6: ERROR CHECK */
if(!$insert){
    die("Insert Error: " . mysqli_error($conn));
}

/* ================= CLEAR CART ================= */
unset($_SESSION['cart']);

/* ================= REDIRECT ================= */
header("Location: success.php");
exit();
?>