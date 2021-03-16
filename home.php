<?php

if (is_user_logged_in()) {
    gsg_ajax_login_redirect();
} else {
    wp_redirect(LOGIN_PAGE_URL);
    exit;
}

get_header();

?>

<div class="container my-3">
    <h1>Hello <?php echo $current_user->display_name; ?></h1>
</div>

<?php get_footer(); ?>
