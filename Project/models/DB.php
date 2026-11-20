<?php
/**
 * DB (Model dasar)
 * ------------------------------------------------------------
 * Class induk untuk semua model. Tugasnya hanya mengurus koneksi
 * dan menjalankan query ke MySQL. Semua query memakai PREPARED
 * STATEMENT sehingga aman dari SQL Injection (kode awal masih
 * menyambung variabel langsung ke string SQL).
 */
class DB
{
    protected $db_host;
    protected $db_user;
    protected $db_pass;
    protected $db_name;
    protected $db_link = null; // objek koneksi mysqli

    // Konstruktor: hanya menyimpan data koneksi, belum menyambung
    function __construct($db_host, $db_user, $db_pass, $db_name)
    {
        $this->db_host = $db_host;
        $this->db_user = $db_user;
        $this->db_pass = $db_pass;
        $this->db_name = $db_name;
    }

    // Membuka koneksi ke database
    public function open()
    {
        // Error mysqli dilempar sebagai exception (mysqli_sql_exception)
        // agar bisa ditangkap dengan try/catch di controller.
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $this->db_link = new mysqli($this->db_host, $this->db_user, $this->db_pass, $this->db_name);
        $this->db_link->set_charset("utf8mb4"); // dukung karakter unicode
    }

    // Menutup koneksi database
    public function close()
    {
        if ($this->db_link instanceof mysqli) {
            $this->db_link->close();
        }
        $this->db_link = null;
    }

    /**
     * Menjalankan query SELECT dan mengembalikan seluruh baris
     * sebagai array asosiatif.
     * $types  : tipe parameter untuk bind_param (s = string, i = integer)
     * $params : array nilai parameter, sesuai urutan tanda tanya (?)
     */
    protected function select($sql, $types = "", $params = array())
    {
        $stmt = $this->db_link->prepare($sql);
        if ($types !== "") {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();

        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);

        $result->free();
        $stmt->close();
        return $rows;
    }

    /**
     * Menjalankan query INSERT / UPDATE / DELETE.
     * Mengembalikan jumlah baris yang terpengaruh.
     */
    protected function run($sql, $types = "", $params = array())
    {
        $stmt = $this->db_link->prepare($sql);
        if ($types !== "") {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();

        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    }
}
