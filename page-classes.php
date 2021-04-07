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

<div class="container my-3">
</div>

<?php include_once 'templates/create-class-modal.php'; ?>

<?php get_footer(); ?>
