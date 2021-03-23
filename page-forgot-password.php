<?php

if (is_user_logged_in()) {
    wp_redirect(home_url());
    exit;
}

get_header();

?>

<div>
    <form class="gsg-auth-form" action="" method="POST">
        <img class="mb-2 text-center" src="<?php echo GSG_IMAGES_URL; ?>/logo.png" alt="" width="72" height="57">

        <h1 class="h3 mb-4 fw-bold">Forgot Password</h1>

        <div class="bg-white p-4 p-sm-5 shadow-sm rounded">
            <?php if (isset($_GET['sent'])): ?>
                <div class="alert alert-success fs-8 px-3 py-2">Your password has been sent to your email address.</div>
            <?php endif; ?>

            <label for="email-address" class="d-block text-start mb-1 text-muted">Email address</label>
            <input type="email" id="email-address" class="form-control">

            <?php wp_nonce_field('gsg_forgot_password', 'gsg_forgot_password_nonce_field'); ?>

            <button class="w-100 btn btn-lg btn-primary mt-3 position-relative" type="submit"><i class="fa-li fa fa-circle-o-notch fa-spin d-none position-relative start-0 top-0"></i> <span>Send</span></button>
        </div>

        <p class="mt-4 mb-0 text-muted text-center"><a href="<?php echo LOGIN_PAGE_URL; ?>" class="text-muted text-decoration-none">Back to login</a></p>
    </form>
</div>

<?php get_footer(); ?>
