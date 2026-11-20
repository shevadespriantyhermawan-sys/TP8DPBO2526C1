<?php
/**
 * Template (bagian View)
 * ------------------------------------------------------------
 * Mesin template sederhana: membaca file HTML di folder
 * templates/ lalu mengganti penanda {{NAMA}} dengan data.
 * Penggantian memakai strtr() sekali jalan, sehingga teks dari
 * pengguna yang kebetulan berisi "{{...}}" tidak ikut diganti.
 * Class ini juga menyediakan helper kecil untuk membangun HTML
 * yang dipakai bersama oleh semua View.
 */
class Template
{
    private $filename = "";
    private $content = "";
    private $vars = array();

    // Membaca file template, contoh: new Template("lecturer_index")
    function __construct($name)
    {
        $this->filename = dirname(__DIR__) . "/templates/" . $name . ".html";
        if (!is_file($this->filename)) {
            throw new RuntimeException("File template tidak ditemukan: " . $name . ".html");
        }
        $this->content = file_get_contents($this->filename);
    }

    // Mengisi penanda {{$key}} dengan $value
    public function replace($key, $value)
    {
        $this->vars["{{" . $key . "}}"] = (string)$value;
    }

    // Mengembalikan isi template setelah penanda diganti
    public function getContent()
    {
        return strtr($this->content, $this->vars);
    }

    // Menampilkan isi template ke browser
    public function write()
    {
        echo $this->getContent();
    }

    // Escape teks agar aman ditampilkan di HTML (mencegah XSS)
    public static function e($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
    }

    // Menampilkan halaman lengkap: layout (navbar + alert) + isi halaman
    public static function page($title, $active, $content, $flash = null)
    {
        $layout = new Template("layout");
        $layout->replace("TITLE", self::e($title));
        $layout->replace("NAV_LECTURERS", $active === "lecturers" ? "active" : "");
        $layout->replace("NAV_DEPARTMENTS", $active === "departments" ? "active" : "");
        $layout->replace("NAV_COURSES", $active === "courses" ? "active" : "");
        $layout->replace("ALERT", self::alert($flash));
        $layout->replace("CONTENT", $content);
        $layout->write();
    }

    // Alert notifikasi (flash message) berhasil / gagal
    public static function alert($flash)
    {
        if (!is_array($flash) || empty($flash["message"])) {
            return "";
        }
        $type = (isset($flash["type"]) && $flash["type"] === "success") ? "success" : "danger";
        return "<div class='alert alert-" . $type . " alert-dismissible fade show' role='alert'>"
            . self::e($flash["message"])
            . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>"
            . "</div>";
    }

    // Daftar pesan error validasi form
    public static function errors($errors)
    {
        if (empty($errors)) {
            return "";
        }
        $html = "<div class='alert alert-danger'><ul class='mb-0'>";
        foreach ($errors as $error) {
            $html .= "<li>" . self::e($error) . "</li>";
        }
        return $html . "</ul></div>";
    }

    // Pilihan <option> untuk dropdown (kolom 'id' dan 'name' pada $options)
    public static function options($options, $selected, $emptyLabel)
    {
        $html = "<option value=''>" . self::e($emptyLabel) . "</option>";
        foreach ($options as $option) {
            $isSelected = ((string)$selected !== "" && (string)$selected === (string)$option["id"]) ? " selected" : "";
            $html .= "<option value='" . self::e($option["id"]) . "'" . $isSelected . ">"
                . self::e($option["name"]) . "</option>";
        }
        return $html;
    }

    // Tombol Edit (link) dan Delete (form POST + konfirmasi) pada tiap baris tabel
    public static function actionButtons($page, $id)
    {
        $id = (int)$id;
        return "<a class='btn btn-success btn-sm' href='" . $page . "?action=edit&amp;id=" . $id . "'>Edit</a> "
            . "<form method='post' action='" . $page . "' class='d-inline' "
            . "onsubmit=\"return confirm('Yakin ingin menghapus data ini?');\">"
            . "<input type='hidden' name='action' value='delete'>"
            . "<input type='hidden' name='id' value='" . $id . "'>"
            . "<button type='submit' class='btn btn-danger btn-sm'>Delete</button>"
            . "</form>";
    }
}
