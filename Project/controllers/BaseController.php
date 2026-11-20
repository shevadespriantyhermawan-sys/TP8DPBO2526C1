<?php
/**
 * BaseController
 * ------------------------------------------------------------
 * Class induk untuk semua controller. Berisi fungsi bantu yang
 * dipakai bersama: membaca input, flash message (notifikasi satu
 * kali tampil), redirect, validasi tanggal dan penerjemah error
 * database menjadi pesan yang mudah dipahami.
 */
abstract class BaseController
{
    function __construct()
    {
        // Session dipakai untuk menyimpan flash message antar-halaman
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    // Mengambil nilai teks dari array input ($_POST) dan membuang spasi di tepi
    protected function input($data, $key)
    {
        return (isset($data[$key]) && is_string($data[$key])) ? trim($data[$key]) : "";
    }

    // Mengubah nilai menjadi id bilangan bulat > 0, selain itu menghasilkan 0
    protected function toId($value)
    {
        return (is_string($value) && ctype_digit($value)) ? (int)$value : 0;
    }

    // Nilai dropdown opsional: string kosong menjadi null (kolom NULL di database)
    protected function nullableId($value)
    {
        return ($value === "") ? null : (int)$value;
    }

    // Memeriksa format tanggal Y-m-d yang benar-benar valid (mis. 2024-02-30 ditolak)
    protected function isValidDate($value)
    {
        $date = DateTime::createFromFormat("Y-m-d", $value);
        return $date !== false && $date->format("Y-m-d") === $value;
    }

    // Menyimpan notifikasi yang akan ditampilkan pada halaman berikutnya
    protected function setFlash($type, $message)
    {
        $_SESSION["flash"] = array("type" => $type, "message" => $message);
    }

    // Mengambil notifikasi sekaligus menghapusnya (hanya tampil sekali)
    protected function pullFlash()
    {
        $flash = isset($_SESSION["flash"]) ? $_SESSION["flash"] : null;
        unset($_SESSION["flash"]);
        return $flash;
    }

    // Pindah halaman lalu menghentikan eksekusi (pola Post/Redirect/Get)
    protected function redirect($url)
    {
        header("Location: " . $url);
        exit;
    }

    // Menerjemahkan error MySQL ke pesan untuk pengguna
    protected function dbErrorMessage($exception, $duplicateMessage)
    {
        switch ($exception->getCode()) {
            case 1062: // duplicate entry (nilai UNIQUE sudah ada)
                return $duplicateMessage;
            case 1452: // foreign key tidak valid (data induk tidak ada)
                return "Data referensi yang dipilih tidak ditemukan.";
            case 1451: // data masih dipakai tabel lain
                return "Data tidak dapat dihapus karena masih digunakan data lain.";
            default:
                return "Terjadi kesalahan pada database. Silakan coba lagi.";
        }
    }
}
