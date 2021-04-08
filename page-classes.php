<?php

if (!is_user_logged_in()) {
    wp_redirect(LOGIN_PAGE_URL);
    exit;
}

get_header();

?>

<div id="page-title" class="bg-white shadow-sm py-3">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="d-flex flex-row align-items-center justify-content-between mb-0">
                    <h1 class="p-0 mb-0 fs-2">Classes</h1>

                    <button name="create_class" id="create-class" class="btn btn-outline-secondary"><i class="bi bi-plus"></i> Create new</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container my-sm-5">
    <div id="main-content" class="bg-white shadow-sm p-4 rounded">
        <div id="status-filter" class="d-flex align-items-center me-2">
            <select id="status-filter-select" class="form-select form-select-sm fs-6">
                <option value="">Select status</option>
                <option value="ongoing">Ongoing</option>
                <option value="completed">Completed</option>
            </select>
        </div>

        <table id="classes-table" class="table table-striped table-responsive w-100">
            <thead>
                <tr>
                    <th width="9%">ID</th>
                    <th width="12%">Code</th>
                    <th width="9%">Level</th>
                    <th width="14%"># of hours to complete</th>
                    <th width="12%">Completed hours</th>
                    <th width="12%">Remaining hours</th>
                    <th width="10%">Status</th>
                    <th width="20%"class="text-center">Actions</th>
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Level</th>
                    <th># of hours to complete</th>
                    <th>Completed hours</th>
                    <th>Remaining hours</th>
                    <th>Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php include_once 'templates/create-class-modal.php'; ?>

<?php get_footer(); ?>
