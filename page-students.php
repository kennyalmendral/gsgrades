<?php

if (!is_user_logged_in()) {
    wp_redirect(LOGIN_PAGE_URL);
    exit;
}

get_header();

?>

<div class="container my-3">
    <h1>Students</h1>
</div>

<?php get_footer(); ?>
