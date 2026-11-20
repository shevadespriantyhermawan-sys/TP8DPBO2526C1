<?php
require_once __DIR__ . '/Template.php';

/**
 * LecturerView (View) - tampilan data dosen.
 * Hanya menyusun HTML dari data yang diberikan controller;
 * tidak pernah mengakses database.
 */
class LecturerView
{
    // Halaman daftar dosen
    public function renderIndex($data, $flash)
    {
        $rows = "";
        foreach ($data as $row) {
            // Dosen tanpa departemen ditampilkan dengan tanda "-"
            $department = ($row["department_name"] === null)
                ? "<span class='text-muted'>-</span>"
                : Template::e($row["department_name"]);

            $rows .= "<tr>"
                . "<th>" . Template::e($row["id"]) . "</th>"
                . "<td>" . Template::e($row["name"]) . "</td>"
                . "<td>" . Template::e($row["nidn"]) . "</td>"
                . "<td>" . Template::e($row["phone"]) . "</td>"
                . "<td>" . Template::e($row["join_date"]) . "</td>"
                . "<td>" . $department . "</td>"
                . "<td>" . Template::actionButtons("index.php", $row["id"]) . "</td>"
                . "</tr>";
        }
        if ($rows === "") {
            $rows = "<tr><td colspan='7' class='text-center text-muted'>Belum ada data dosen.</td></tr>";
        }

        $tpl = new Template("lecturer_index");
        $tpl->replace("TABLE_ROWS", $rows);
        Template::page("Home", "lecturers", $tpl->getContent(), $flash);
    }

    // Halaman form tambah ($mode = "create") atau ubah ($mode = "edit")
    public function renderForm($mode, $form, $departments, $errors, $flash)
    {
        $isEdit = ($mode === "edit");
        $title = $isEdit ? "Update Lecturer" : "Create Lecturer";

        $tpl = new Template("lecturer_form");
        $tpl->replace("FORM_TITLE", $title);
        $tpl->replace("HEADER_CLASS", $isEdit ? "bg-warning" : "bg-primary");
        $tpl->replace("ACTION", $isEdit ? "update" : "store");
        $tpl->replace("ID_FIELD", $isEdit ? "<input type='hidden' name='id' value='" . (int)$form["id"] . "'>" : "");
        $tpl->replace("ERRORS", Template::errors($errors));
        $tpl->replace("NAME", Template::e($form["name"]));
        $tpl->replace("NIDN", Template::e($form["nidn"]));
        $tpl->replace("PHONE", Template::e($form["phone"]));
        $tpl->replace("JOIN_DATE", Template::e($form["join_date"]));
        $tpl->replace("DEPARTMENT_OPTIONS", Template::options($departments, $form["department_id"], "-- Tanpa Departemen --"));

        Template::page($title, "lecturers", $tpl->getContent(), $flash);
    }
}
