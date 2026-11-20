<?php
/**
 * Course (Model) - tabel `courses` (TABEL BARU)
 * Mengelola data mata kuliah. Setiap mata kuliah diampu oleh
 * satu dosen (relasi N-1 ke tabel lecturers lewat lecturer_id).
 */
class Course extends DB
{
    // Semua mata kuliah + nama dosen pengampunya
    public function getAll()
    {
        $sql = "SELECT c.id, c.code, c.name, c.credits, c.lecturer_id,
                       l.name AS lecturer_name
                FROM courses c
                LEFT JOIN lecturers l ON l.id = c.lecturer_id
                ORDER BY c.id";
        return $this->select($sql);
    }

    // Satu mata kuliah berdasarkan id (null jika tidak ada)
    public function getById($id)
    {
        $sql = "SELECT id, code, name, credits, lecturer_id FROM courses WHERE id = ?";
        $rows = $this->select($sql, "i", array($id));
        return count($rows) > 0 ? $rows[0] : null;
    }

    // CREATE (lecturer_id boleh null)
    public function add($code, $name, $credits, $lecturer_id)
    {
        $sql = "INSERT INTO courses (code, name, credits, lecturer_id) VALUES (?, ?, ?, ?)";
        return $this->run($sql, "ssii", array($code, $name, $credits, $lecturer_id));
    }

    // UPDATE
    public function update($id, $code, $name, $credits, $lecturer_id)
    {
        $sql = "UPDATE courses SET code = ?, name = ?, credits = ?, lecturer_id = ? WHERE id = ?";
        return $this->run($sql, "ssiii", array($code, $name, $credits, $lecturer_id, $id));
    }

    // DELETE
    public function delete($id)
    {
        return $this->run("DELETE FROM courses WHERE id = ?", "i", array($id));
    }
}
