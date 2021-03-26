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
                <h1 class="p-0 mb-0 fs-2">Grades</h1>
            </div>
        </div>
    </div>
</div>

<div class="container my-3">
</div>

<?php get_footer(); ?>
