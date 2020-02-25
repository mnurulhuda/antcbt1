<?php
require "../config/config.database.php";
require "import_word/import.php";

$id_mapel = $_POST['id_mapel'];
$namafile = $_FILES['word_file']['name'];
$tmp = $_FILES['word_file']['tmp_name'];

$folder = 'soal/';

$file = $folder . $namafile;

move_uploaded_file($tmp, $file);

$soals = import($file);

$id_soal=$_POST['id_bank_soal'];
$id_lokal=$_POST['id_lokal'];
$sip = $_SERVER['SERVER_NAME'];

foreach ($soals as $no => $soal) {
    $j = count($soal);
    $opj = array('1'=>'','2'=>'','3'=>'','4'=>'','5'=>'');
    for ($ab=0;$ab<$j;$ab++) {
        if ($ab == 0) {
            $soal_tanya = trim($soal[$ab]);
        } else if ($ab == $j - 1) {
            $kunci = strtoupper(trim($soal[$ab]));
        } else {
            $opj[$ab] = trim($soal[$ab]);
        }
    }

    $exec = mysqli_query($koneksi, "INSERT INTO soal (id_mapel,nomor,soal,pilA,pilB,pilC,pilD,pilE,jawaban,jenis) VALUES ('$id_mapel','$no','$soal_tanya','$opj[1]','$opj[2]','$opj[3]','$opj[4]','$opj[5]','$kunci','1')");
}

header('location:index.php?pg=banksoal&tambah=yes&ac=lihat&id='.$id_soal); 