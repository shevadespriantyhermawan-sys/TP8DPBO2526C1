<?php
require_once __DIR__ . '/Template.php';

/**
 * ErrorView (View)
 * Halaman yang tampil jika terjadi error fatal, misalnya MySQL
 * belum menyala atau database belum di-import.
 */
class ErrorView
{
    public static function render($exception)
    {
        if (!headers_sent()) {
            http_response_code(500);
        }
        try {
            $tpl = new Template("error");
            $tpl->replace("DETAIL", Template::e($exception->getMessage()));
            Template::page("Error", "", $tpl->getContent(), null);
        } catch (Throwable $e) {
            // Jika template pun gagal dimuat, tampilkan teks biasa
            echo "Terjadi kesalahan pada aplikasi: " . Template::e($exception->getMessage());
        }
    }
}
