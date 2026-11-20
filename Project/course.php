<?php
/**
 * course.php - titik masuk (front controller) untuk data mata kuliah (Courses).
 * Tugasnya hanya membaca request (GET / POST) lalu memanggil
 * method yang sesuai pada CourseController. Tidak ada query SQL maupun HTML di sini.
 *
 * Rute:
 *   GET  ?action=create        -> form tambah
 *   GET  ?action=edit&id=N     -> form ubah
 *   GET  (lainnya)             -> daftar data
 *   POST action=store          -> simpan data baru
 *   POST action=update         -> simpan perubahan
 *   POST action=delete         -> hapus data
 */
require_once __DIR__ . '/controllers/CourseController.php';
require_once __DIR__ . '/views/ErrorView.php';

try {
    $controller = new CourseController();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Request POST: operasi yang mengubah data
        $action = isset($_POST['action']) ? $_POST['action'] : '';

        if ($action === 'store') {
            $controller->store($_POST);
        } elseif ($action === 'update') {
            $controller->update($_POST);
        } elseif ($action === 'delete') {
            $controller->delete($_POST);
        } else {
            $controller->index();
        }
    } else {
        // Request GET: menampilkan halaman
        $action = isset($_GET['action']) ? $_GET['action'] : '';

        if ($action === 'create') {
            $controller->create();
        } elseif ($action === 'edit') {
            $controller->edit(isset($_GET['id']) ? $_GET['id'] : '');
        } else {
            $controller->index();
        }
    }
} catch (Throwable $e) {
    // Error tak terduga (mis. database belum menyala) ditampilkan dengan ramah
    ErrorView::render($e);
}
