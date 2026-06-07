<?php
session_start();
include('connection.php');

$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Orders</title>

    <style>
        body{
            font-family: Arial;
            background:#f5f5f5;
        }

        .container{
            width:70%;
            margin:auto;
            background:#fff;
            padding:20px;
            border-radius:10px;
        }

        h2{
            text-align:center;
            color:#FF6B35;
        }

        .order-card{
            border:1px solid #ddd;
            padding:15px;
            margin-bottom:10px;
            border-radius:8px;
        }

        .status{
            padding:5px 10px;
            border-radius:5px;
            color:white;
            display:inline-block;
        }

        .pending{ background:orange; }
        .completed{ background:green; }
        .cancelled{ background:red; }

        /* BUTTONS */
        .btn{
            display:inline-block;
            padding:8px 14px;
            text-decoration:none;
            border-radius:6px;
            font-size:13px;
            margin-top:8px;
            font-weight:600;
        }

        .reorder-btn{
            background:#FF6B35;
            color:white;
        }

        .reorder-btn:hover{
            background:#e65c2e;
        }

        .back-btn{
            display:inline-block;
            margin-top:20px;
            padding:10px 18px;
            background:#FF6B35;
            color:white;
            text-decoration:none;
            border-radius:8px;
            font-weight:600;
            transition:0.3s;
        }

        .back-btn:hover{
            background:#e65c2e;
            transform:translateY(-2px);
        }

        .btn-container{
            text-align:center;
        }

    </style>
</head>

<body>

<div class="container">

    <h2>My Orders</h2>

    <?php
    // $q = mysqli_query($conn,"SELECT * FROM orders ORDER BY o_id DESC");


$q = mysqli_query($conn,
"SELECT * FROM orders WHERE user_id='$user_id' ORDER BY o_id DESC");
    if(mysqli_num_rows($q) == 0){
        echo "<p style='text-align:center;'>No orders yet</p>";
    }

    while($row = mysqli_fetch_assoc($q)){
    ?>

    <div class="order-card">

        <h3>Order #<?php echo $row['o_id']; ?></h3>

        <p><b>Customer:</b> <?php echo $row['c_name']; ?></p>

        <p><b>Items:</b> <?php echo $row['items']; ?></p>

        <p><b>Total:</b> Rs <?php echo $row['total']; ?></p>

        <p>
            <b>Status:</b>
            <span class="status <?php echo strtolower($row['status']); ?>">
                <?php echo $row['status']; ?>
            </span>
        </p>

        <p><small><?php echo $row['order_date']; ?></small></p>

        <!--  REORDER BUTTON (INSIDE LOOP) -->
        <a href="reorder.php?id=<?php echo $row['o_id']; ?>" 
           class="btn reorder-btn">
             Reorder
        </a>

    </div>

    <?php } ?>

    <!-- BACK BUTTON -->
    <div class="btn-container">
        <a href="Menu.php" class="back-btn">← Back to Menu</a>
    </div>

</div>

</body>
</html>