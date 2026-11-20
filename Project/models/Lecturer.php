<?php
/**
 * Lecturer (Model) - tabel `lecturers` (TABEL LAMA)
 * Berisi semua query yang sebelumnya tersebar di index.php,
 * create.php, edit.php dan delete.php. Tabel ini diberi kolom
 * baru `department_id` (relasi ke tabel departments).
 */
class Lecturer extends DB
{
    // Semua dosen + nama departemennya (LEFT JOIN agar dosen tanpa departemen tetap tampil)
    public function getAll()
    {
        $sql = "SELECT l.id, l.name, l.nidn, l.phone, l.join_date, l.department_id,
                       d.name AS department_name
                FROM lecturers l
                LEFT JOIN departments d ON d.id = l.department_id
                ORDER BY l.id";
        return $this->select($sql);
    }

    // Daftar id & nama dosen untuk isi dropdown pada form mata kuliah
    public function getOptions()
    {
        return $this->select("SELECT id, name FROM lecturers ORDER BY name");
    }

    // Satu dosen berdasarkan id (null jika tidak ada)
    public function getById($id)
    {
        $sql = "SELECT id, name, nidn, phone, join_date, department_id FROM lecturers WHERE id = ?";
        $rows = $this->select($sql, "i", array($id));
        return count($rows) > 0 ? $rows[0] : null;
    }

    // CREATE (department_id boleh null)
    public function add($name, $nidn, $phone, $join_date, $department_id)
    {
        $sql = "INSERT INTO lecturers (name, nidn, phone, join_date, department_id) VALUES (?, ?, ?, ?, ?)";
        return $this->run($sql, "ssssi", array($name, $nidn, $phone, $join_date, $department_id));
    }

    // UPDATE
    public function update($id, $name, $nidn, $phone, $join_date, $department_id)
    {
        $sql = "UPDATE lecturers SET name = ?, nidn = ?, phone = ?, join_date = ?, department_id = ? WHERE id = ?";
        return $this->run($sql, "ssssii", array($name, $nidn, $phone, $join_date, $department_id, $id));
    }

    // DELETE
    public function delete($id)
    {
        return $this->run("DELETE FROM lecturers WHERE id = ?", "i", array($id));
    }
}
