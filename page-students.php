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

        $heading_columns = array('Name', 'ID', 'Email address', 'Contact number', 'Date registered', 'Profile picture');

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
                'profile_picture_url' => !empty(get_user_meta($user->ID, 'profile_picture', true)) ? GSG_UPLOADS_URL . '/profile-pictures/'. get_user_meta($user->ID, 'profile_picture', true) : null
            );
        }

        require_once GSG_VENDORS_PATH . '/dompdf/autoload.inc.php';

        $options = new \Dompdf\Options();
        $options->setIsRemoteEnabled(true);
        $options->isHtml5ParserEnabled(true);

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->setPaper('A4', 'landscape');

        $html_template_raw = file_get_contents(GSG_TEMPLATES_PATH . '/students.html');
        $html_template = '';

        $users_table = '<table cellpadding=0 cellspacing=0>';
            $users_table .= '<thead>';
                $users_table .= '<tr>';
                    $users_table .= '<th>ID</th>';
                    $users_table .= '<th>Name</th>';
                    $users_table .= '<th>Email address</th>';
                    $users_table .= '<th>Contact number</th>';
                    $users_table .= '<th>Date registered</th>';
                    $users_table .= '<th>Profile picture</th>';
                $users_table .= '</tr>';
            $users_table .= '</thead>';

            $users_table .= '<tbody>';
                foreach ($users as $user) {
                    $users_table .= '<tr>';
                        $users_table .= "<td>{$user['id']}</td>";
                        $users_table .= "<td>{$user['name']}</td>";
                        $users_table .= "<td>{$user['email_address']}</td>";
                        $users_table .= "<td>{$user['contact_number']}</td>";
                        $users_table .= "<td>{$user['date_registered']}</td>";

                        if (empty($user['profile_picture_url'])) {
                            $users_table .= '<td>N/A</td>';
                        } else {
                            $users_table .= '<td><a href="' . $user['profile_picture_url'] . '">Click to view</a></td>';
                        }
                    $users_table .= '</tr>';
                }
            $users_table .= '</tbody>';
        $users_table .= '</table>';

        $variables = array(
            '[STUDENTS]' => $users_table,
        );

        foreach ($variables as $key => $value) {
            $html_template = strtr($html_template_raw, $variables);
        }

        $dompdf->loadHtml($html_template, 'UTF-8');
        $dompdf->render();
        $dompdf->stream('Gottes Segen Students.pdf');

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
    <div id="main-content" class="bg-white shadow-sm p-4 rounded">
        <table id="students-table" class="table table-striped table-responsive w-100">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email address</th>
                    <th>Contact number</th>
                    <th>Date registered</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email address</th>
                    <th>Contact number</th>
                    <th>Date registered</th>
                    <th class="text-center">Action</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php include_once 'templates/student-details-modal.php'; ?>

<?php get_footer(); ?>
