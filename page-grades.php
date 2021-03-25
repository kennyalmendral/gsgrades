<?php

if (!is_user_logged_in()) {
    wp_redirect(LOGIN_PAGE_URL);
    exit;
}

get_header();

?>

<div id="page-title" class="bg-white shadow-sm pt-2 pb-3">
    <div class="container">
        <h1 class="p-0 mb-0 fs-2">Grades</h1>
    </div>
</div>

<div class="container my-3">
</div>

<?php get_footer(); ?>
