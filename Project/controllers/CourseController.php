<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/DB.php';
require_once __DIR__ . '/../models/Course.php';
require_once __DIR__ . '/../models/Lecturer.php';
require_once __DIR__ . '/../views/CourseView.php';

/**
 * CourseController (Controller) untuk tabel baru `courses`.
 * Selain Model Course, controller ini memakai Model Lecturer
 * untuk mengisi dropdown dosen pengampu pada form.
 */
class CourseController extends BaseController
{
    private $course;   // model mata kuliah
    private $lecturer; // model dosen (untuk isi dropdown)
    private $view;     // view mata kuliah

    function __construct()
    {
        parent::__construct();
        $this->course = new Course(Config::$db_host, Config::$db_user, Config::$db_pass, Config::$db_name);
        $this->lecturer = new Lecturer(Config::$db_host, Config::$db_user, Config::$db_pass, Config::$db_name);
        $this->view = new CourseView();
    }

    // READ: menampilkan daftar mata kuliah
    public function index()
    {
        $this->course->open();
        try {
            $data = $this->course->getAll();
        } finally {
            $this->course->close();
        }
        $this->view->renderIndex($data, $this->pullFlash());
    }

    // Menampilkan form tambah mata kuliah
    public function create()
    {
        $form = array("id" => 0, "code" => "", "name" => "", "credits" => "", "lecturer_id" => "");
        $this->view->renderForm("create", $form, $this->getLecturerOptions(), array(), $this->pullFlash());
    }

    // CREATE: memproses form tambah mata kuliah
    public function store($post)
    {
        $form = $this->readForm($post);
        $errors = $this->validate($form);

        if (empty($errors)) {
            try {
                $this->course->open();
                $this->course->add($form["code"], $form["name"], (int)$form["credits"], $this->nullableId($form["lecturer_id"]));
            } catch (mysqli_sql_exception $e) {
                $errors[] = $this->dbErrorMessage($e, "Kode mata kuliah sudah digunakan.");
            } finally {
                $this->course->close();
            }
        }

        if (empty($errors)) {
            $this->setFlash("success", "Data mata kuliah berhasil ditambahkan.");
            $this->redirect("course.php");
        }

        $this->view->renderForm("create", $form, $this->getLecturerOptions(), $errors, null);
    }

    // Menampilkan form ubah mata kuliah
    public function edit($id)
    {
        $id = $this->toId($id);
        $row = null;

        if ($id > 0) {
            $this->course->open();
            try {
                $row = $this->course->getById($id);
            } finally {
                $this->course->close();
            }
        }

        if ($row === null) {
            $this->setFlash("danger", "Data mata kuliah tidak ditemukan.");
            $this->redirect("course.php");
        }

        $form = array(
            "id" => $row["id"],
            "code" => $row["code"],
            "name" => $row["name"],
            "credits" => (string)$row["credits"],
            "lecturer_id" => ($row["lecturer_id"] === null) ? "" : (string)$row["lecturer_id"]
        );
        $this->view->renderForm("edit", $form, $this->getLecturerOptions(), array(), $this->pullFlash());
    }

    // UPDATE: memproses form ubah mata kuliah
    public function update($post)
    {
        $form = $this->readForm($post);

        if ($form["id"] <= 0) {
            $this->setFlash("danger", "Data mata kuliah tidak ditemukan.");
            $this->redirect("course.php");
        }

        $errors = $this->validate($form);
        $notFound = false;

        if (empty($errors)) {
            try {
                $this->course->open();
                if ($this->course->getById($form["id"]) === null) {
                    $notFound = true;
                } else {
                    $this->course->update($form["id"], $form["code"], $form["name"], (int)$form["credits"], $this->nullableId($form["lecturer_id"]));
                }
            } catch (mysqli_sql_exception $e) {
                $errors[] = $this->dbErrorMessage($e, "Kode mata kuliah sudah digunakan.");
            } finally {
                $this->course->close();
            }
        }

        if ($notFound) {
            $this->setFlash("danger", "Data mata kuliah tidak ditemukan.");
            $this->redirect("course.php");
        }

        if (empty($errors)) {
            $this->setFlash("success", "Data mata kuliah berhasil diubah.");
            $this->redirect("course.php");
        }

        $this->view->renderForm("edit", $form, $this->getLecturerOptions(), $errors, null);
    }

    // DELETE: menghapus mata kuliah
    public function delete($post)
    {
        $id = $this->toId(isset($post["id"]) ? $post["id"] : "");
        $type = "danger";
        $message = "Data mata kuliah tidak ditemukan.";

        if ($id > 0) {
            try {
                $this->course->open();
                if ($this->course->delete($id) > 0) {
                    $type = "success";
                    $message = "Data mata kuliah berhasil dihapus.";
                }
            } catch (mysqli_sql_exception $e) {
                $message = $this->dbErrorMessage($e, "Data tidak dapat dihapus.");
            } finally {
                $this->course->close();
            }
        }

        $this->setFlash($type, $message);
        $this->redirect("course.php");
    }

    // ---------- fungsi bantu (private) ----------

    // Daftar dosen untuk dropdown pada form
    private function getLecturerOptions()
    {
        $this->lecturer->open();
        try {
            return $this->lecturer->getOptions();
        } finally {
            $this->lecturer->close();
        }
    }

    // Membaca dan merapikan isian form dari $_POST (kode dijadikan huruf besar)
    private function readForm($post)
    {
        return array(
            "id" => $this->toId(isset($post["id"]) ? $post["id"] : ""),
            "code" => strtoupper($this->input($post, "code")),
            "name" => $this->input($post, "name"),
            "credits" => $this->input($post, "credits"),
            "lecturer_id" => $this->input($post, "lecturer_id")
        );
    }

    // Validasi isian form; mengembalikan array pesan error
    private function validate($form)
    {
        $errors = array();

        if (!preg_match('/^[A-Z0-9\-]{3,10}$/', $form["code"])) {
            $errors[] = "Kode mata kuliah harus 3-10 karakter (huruf, angka, atau tanda minus).";
        }

        if ($form["name"] === "") {
            $errors[] = "Nama mata kuliah wajib diisi.";
        } elseif (mb_strlen($form["name"], "UTF-8") > 150) {
            $errors[] = "Nama mata kuliah maksimal 150 karakter.";
        }

        if (!ctype_digit($form["credits"]) || (int)$form["credits"] < 1 || (int)$form["credits"] > 6) {
            $errors[] = "SKS harus berupa angka 1 sampai 6.";
        }

        if ($form["lecturer_id"] !== "" && !ctype_digit($form["lecturer_id"])) {
            $errors[] = "Dosen yang dipilih tidak valid.";
        }

        return $errors;
    }
}
