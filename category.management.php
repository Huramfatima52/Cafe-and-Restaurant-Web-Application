<?php
include('connection.php');
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Category Management Brew & Bite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Open+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">

        <!-- Sidebar -->
        <div class="sidebar">
            <h2>Brew & Bite</h2>
            <ul>
                <li>Dashboard</li>
                <li>Products</li>
                <li>Orders</li>
                <li>Categories</li>
                <li>Customers</li>
                <li>Analytics</li>
                <li>Reviews</li>
                <li>Profiles</li>
            </ul>
        </div>

        <!-- Main content -->
        <div class="main-content">

            <!-- Topbar -->
            <div class="topbar">
                <h2>Category Management</h2>
            </div>

            <!-- Table -->
            <table class="Menu-table">
                <tr>
                    <th>Category Name</th>
                    <th>Item Name</th>
                    <th>Price</th>
                    <th>Status</th>
                </tr>

                <?php
                $result = mysqli_query($conn, "SELECT * FROM categories ORDER BY id ASC");

                if(mysqli_num_rows($result) > 0){
                    while($row = mysqli_fetch_assoc($result)){

                        $statusClass = ($row['status'] == 'available') ? 'in-stock' : 'out-stock';
                        $statusLabel = ($row['status'] == 'available') ? 'In Stock' : 'Out of Stock';
                ?>

                <tr>
                    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                    <td>Rs <?php echo htmlspecialchars($row['price']); ?></td>
                    <td class="<?php echo $statusClass; ?>"><?php echo $statusLabel; ?></td>
                </tr>

                <?php
                    }
                } else {
                ?>
                <tr>
                    <td colspan="4" style="text-align:center; padding: 20px;">No categories found.</td>
                </tr>
                <?php } ?>

            </table>

        </div>
    </div>
</body>
</html>