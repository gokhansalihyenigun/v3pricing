<?php
$servername = "localhost";
$username = "priceusr";
$password = "Aa384we326sasa*-";
$dbname = "pricedb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set to utf8
$conn->set_charset("utf8");
?>