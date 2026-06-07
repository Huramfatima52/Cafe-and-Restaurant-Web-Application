<?php
include('connection.php');

$email = "admin@gmail.com";

$new_password = password_hash("admin12345", PASSWORD_DEFAULT);

$query = "UPDATE admins 
          SET password='$new_password' 
          WHERE email='$email'";

if (mysqli_query($conn, $query)) {

    echo "Update successful <br>";
    echo "Affected rows: " . mysqli_affected_rows($conn);

} else {
    echo "Error: " . mysqli_error($conn);
}
?>