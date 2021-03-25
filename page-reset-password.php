<?php

if (is_user_logged_in()) {
    wp_redirect(home_url());
    exit;
}

if (isset($_GET['key'], $_GET['login'])) {
    $reset_password_key = $_GET['key'];
    $user_login = $_GET['login'];

    $user = check_password_reset_key($reset_password_key, $user_login);

    if (is_wp_error($user)) {
        wp_redirect(FORGOT_PASSWORD_PAGE_URL);
        exit;
    }
} else {
    wp_redirect(FORGOT_PASSWORD_PAGE_URL);
    exit;
}

get_header();

?>

<div>
    <form class="gsg-auth-form" action="" method="POST">
        <img class="mb-2 text-center" src="<?php echo GSG_IMAGES_URL; ?>/logo.png" alt="" width="72" height="57">

        <h1 class="h3 mb-4 fw-bold">Reset Password</h1>

        <div class="bg-white p-4 p-sm-5 shadow-sm rounded">
            <div class="mb-3">
                <label for="password" class="d-block text-start mb-1 text-muted">New Password</label>
                <input type="password" id="password" class="form-control" required autofocus>
            </div>

            <div class="mb-3">
                <label for="password-confirmation" class="d-block text-start mb-1 text-muted">New Password Confirmation</label>
                <input type="password" id="password-confirmation" class="form-control" required>
            </div>

            <div class="notice alert alert-warning text-start px-3 py-2 mt-4">
                <p class="mb-0 fw-bold">Password requirements for security purposes</p>

                <ul class="unstyled mb-0 ps-3">
                    <li>Must have at least 1 lowercase and uppercase character.</li>
                    <li>Must have at least 1 special character.</li>
                    <li>Must be between 8 to 20 characters long.</li>
                </ul>
            </div>

            <input type="hidden" id="reset-password-key" value="<?php echo esc_attr($reset_password_key); ?>" required>
            <input type="hidden" id="user-login" value="<?php echo esc_attr($user_login); ?>" required>

            <?php wp_nonce_field('gsg_reset_password', 'gsg_reset_password_nonce_field'); ?>

            <button class="w-100 btn btn-lg btn-primary bg-gradient mt-2 position-relative" type="submit"><i class="fa-li fa fa-circle-o-notch fa-spin d-none position-relative start-0 top-0"></i> <span>Save</span></button>
        </div>

        <p class="mt-4 mb-0 text-muted text-center"><a href="<?php echo LOGIN_PAGE_URL; ?>" class="text-muted text-decoration-none">Back to login</a></p>
    </form>
</div>

<?php get_footer(); ?>
