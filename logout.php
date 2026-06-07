<?php
session_start();

// session destroy (logout)
session_unset();
session_destroy();

header("Location: Login.php");
exit();
?>