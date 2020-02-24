<?php

require "../config/config.default.php";
require "../config/config.function.php";

// $exec = mysqli_query($koneksi, "TRUNCATE berita");
$beritaQ = mysqli_query($koneksi, "SELECT * FROM ujian");
$sesiq = mysqli_query($koneksi, "SELECT * FROM ujian group by sesi ");

while ($berita = mysqli_fetch_array($beritaQ)) {

    // while ($sesi = mysqli_fetch_array($sesiq)) {
    $ruangq = mysqli_query($koneksi, "SELECT * FROM ruang");
    $ruang = mysqli_fetch_array($ruangq);
    // while ($ruang = mysqli_fetch_array($ruangq)) {
    // $exec = mysqli_query($koneksi, "INSERT INTO berita (id_mapel,sesi,ruang,jenis)VALUES('$berita[id_mapel]','$sesi[sesi]','$ruang[kode_ruang]','$berita[kode_ujian]')");
    // print_r($ruang);
    // }
    // print_r($sesi);
    // }
    $tu = date_format(date_create($berita[tgl_ujian]), "H:i");
    
    $ts = date_format(date_create($berita[tgl_selesai]), "H:i");

    $tl = date_format(date_create($berita[tgl_ujian]), "Y-m-d");


    $exec = mysqli_query($koneksi, "INSERT INTO berita (id_mapel,sesi,ruang,jenis,mulai,selesai,tgl_ujian)VALUES('$berita[id_mapel]','$berita[sesi]','$ruang[kode_ruang]','$berita[kode_ujian]', '$tu', '$ts', '$tl')");
}