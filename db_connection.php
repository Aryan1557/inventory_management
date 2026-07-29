<?php
// db_connection.php

// Database connection
$conn = mysqli_connect("localhost", "root", "ebiztech99", "inventory_management");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>