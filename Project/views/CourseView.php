<?php
require_once __DIR__ . '/Template.php';

/**
 * CourseView (View) - tampilan data mata kuliah.
 */
class CourseView
{
    // Halaman daftar mata kuliah
    public function renderIndex($data, $flash)
    {
        $rows = "";
        foreach ($data as $row) {
            // Mata kuliah tanpa dosen pengampu ditampilkan dengan tanda "-"
            $lecturer = ($row["lecturer_name"] === null)
                ? "<span class='text-muted'>-</span>"
                : Template::e($row["lecturer_name"]);

            $rows .= "<tr>"
                . "<th>" . Template::e($row["id"]) . "</th>"
                . "<td>" . Template::e($row["code"]) . "</td>"
                . "<td>" . Template::e($row["name"]) . "</td>"
                . "<td>" . Template::e($row["credits"]) . "</td>"
                . "<td>" . $lecturer . "</td>"
                . "<td>" . Template::actionButtons("course.php", $row["id"]) . "</td>"
                . "</tr>";
        }
        if ($rows === "") {
            $rows = "<tr><td colspan='6' class='text-center text-muted'>Belum ada data mata kuliah.</td></tr>";
        }

        $tpl = new Template("course_index");
        $tpl->replace("TABLE_ROWS", $rows);
        Template::page("Courses", "courses", $tpl->getContent(), $flash);
    }

    // Halaman form tambah / ubah mata kuliah
    public function renderForm($mode, $form, $lecturers, $errors, $flash)
    {
        $isEdit = ($mode === "edit");
        $title = $isEdit ? "Update Course" : "Create Course";

        $tpl = new Template("course_form");
        $tpl->replace("FORM_TITLE", $title);
        $tpl->replace("HEADER_CLASS", $isEdit ? "bg-warning" : "bg-primary");
        $tpl->replace("ACTION", $isEdit ? "update" : "store");
        $tpl->replace("ID_FIELD", $isEdit ? "<input type='hidden' name='id' value='" . (int)$form["id"] . "'>" : "");
        $tpl->replace("ERRORS", Template::errors($errors));
        $tpl->replace("CODE", Template::e($form["code"]));
        $tpl->replace("NAME", Template::e($form["name"]));
        $tpl->replace("CREDITS", Template::e($form["credits"]));
        $tpl->replace("LECTURER_OPTIONS", Template::options($lecturers, $form["lecturer_id"], "-- Belum Ada Dosen --"));

        Template::page($title, "courses", $tpl->getContent(), $flash);
    }
}
