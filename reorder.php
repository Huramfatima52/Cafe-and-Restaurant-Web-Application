<?php
session_start();
include('connection.php');

if(!isset($_GET['id'])){
    die("Invalid request");
}

$order_id = (int)$_GET['id'];

$q = mysqli_query($conn,"SELECT * FROM orders WHERE o_id=$order_id");
$order = mysqli_fetch_assoc($q);

if(!$order){
    die("Order not found");
}

/* CART RESET */
$_SESSION['cart'] = [];

/*  EXTRACT ITEMS FROM TEXT */
$items = $order['items']; 
$itemsArray = explode(",", $items);

foreach($itemsArray as $item){

    // example: 
    preg_match('/(.*)\(x(\d+)\)/', trim($item), $matches);

    if(isset($matches[1]) && isset($matches[2])){

        $name = trim($matches[1]);
        $qty = (int)$matches[2];

        // find product by name
        $q2 = mysqli_query($conn,"SELECT * FROM products WHERE item_name='$name' LIMIT 1");
        $row = mysqli_fetch_assoc($q2);

        if($row){
            $id = $row['id'];

            $_SESSION['cart'][$id] = $qty;
        }
    }
}

/* SUCCESS */
echo "<script>
alert('Order added again to cart!');
window.location='Cart.php';
</script>";
?>