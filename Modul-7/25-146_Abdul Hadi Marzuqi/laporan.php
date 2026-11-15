<?php
require_once __DIR__ . "/koneksi.php";
$mulai   = $_GET['tanggal_mulai']   ?? '';
$selesai = $_GET['tanggal_selesai'] ?? '';

$sudah_filter = ($mulai !== '' && $selesai !== '');
$error = '';
$rekap = [];
$ringkasan = ['jml_pelanggan' => 0, 'total_pendapatan' => 0];

if ($sudah_filter) {
    if ($mulai > $selesai) {
        $error = 'Tanggal mulai tidak boleh lebih besar dari tanggal selesai.';
    } else {
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
        while ($r = mysqli_fetch_assoc($hasil)) {
            $rekap[] = $r;
        }

        $sql2 = "SELECT COUNT(DISTINCT pelanggan_id) AS jml_pelanggan,
                        SUM(total) AS total_pendapatan
                 FROM transaksi
                 WHERE waktu_transaksi >= ? AND waktu_transaksi < ?";
        $stmt2 = mysqli_prepare($conn, $sql2);
        mysqli_stmt_bind_param($stmt2, "ss", $mulai, $batas);
        mysqli_stmt_execute($stmt2);
        $ringkasan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2));
    }
}


$labels = array_column($rekap, 'tgl');
$nilai  = array_map('intval', array_column($rekap, 'total_harian'));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan penjualan</title>
    <style>
        * {
            font-family: Arial, Helvetica, sans-serif;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-grup {
            display: flex;
            flex-direction: column;
            width: 250px;
            gap: 5px;
            font-weight: bold;
        }

        input {
            padding: 8px;
        }

        .tampilkan {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border-color: black;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: bold;
            width: 200px;
        }

        .tampilkan:hover {
            background-color: #0056b3;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #d9eaf3;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            th {
                background-color: #d9eaf3 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
        .aksi{
            margin: 10px 5px;
        }
        .btn-cetak{
            margin: 10px;
        }
    </style>
</head>

<body>
    <div class="no-print">
        <form method="get">
            <div class="form-grup">
                <label>Input Tanggal Mulai :</label>
                <input type="date" name="tanggal_mulai" value="<?= htmlspecialchars($_GET['tanggal_mulai'] ?? '') ?>" required>
            </div>
            <div class="form-grup">
                <label>Input Tanggal Selesai :</label>
                <input type="date" name="tanggal_selesai" value="<?= htmlspecialchars($_GET['tanggal_selesai'] ?? '') ?>" required required>
            </div>
            <button type="submit" name="tampilkan" class="tampilkan">Tampilkan</button>
        </form>
        <?php if ($sudah_filter && !$error && !empty($rekap)): ?>
            <?php if (!empty($rekap)): ?>
                <div class="aksi">
                    <button type="button" class="btn-cetak" onclick="window.print()">Cetak</button>
                    <a class="btn-excel"
                        href="excel.php?<?= http_build_query([
                                                    'tanggal_mulai'   => $mulai,
                                                    'tanggal_selesai' => $selesai
                                                ]) ?>">Excel</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>

    <?php elseif ($sudah_filter): ?>

        <?php if (empty($rekap)): ?>
            <p>Tidak ada transaksi pada rentang tanggal tersebut.</p>

        <?php else: ?>
            <h2>Grafik Penjualan</h2>
            <div style="width:100%; max-width:800px; height:320px;">
                <canvas id="myChart"></canvas>
            </div>

            <script>
                new Chart(document.getElementById('myChart'), {
                    type: 'bar',
                    data: {
                        labels: <?= json_encode($labels) ?>,
                        datasets: [{
                            label: 'Total Penjualan',
                            data: <?= json_encode($nilai) ?>,
                            backgroundColor: '#007bff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Tanggal Transaksi'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Total Pendapatan (Rp)'
                                }
                            }
                        }
                    }
                });
            </script>
            <h2>Rekapitulasi</h2>
            <table>
                <tr>
                    <th>No</th>
                    <th>Total Pendapatan</th>
                    <th>Tanggal Transaksi</th>
                </tr>
                <?php $no = 1;
                foreach ($rekap as $row): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td>Rp. <?= number_format($row['total_harian'], 0, ',', '.') ?></td>
                        <td><?= date('d M Y', strtotime($row['tgl'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <div class="ringkasan">
                <div class="kartu">
                    <div class="judul">Jumlah Pelanggan</div>
                    <div class="isi"><?= (int)$ringkasan['jml_pelanggan'] ?> Orang</div>
                </div>
                <div class="kartu">
                    <div class="judul">Jumlah Pendapatan</div>
                    <div class="isi">Rp. <?= number_format($ringkasan['total_pendapatan'] ?? 0, 0, ',', '.') ?></div>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>

</body>

</html>