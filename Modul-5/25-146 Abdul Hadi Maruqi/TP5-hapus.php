    <?php
    include 'TP5-koneksi.php';

    $id = $_GET['id'];
    mysqli_query($koneksi, "DELETE FROM supplier WHERE id='$id'");
    header("Location: TP5-main.php");