<?php
$koneksi = mysqli_connect("localhost","root","","penjualan");
if (!$koneksi){
    die ("Koneksi Gagal : ".mysqli_connect_error());
}
?>