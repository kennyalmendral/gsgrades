<?php

if (!is_user_logged_in()) {
    wp_redirect(LOGIN_PAGE_URL);
    exit;
}

if (isset($_POST['export_csv'])) {/*{{{*/
    if (!isset($_POST['gsg_export_students_csv_nonce_field']) || !wp_verify_nonce($_POST['gsg_export_students_csv_nonce_field'], 'gsg_export_students_csv')) {
        wp_redirect(STUDENTS_PAGE_URL);
        exit;
    }

    function gsg_export_students_csv() {
        $user_query = new WP_User_Query(array(
            'role' => 'student',
            'orderby' => 'display_name',
            'order' => 'ASC'
        ));

        $users = array();

        foreach ($user_query->results as $user) {
            $users[] = array(
                'name' => $user->display_name,
                'id' => $user->ID,
                'email_address' => $user->user_email,
                'contact_number' => get_user_meta($user->ID, 'contact_number', true),
                'date_registered' => date('F d, Y', strtotime($user->user_registered)),
                'profile_picture_url' => !empty(get_user_meta($user->ID, 'profile_picture', true)) ? GSG_UPLOADS_URL . '/profile-pictures/'. get_user_meta($user->ID, 'profile_picture', true) : 'N/A'
            );
        }

        $filepath = GSG_UPLOADS_PATH . '/exports/Gottes Segen Students.csv';
        $csv_file = fopen($filepath, 'w+');

        $heading_columns = array('Name', 'ID', 'Email Address', 'Contact Number', 'Date Registered', 'Profile Picture URL');

        fputcsv($csv_file, $heading_columns);

        foreach ($users as $user) {
            $user = mb_convert_encoding($user, 'cp1252', 'utf-8');

            $user['contact_number'] = "=\"". $user['contact_number'] . "\"";
            $user['date_registered'] = "=\"". $user['date_registered'] . "\"";

            fputcsv($csv_file, $user);
        }

        fclose($csv_file);

        header('Content-type: text/csv');
        header('Content-disposition: attachment; filename=Gottes Segen Students.csv');

        readfile($filepath);

        exit;
    }

    gsg_export_students_csv();
}/*}}}*/

if (isset($_POST['export_pdf'])) {/*{{{*/
    if (!isset($_POST['gsg_export_students_pdf_nonce_field']) || !wp_verify_nonce($_POST['gsg_export_students_pdf_nonce_field'], 'gsg_export_students_pdf')) {
        wp_redirect(STUDENTS_PAGE_URL);
        exit;
    }

    function gsg_export_students_pdf() {
        $user_query = new WP_User_Query(array(
            'role' => 'student',
            'orderby' => 'display_name',
            'order' => 'ASC'
        ));

        $users = array();

        foreach ($user_query->results as $user) {
            $users[] = array(
                'id' => $user->ID,
                'name' => $user->display_name,
                'email_address' => $user->user_email,
                'contact_number' => get_user_meta($user->ID, 'contact_number', true),
                'date_registered' => date('F d, Y', strtotime($user->user_registered)),
                'profile_picture_url' => !empty(get_user_meta($user->ID, 'profile_picture', true)) ? GSG_UPLOADS_URL . '/profile-pictures/'. get_user_meta($user->ID, 'profile_picture', true) : 'N/A'
            );
        }

        $filepath = GSG_UPLOADS_PATH . '/exports/Gottes Segen Students.pdf';

        require_once GSG_VENDORS_PATH . '/fpdf182/fpdf.php';

        $pdf = new FPDF('L');

        $pdf->AddPage();

        $pdf->SetFont('Arial', 'B', 24);
        $pdf->Cell(0, 0, 'Gottes Segen Students', 0, 1, 'C');

        $pdf->Ln(10);

        $pdf->SetFont('Arial', 'B', 9);

        $data = array();

        $heading_columns = array('ID', 'Name', 'Email Address', 'Contact Number', 'Date Registered', 'Profile Picture URL');

        foreach ($heading_columns as $column) {
            $pdf->Cell(46.2, 7, $column, 1, 0, 'L');
        }

        $pdf->Ln();

        $pdf->SetFont('Arial', '', 9);

        foreach ($users as $row) {
            foreach ($row as $column) {
                $pdf->Cell(46.2, 6, $column, 1, 0, 'L');
            }

            $pdf->Ln();
        }

        $pdf->Ln();

        $pdf->Output('D', 'Gottes Segen Grades.pdf', true);

        exit;
    }

    gsg_export_students_pdf();
}/*}}}*/

get_header();

?>

<div id="page-title" class="bg-white shadow-sm text-center text-sm-start py-3 py-sm-3 mb-3 mb-sm-0">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="d-flex flex-row align-items-center justify-content-between mb-0">
                    <h1 class="p-0 mb-0 fs-2">Students</h1>

                    <form action="" method="POST">
                        <?php wp_nonce_field('gsg_export_students_csv', 'gsg_export_students_csv_nonce_field'); ?>
                        <?php wp_nonce_field('gsg_export_students_pdf', 'gsg_export_students_pdf_nonce_field'); ?>

                        <div class="btn-group">
                            <button name="export_csv" id="export-csv" class="btn btn-outline-secondary"><i class="bi bi-download"></i> Export to spreadsheet</button>
                            <button name="export_pdf" id="export-pdf" class="btn btn-outline-secondary border-start-0"><i class="bi bi-download"></i> Export to PDF</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container my-sm-5">
    <div class="bg-white shadow-sm p-4 rounded">
        <table id="students-table" class="table table-striped table-responsive w-100">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email Address</th>
                    <th>Contact Number</th>
                    <th>Date Registered</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email Address</th>
                    <th>Contact Number</th>
                    <th>Date Registered</th>
                    <th class="text-center">Action</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php include_once 'templates/student-details-modal.php'; ?>

<?php get_footer(); ?>
