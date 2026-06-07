<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add products Brew & Bite</title>
    <link rel="stylesheet" href="style.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Open+Sans&display=swap" rel="stylesheet">
    <!-- Font Awesome (for icons like bars, cart, etc.) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Side bar -->
        <div class="sidebar">
            <h2>Brew & Bite</h2>
            <ul>
                <li>
                   <a href="admin_dashboard.php">
                     <i class="fa fa-chart-line"></i> Dashboard
                     </a>
                </li>

                <li>
                    <a href="admin_menu.php">
                     <i class="fa fa-box"></i> Menu
                     </a>
                </li>

                <li>
                   <a href="order_management.php">
                     <i class="fa fa-shopping-cart"></i> Orders
                    </a>
                </li>

                <li>
                   <a href="category_management.php">
                     <i class="fa fa-list"></i> Categories
                    </a>
                </li>

                <li>
                   <a href="customer_info.php">
                     <i class="fa fa-user"></i> Customers
                    </a>
                </li>

                <li>
                   <a href="admin_analytics.php">
                     <i class="fa fa-chart-bar"></i> Analytics
                   </a>
                </li>

                <!-- <li>
                   <a href="feedback_review.php">
                     <i class="fa fa-star"></i> Reviews
                    </a>
                </li> -->

                <li>
                    <a href="admin-profile.php">
                     <i class="fa fa-user"></i> Profiles
                    </a>
                </li>
            </ul>
        </div>
        <br><br>

<table border="1" cellpadding="10" width="100%">

<tr>
    <th>ID</th>
    <th>Image</th>
    <th>Name</th>
    <th>Price</th>
    <th>Category</th>
    <th>Stock</th>
    <th>Status</th>
</tr>

<?php
$result = mysqli_query($conn, "SELECT *FROM product");

while($row = mysqli_fetch_assoc($result)){
?>

<tr>
    <td><?php echo $row['id']; ?></td>
    <td><?php echo $row['image']; ?></td>
    <td><?php echo $row['name']; ?></td>
    <td><?php echo $row['price']; ?></td>
    <td><?php echo $row['category']; ?></td>
    <td><?php echo $row['stock']; ?></td>
    <td><?php echo $row['status']; ?></td>
</tr>

<?php } ?>

</table>
        <!-- Main content -->
        <!-- <div class="main-content"> -->
            <!-- Topbar -->
            <!-- <div class="topbar"> -->
                <!-- <h2>Menu Management</h2>
            </div>
            <table class="product-table">

               <tr>
                 <th>Image</th>
                 <th>Name</th>
                 <th>Price</th>
                 <th>Category</th>
                 <th>Stock</th>
                 <th>Status</th>
               </tr>

               <tr>
                 <td><img src="images/orangejasminetea.jpg" class="admin-img"></td>
                 <td>Orange Jasmine Tea</td>
                 <td>Rs 499</td>
                 <td>Drinks</td>
                 <td>20</td>
                 <td class="in-stock">Available</td>
                </tr>

                <tr>
                 <td><img src="images/Ramennoodles.jpg" class="admin-img"></td>
                 <td>Ramen Noodles</td>
                 <td>Rs 1200</td>
                 <td>Chinese Cusine</td>
                 <td>10</td>
                 <td class="in-stock">Available</td>
                </tr>

                <tr>
                 <td><img src="images/chickenchowmein.jpg" class="admin-img"></td>
                 <td>Chicken Chow Mein</td>
                 <td>Rs 850</td>
                 <td>Chinese Cusine</td>
                 <td>0</td>
                 <td class="out-stock">Out of Stock</td>
                </tr>

</table> -->
</body>
</html>