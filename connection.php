<?php

$conn = mysqli_connect("localhost","root","","brewbite");

if(!$conn){
    die("Connection Failed: " . mysqli_connect_error());
}

?>