<?php
$server = "localhost";
$username = "root";
$pw = "";
$db_name = "penjualan";
$conn = mysqli_connect($server, $username, $pw, $db_name);
if (!$conn){
    die("Koneksi Error".mysqli_connect_error());
}
echo "";
















?>