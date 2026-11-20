<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/DB.php';
require_once __DIR__ . '/../models/Lecturer.php';
require_once __DIR__ . '/../models/Department.php';
require_once __DIR__ . '/../views/LecturerView.php';

/**
 * LecturerController (Controller) - penghubung Model dan View
 * untuk data dosen. Menerima request dari index.php, memvalidasi
 * input, memanggil Model, lalu menyerahkan hasilnya ke View.
 */
class LecturerController extends BaseController
{
    private $lecturer;   // model dosen
    private $department; // model departemen (untuk isi dropdown)
    private $view;       // view dosen

    function __construct()
    {
        parent::__construct();
        $this->lecturer = new Lecturer(Config::$db_host, Config::$db_user, Config::$db_pass, Config::$db_name);
        $this->department = new Department(Config::$db_host, Config::$db_user, Config::$db_pass, Config::$db_name);
        $this->view = new LecturerView();
    }

    // READ: menampilkan daftar dosen
    public function index()
    {
        $this->lecturer->open();
        try {
            $data = $this->lecturer->getAll();
        } finally {
            $this->lecturer->close();
        }
        $this->view->renderIndex($data, $this->pullFlash());
    }

    // Menampilkan form tambah dosen (form kosong)
    public function create()
    {
        $this->view->renderForm("create", $this->emptyForm(), $this->getDepartmentOptions(), array(), $this->pullFlash());
    }

    // CREATE: memproses form tambah dosen
    public function store($post)
    {
        $form = $this->readForm($post);
        $errors = $this->validate($form);

        if (empty($errors)) {
            try {
                $this->lecturer->open();
                $this->lecturer->add($form["name"], $form["nidn"], $form["phone"], $form["join_date"], $this->nullableId($form["department_id"]));
            } catch (mysqli_sql_exception $e) {
                $errors[] = $this->dbErrorMessage($e, "NIDN sudah terdaftar pada dosen lain.");
            } finally {
                $this->lecturer->close();
            }
        }

        if (empty($errors)) {
            $this->setFlash("success", "Data dosen berhasil ditambahkan.");
            $this->redirect("index.php");
        }

        // Ada error: tampilkan kembali form beserta isian sebelumnya
        $this->view->renderForm("create", $form, $this->getDepartmentOptions(), $errors, null);
    }

    // Menampilkan form ubah dosen (terisi data lama)
    public function edit($id)
    {
        $id = $this->toId($id);
        $row = null;

        if ($id > 0) {
            $this->lecturer->open();
            try {
                $row = $this->lecturer->getById($id);
            } finally {
                $this->lecturer->close();
            }
        }

        if ($row === null) {
            $this->setFlash("danger", "Data dosen tidak ditemukan.");
            $this->redirect("index.php");
        }

        $form = array(
            "id" => $row["id"],
            "name" => $row["name"],
            "nidn" => $row["nidn"],
            "phone" => $row["phone"],
            "join_date" => $row["join_date"],
            "department_id" => ($row["department_id"] === null) ? "" : (string)$row["department_id"]
        );
        $this->view->renderForm("edit", $form, $this->getDepartmentOptions(), array(), $this->pullFlash());
    }

    // UPDATE: memproses form ubah dosen
    public function update($post)
    {
        $form = $this->readForm($post);

        if ($form["id"] <= 0) {
            $this->setFlash("danger", "Data dosen tidak ditemukan.");
            $this->redirect("index.php");
        }

        $errors = $this->validate($form);
        $notFound = false;

        if (empty($errors)) {
            try {
                $this->lecturer->open();
                if ($this->lecturer->getById($form["id"]) === null) {
                    $notFound = true;
                } else {
                    $this->lecturer->update($form["id"], $form["name"], $form["nidn"], $form["phone"], $form["join_date"], $this->nullableId($form["department_id"]));
                }
            } catch (mysqli_sql_exception $e) {
                $errors[] = $this->dbErrorMessage($e, "NIDN sudah terdaftar pada dosen lain.");
            } finally {
                $this->lecturer->close();
            }
        }

        if ($notFound) {
            $this->setFlash("danger", "Data dosen tidak ditemukan.");
            $this->redirect("index.php");
        }

        if (empty($errors)) {
            $this->setFlash("success", "Data dosen berhasil diubah.");
            $this->redirect("index.php");
        }

        $this->view->renderForm("edit", $form, $this->getDepartmentOptions(), $errors, null);
    }

    // DELETE: menghapus dosen (dipanggil lewat form POST)
    public function delete($post)
    {
        $id = $this->toId(isset($post["id"]) ? $post["id"] : "");
        $type = "danger";
        $message = "Data dosen tidak ditemukan.";

        if ($id > 0) {
            try {
                $this->lecturer->open();
                if ($this->lecturer->delete($id) > 0) {
                    $type = "success";
                    $message = "Data dosen berhasil dihapus.";
                }
            } catch (mysqli_sql_exception $e) {
                $message = $this->dbErrorMessage($e, "Data tidak dapat dihapus.");
            } finally {
                $this->lecturer->close();
            }
        }

        $this->setFlash($type, $message);
        $this->redirect("index.php");
    }

    // ---------- fungsi bantu (private) ----------

    // Daftar departemen untuk dropdown pada form
    private function getDepartmentOptions()
    {
        $this->department->open();
        try {
            return $this->department->getOptions();
        } finally {
            $this->department->close();
        }
    }

    // Nilai awal form tambah
    private function emptyForm()
    {
        return array("id" => 0, "name" => "", "nidn" => "", "phone" => "", "join_date" => "", "department_id" => "");
    }

    // Membaca dan merapikan isian form dari $_POST
    private function readForm($post)
    {
        return array(
            "id" => $this->toId(isset($post["id"]) ? $post["id"] : ""),
            "name" => $this->input($post, "name"),
            "nidn" => $this->input($post, "nidn"),
            "phone" => $this->input($post, "phone"),
            "join_date" => $this->input($post, "join_date"),
            "department_id" => $this->input($post, "department_id")
        );
    }

    // Validasi isian form; mengembalikan array pesan error (kosong jika valid)
    private function validate($form)
    {
        $errors = array();

        if ($form["name"] === "") {
            $errors[] = "Nama wajib diisi.";
        } elseif (mb_strlen($form["name"], "UTF-8") > 100) {
            $errors[] = "Nama maksimal 100 karakter.";
        }

        if (!preg_match('/^[0-9]{10}$/', $form["nidn"])) {
            $errors[] = "NIDN harus berupa 10 digit angka.";
        }

        if (!preg_match('/^(?=.{8,20}$)\+?[0-9 \-]+$/', $form["phone"])) {
            $errors[] = "Nomor telepon tidak valid (8-20 karakter, hanya angka, spasi, tanda minus, dan boleh diawali +).";
        }

        if (!$this->isValidDate($form["join_date"])) {
            $errors[] = "Tanggal bergabung tidak valid.";
        }

        if ($form["department_id"] !== "" && !ctype_digit($form["department_id"])) {
            $errors[] = "Departemen yang dipilih tidak valid.";
        }

        return $errors;
    }
}
