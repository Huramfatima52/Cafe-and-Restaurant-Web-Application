<?php
session_start();
include('connection.php');

$cart = $_SESSION['cart'];

$total = 0;
$items = "";

foreach($cart as $item){
    $total += $item['price'];
    $items .= $item['name'] . ", ";
}

if(isset($_POST['place_order'])){

    $customer_name = $_POST['customer_name'];

    $query = "INSERT INTO orders (customer_name, items, total, status)
              VALUES ('$customer_name', '$items', '$total', 'pending')";

    if(mysqli_query($conn, $query)){
        unset($_SESSION['cart']);

        echo "<script>
        alert('Order Placed Successfully');
        window.location.href='cart.php';
        </script>";
    } else {
        echo mysqli_error($conn);
    }
}
?>

<h2>Checkout</h2>

<form method="POST">

    <input type="text" name="customer_name" placeholder="Your Name" required>

    <p>Items: <?php echo $items; ?></p>
    <p>Total: <?php echo $total; ?></p>

    <button type="submit" name="place_order">Place Order</button>

</form>