<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management Brew & Bite</title>
    <link rel="stylesheet" href="style.css">
     <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Open+Sans&display=swap" rel="stylesheet">
  <!-- Font Awesome (for icons like bars, cart, etc.) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <h2>Order Management</h2>
     <!-- Filter+Sort -->
    <div class="controls">
        <select id="status-filter">
            <option value="all">All Orders</option>
            <option value="pending">Pending</option>
            <option value="processing">Processing</option>
            <option value="delivered">Delivered</option>
            <option value="cancelled">Cancelled</option>
        </select>

        <select id="sortOrders">
            <option value="newest">Newest First</option>
            <option value="oldest">Oldest First</option>
            <option value="high">High Price</option>
            <option value="low">Low Price</option>
        </select>
    </div>
     <!-- Table   -->
    <table class="order-table">
        <tr>
            <th>Order ID</th>
            <th>Customer Name</th>
            <th>Items</th>
            <th>Total</th>
            <th>Status</th>
            <th>Update</th>
            <th>Date</th>
        </tr>

        <tr>
            <td>#101</td>
            <td>Ayan</td>
            <td>Iced Latte, Cookies</td>
            <td>3660</td>
            <td class="status">pending</td>
            <td>
                <select class="statusUpdate">
                    <option>Pending</option>
                    <option>Processing</option>
                    <option>Delivered</option>
                    <option>Cancelled</option>
                 </select>
            </td>
            <td>2026-02-15</td>
        </tr>
        <tr>
            <td>#106</td>
            <td>Ahmed</td>
            <td>Margarita, Loaded Fries, FajitaWrap</td>
            <td>3350</td>
            <td class="status">pending</td>
            <td>
               <select class="statusUpdate">
                   <option>Pending</option>
                   <option>Processing</option>
                   <option>Delivered</option>
                   <option>Cancelled</option>
                </select>
            </td>
            <td>2026-02-18</td>
        <tr>
      </table>
</body>
</html>