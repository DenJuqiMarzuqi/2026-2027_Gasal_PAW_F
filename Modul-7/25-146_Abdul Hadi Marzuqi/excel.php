<?php
require_once __DIR__ . "/koneksi.php";

$mulai   = $_GET['tanggal_mulai']   ?? '';
$selesai = $_GET['tanggal_selesai'] ?? '';


if ($mulai === '' || $selesai === '' || $mulai > $selesai) {
    die('Rentang tanggal tidak valid.');
}

$batas = date('Y-m-d', strtotime($selesai . ' +1 day'));


$sql = "SELECT DATE(waktu_transaksi) AS tgl, SUM(total) AS total_harian
        FROM transaksi
        WHERE waktu_transaksi >= ? AND waktu_transaksi < ?
        GROUP BY DATE(waktu_transaksi)
        ORDER BY tgl ASC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ss", $mulai, $batas);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);

// Query 2: ringkasan
$sql2 = "SELECT COUNT(DISTINCT pelanggan_id) AS jml_pelanggan,
                SUM(total) AS total_pendapatan
         FROM transaksi
         WHERE waktu_transaksi >= ? AND waktu_transaksi < ?";
$stmt2 = mysqli_prepare($conn, $sql2);
mysqli_stmt_bind_param($stmt2, "ss", $mulai, $batas);
mysqli_stmt_execute($stmt2);
$ringkasan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2));


header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=rekap_penjualan_{$mulai}_sd_{$selesai}.xls");
?>
<table border="1">
    <tr>
        <th colspan="3">Rekap Laporan Penjualan <?= $mulai ?> sampai <?= $selesai ?></th>
    </tr>
    <tr>
        <th>No</th>
        <th>Total Pendapatan</th>
        <th>Tanggal Transaksi</th>
    </tr>
    <?php $no = 1;
    while ($r = mysqli_fetch_assoc($hasil)): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= (int)$r['total_harian'] ?></td>
            <td><?= date('d M Y', strtotime($r['tgl'])) ?></td>
        </tr>
    <?php endwhile; ?>
    <tr>
        <td colspan="2"><b>Jumlah Pelanggan Unik</b></td>
        <td><?= (int)$ringkasan['jml_pelanggan'] ?> Orang</td>
    </tr>
    <tr>
        <td colspan="2"><b>Total Pendapatan Keseluruhan</b></td>
        <td><?= (int)$ringkasan['total_pendapatan'] ?></td>
    </tr>
</table>