<?php
require_once __DIR__.'/TP6-koneksi.php';

$servername = "localhost";
$username = "root";
$pass = "";
$database_name = "penjualan";

$conn = mysqli_connect($servername, $username, $pass, $database_name);
if (!$conn){
    die("connected Failed : ".mysqli_connect_error());
}
echo " ";

function e($teks){
    return htmlspecialchars((string)$teks);
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Table</title>
</head>
<body>
    <table border = "1" cellpadding="10" cellspacing="1">
        
        <tr>
            <th>Nama</th>
            <th>NIM</th>
            <th>Action</th>
        </tr>
        <tr>
            <td>Ahmad</td>
            <td>250411100146</td>
            <td><button><a href="TP6-transaksi_detail.php?id=<?= $r['id'] ?>">Detail</a></td></button>
        </tr>
    </table>
</body>
</html>



