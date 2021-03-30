<?php

if (!is_user_logged_in()) {
    wp_redirect(LOGIN_PAGE_URL);
    exit;
}

get_header();

?>

<div id="page-title" class="bg-white shadow-sm text-center text-sm-start py-3 py-sm-3 mb-3 mb-sm-0">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="d-flex flex-row align-items-center justify-content-between mb-0">
                    <h1 class="p-0 mb-0 fs-2">Students</h1>

                    <div class="btn-group">
                        <button class="btn btn-outline-secondary border-end-0"><i class="bi bi-person-plus"></i> Add new</button>
                        <button class="btn btn-outline-secondary"><i class="bi bi-download"></i> Export to spreadsheet</button>
                        <button class="btn btn-outline-secondary border-start-0"><i class="bi bi-download"></i> Export to PDF</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container my-sm-5">
    <div class="bg-white shadow-sm p-4 rounded">
        <table id="students-table" class="table table-striped w-100">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email address</th>
                    <th>Contact number</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <th>Name</th>
                    <th>Email address</th>
                    <th>Contact number</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php get_footer(); ?>
