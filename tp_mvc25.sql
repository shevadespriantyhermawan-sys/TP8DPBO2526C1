-- =====================================================================
-- Database : tp_mvc25
-- Praktikum: Desain Pemrograman Berbasis Objek - Modul 9 (MVC)
-- Tabel    : departments (baru), lecturers (lama + kolom department_id),
--            courses (baru)
-- Cara pakai: buka localhost/phpmyadmin -> tab Import -> pilih file ini.
-- Catatan   : seluruh data contoh di bawah ini fiktif.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS tp_mvc25
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_general_ci;

USE tp_mvc25;

-- Hapus tabel lama (urutan: tabel anak dahulu, baru tabel induk)
DROP TABLE IF EXISTS courses;
DROP TABLE IF EXISTS lecturers;
DROP TABLE IF EXISTS departments;

-- ---------------------------------------------------------------------
-- Tabel departments (BARU)
-- Satu departemen memiliki banyak dosen.
-- ---------------------------------------------------------------------
CREATE TABLE departments (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    name    VARCHAR(100) NOT NULL,
    faculty VARCHAR(100) NOT NULL,
    CONSTRAINT uq_departments_name UNIQUE (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------
-- Tabel lecturers (LAMA)
-- Kolom awal (dari query pada kode PHP): id, name, nidn, phone, join_date.
-- Kolom tambahan: department_id (relasi ke departments, boleh kosong).
-- ---------------------------------------------------------------------
CREATE TABLE lecturers (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100) NOT NULL,
    nidn          VARCHAR(10)  NOT NULL,
    phone         VARCHAR(20)  NOT NULL,
    join_date     DATE         NOT NULL,
    department_id INT NULL,
    CONSTRAINT uq_lecturers_nidn UNIQUE (nidn),
    CONSTRAINT fk_lecturers_department
        FOREIGN KEY (department_id) REFERENCES departments (id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------
-- Tabel courses (BARU)
-- Satu dosen dapat mengampu banyak mata kuliah.
-- ---------------------------------------------------------------------
CREATE TABLE courses (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    code        VARCHAR(10)  NOT NULL,
    name        VARCHAR(150) NOT NULL,
    credits     TINYINT UNSIGNED NOT NULL,
    lecturer_id INT NULL,
    CONSTRAINT uq_courses_code UNIQUE (code),
    CONSTRAINT fk_courses_lecturer
        FOREIGN KEY (lecturer_id) REFERENCES lecturers (id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---------------------------------------------------------------------
-- Data contoh: departments
-- ---------------------------------------------------------------------
INSERT INTO departments (name, faculty) VALUES
('Ilmu Komputer', 'FPMIPA'),
('Pendidikan Ilmu Komputer', 'FPMIPA'),
('Pendidikan Matematika', 'FPMIPA');

-- ---------------------------------------------------------------------
-- Data contoh: lecturers
-- ---------------------------------------------------------------------
INSERT INTO lecturers (name, nidn, phone, join_date, department_id) VALUES
('Dr. Ahmad Fauzi, M.Kom.', '0401018801', '081234560001', '2012-08-01', 1),
('Siti Rahmawati, M.T.',    '0415059002', '081234560002', '2015-02-16', 1),
('Budi Santoso, M.Cs.',     '0420078503', '081234560003', '2010-09-01', 2),
('Dewi Lestari, M.Pd.',     '0410129204', '081234560004', '2018-03-12', 2),
('Rizky Maulana, M.Si.',    '0425038905', '081234560005', '2016-07-04', 3),
('Nur Aisyah, M.Kom.',      '0407069106', '081234560006', '2019-01-21', 1);

-- ---------------------------------------------------------------------
-- Data contoh: courses
-- ---------------------------------------------------------------------
INSERT INTO courses (code, name, credits, lecturer_id) VALUES
('IK101', 'Algoritma dan Pemrograman',                3, 1),
('IK102', 'Struktur Data',                            3, 2),
('IK201', 'Desain Pemrograman Berbasis Objek',        3, 6),
('IK202', 'Basis Data',                               3, 2),
('IK203', 'Rekayasa Perangkat Lunak',                 3, 1),
('PK101', 'Pengantar Pendidikan',                     2, 4),
('PK201', 'Strategi Pembelajaran Komputer',           2, 3),
('MT101', 'Kalkulus',                                 3, 5);
