<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/DB.php';
require_once __DIR__ . '/../models/Department.php';
require_once __DIR__ . '/../views/DepartmentView.php';

/**
 * DepartmentController (Controller) untuk tabel baru `departments`.
 * Alurnya sama dengan LecturerController: terima request ->
 * validasi -> panggil Model -> serahkan hasil ke View.
 */
class DepartmentController extends BaseController
{
    private $department; // model departemen
    private $view;       // view departemen

    function __construct()
    {
        parent::__construct();
        $this->department = new Department(Config::$db_host, Config::$db_user, Config::$db_pass, Config::$db_name);
        $this->view = new DepartmentView();
    }

    // READ: menampilkan daftar departemen
    public function index()
    {
        $this->department->open();
        try {
            $data = $this->department->getAll();
        } finally {
            $this->department->close();
        }
        $this->view->renderIndex($data, $this->pullFlash());
    }

    // Menampilkan form tambah departemen
    public function create()
    {
        $this->view->renderForm("create", array("id" => 0, "name" => "", "faculty" => ""), array(), $this->pullFlash());
    }

    // CREATE: memproses form tambah departemen
    public function store($post)
    {
        $form = $this->readForm($post);
        $errors = $this->validate($form);

        if (empty($errors)) {
            try {
                $this->department->open();
                $this->department->add($form["name"], $form["faculty"]);
            } catch (mysqli_sql_exception $e) {
                $errors[] = $this->dbErrorMessage($e, "Nama departemen sudah ada.");
            } finally {
                $this->department->close();
            }
        }

        if (empty($errors)) {
            $this->setFlash("success", "Data departemen berhasil ditambahkan.");
            $this->redirect("department.php");
        }

        $this->view->renderForm("create", $form, $errors, null);
    }

    // Menampilkan form ubah departemen
    public function edit($id)
    {
        $id = $this->toId($id);
        $row = null;

        if ($id > 0) {
            $this->department->open();
            try {
                $row = $this->department->getById($id);
            } finally {
                $this->department->close();
            }
        }

        if ($row === null) {
            $this->setFlash("danger", "Data departemen tidak ditemukan.");
            $this->redirect("department.php");
        }

        $form = array("id" => $row["id"], "name" => $row["name"], "faculty" => $row["faculty"]);
        $this->view->renderForm("edit", $form, array(), $this->pullFlash());
    }

    // UPDATE: memproses form ubah departemen
    public function update($post)
    {
        $form = $this->readForm($post);

        if ($form["id"] <= 0) {
            $this->setFlash("danger", "Data departemen tidak ditemukan.");
            $this->redirect("department.php");
        }

        $errors = $this->validate($form);
        $notFound = false;

        if (empty($errors)) {
            try {
                $this->department->open();
                if ($this->department->getById($form["id"]) === null) {
                    $notFound = true;
                } else {
                    $this->department->update($form["id"], $form["name"], $form["faculty"]);
                }
            } catch (mysqli_sql_exception $e) {
                $errors[] = $this->dbErrorMessage($e, "Nama departemen sudah ada.");
            } finally {
                $this->department->close();
            }
        }

        if ($notFound) {
            $this->setFlash("danger", "Data departemen tidak ditemukan.");
            $this->redirect("department.php");
        }

        if (empty($errors)) {
            $this->setFlash("success", "Data departemen berhasil diubah.");
            $this->redirect("department.php");
        }

        $this->view->renderForm("edit", $form, $errors, null);
    }

    // DELETE: menghapus departemen (dosen di dalamnya menjadi tanpa departemen)
    public function delete($post)
    {
        $id = $this->toId(isset($post["id"]) ? $post["id"] : "");
        $type = "danger";
        $message = "Data departemen tidak ditemukan.";

        if ($id > 0) {
            try {
                $this->department->open();
                if ($this->department->delete($id) > 0) {
                    $type = "success";
                    $message = "Data departemen berhasil dihapus. Dosen pada departemen ini menjadi tanpa departemen.";
                }
            } catch (mysqli_sql_exception $e) {
                $message = $this->dbErrorMessage($e, "Data tidak dapat dihapus.");
            } finally {
                $this->department->close();
            }
        }

        $this->setFlash($type, $message);
        $this->redirect("department.php");
    }

    // ---------- fungsi bantu (private) ----------

    // Membaca dan merapikan isian form dari $_POST
    private function readForm($post)
    {
        return array(
            "id" => $this->toId(isset($post["id"]) ? $post["id"] : ""),
            "name" => $this->input($post, "name"),
            "faculty" => $this->input($post, "faculty")
        );
    }

    // Validasi isian form; mengembalikan array pesan error
    private function validate($form)
    {
        $errors = array();

        if ($form["name"] === "") {
            $errors[] = "Nama departemen wajib diisi.";
        } elseif (mb_strlen($form["name"], "UTF-8") > 100) {
            $errors[] = "Nama departemen maksimal 100 karakter.";
        }

        if ($form["faculty"] === "") {
            $errors[] = "Fakultas wajib diisi.";
        } elseif (mb_strlen($form["faculty"], "UTF-8") > 100) {
            $errors[] = "Fakultas maksimal 100 karakter.";
        }

        return $errors;
    }
}
