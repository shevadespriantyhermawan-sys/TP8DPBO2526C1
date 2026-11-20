<?php
/**
 * config.php
 * ------------------------------------------------------------
 * Konfigurasi koneksi database untuk seluruh aplikasi.
 * Sebelumnya konfigurasi ini ada di connection.php (variabel
 * biasa). Sekarang dibungkus dalam class Config agar nilainya
 * bisa dipakai di mana saja dengan Config::$db_host, dst.
 * Sesuaikan nilainya jika setting MySQL/XAMPP kamu berbeda.
 */
class Config
{
    static public $db_host = "localhost"; // alamat server MySQL
    static public $db_user = "root";      // username MySQL (default XAMPP)
    static public $db_pass = "";          // password MySQL (default XAMPP: kosong)
    static public $db_name = "tp_mvc25";  // nama database hasil import tp_mvc25.sql
}
