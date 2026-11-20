<?php
/**
 * Department (Model) - tabel `departments` (TABEL BARU)
 * Mengelola data departemen/program studi. Satu departemen
 * memiliki banyak dosen (relasi 1-N ke tabel lecturers).
 */
class Department extends DB
{
    // Semua departemen + jumlah dosen di tiap departemen (LEFT JOIN + COUNT)
    public function getAll()
    {
        $sql = "SELECT d.id, d.name, d.faculty, COUNT(l.id) AS total_lecturers
                FROM departments d
                LEFT JOIN lecturers l ON l.department_id = d.id
                GROUP BY d.id, d.name, d.faculty
                ORDER BY d.id";
        return $this->select($sql);
    }

    // Daftar id & nama untuk isi dropdown pada form dosen
    public function getOptions()
    {
        return $this->select("SELECT id, name FROM departments ORDER BY name");
    }

    // Satu departemen berdasarkan id (null jika tidak ada)
    public function getById($id)
    {
        $rows = $this->select("SELECT id, name, faculty FROM departments WHERE id = ?", "i", array($id));
        return count($rows) > 0 ? $rows[0] : null;
    }

    // CREATE
    public function add($name, $faculty)
    {
        return $this->run("INSERT INTO departments (name, faculty) VALUES (?, ?)", "ss", array($name, $faculty));
    }

    // UPDATE
    public function update($id, $name, $faculty)
    {
        return $this->run("UPDATE departments SET name = ?, faculty = ? WHERE id = ?", "ssi", array($name, $faculty, $id));
    }

    // DELETE
    public function delete($id)
    {
        return $this->run("DELETE FROM departments WHERE id = ?", "i", array($id));
    }
}
