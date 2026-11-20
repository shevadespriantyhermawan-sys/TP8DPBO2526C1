<?php
require_once __DIR__ . '/Template.php';

/**
 * DepartmentView (View) - tampilan data departemen.
 */
class DepartmentView
{
    // Halaman daftar departemen
    public function renderIndex($data, $flash)
    {
        $rows = "";
        foreach ($data as $row) {
            $rows .= "<tr>"
                . "<th>" . Template::e($row["id"]) . "</th>"
                . "<td>" . Template::e($row["name"]) . "</td>"
                . "<td>" . Template::e($row["faculty"]) . "</td>"
                . "<td>" . Template::e($row["total_lecturers"]) . "</td>"
                . "<td>" . Template::actionButtons("department.php", $row["id"]) . "</td>"
                . "</tr>";
        }
        if ($rows === "") {
            $rows = "<tr><td colspan='5' class='text-center text-muted'>Belum ada data departemen.</td></tr>";
        }

        $tpl = new Template("department_index");
        $tpl->replace("TABLE_ROWS", $rows);
        Template::page("Departments", "departments", $tpl->getContent(), $flash);
    }

    // Halaman form tambah / ubah departemen
    public function renderForm($mode, $form, $errors, $flash)
    {
        $isEdit = ($mode === "edit");
        $title = $isEdit ? "Update Department" : "Create Department";

        $tpl = new Template("department_form");
        $tpl->replace("FORM_TITLE", $title);
        $tpl->replace("HEADER_CLASS", $isEdit ? "bg-warning" : "bg-primary");
        $tpl->replace("ACTION", $isEdit ? "update" : "store");
        $tpl->replace("ID_FIELD", $isEdit ? "<input type='hidden' name='id' value='" . (int)$form["id"] . "'>" : "");
        $tpl->replace("ERRORS", Template::errors($errors));
        $tpl->replace("NAME", Template::e($form["name"]));
        $tpl->replace("FACULTY", Template::e($form["faculty"]));

        Template::page($title, "departments", $tpl->getContent(), $flash);
    }
}
