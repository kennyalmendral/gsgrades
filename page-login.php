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

        <h1 class="h3 mb-4 fw-bold">Gottes Segen Grades</h1>

        <div class="bg-white p-4 p-sm-5 shadow-sm rounded">
            <?php if (isset($_GET['logged_out'])): ?>
                <div class="alert alert-success fs-8 px-3 py-2">You have been logged out successfully.</div>
            <?php endif; ?>

            <?php if (isset($_GET['account_created'])): ?>
                <div class="alert alert-success fs-8 px-3 py-2">Your account has been created successfully. Please check your email address for your login credentials.</div>
            <?php endif; ?>

            <?php if (isset($_GET['password_changed'])): ?>
                <div class="alert alert-success fs-8 px-3 py-2">Your password has been changed successfully.</div>
            <?php endif; ?>


            <div class="input-group mb-3">
                <label for="email-address" class="input-group-text"><i class="bi bi-person-fill"></i></label>
                <input type="email" id="email-address" class="form-control" placeholder="Email address" required autofocus>
            </div>

            <div class="input-group mb-3">
                <label for="password" class="input-group-text"><i class="bi bi-lock-fill"></i></label>
                <input type="password" id="password" class="form-control" placeholder="Password" required>
            </div>

            <div class="checkbox mb-0 mt-3">
                <label class="text-muted"><input type="checkbox" id="remember" name="remember"> &nbsp;Remember me</label>
            </div>

            <?php wp_nonce_field('gsg_login', 'gsg_login_nonce_field'); ?>

            <button class="w-100 btn btn-lg btn-primary bg-gradient mt-3 position-relative" type="submit"><i class="fa-li fa fa-circle-o-notch fa-spin d-none position-relative start-0 top-0"></i> <span>Login</span></button>
        </div>

        <p class="mt-4 mb-0 text-center"><a href="<?php echo REGISTER_PAGE_URL; ?>" class="text-muted text-decoration-none">Create an account</a> <span class="mx-2 nav-separator">|</span> <a href="<?php echo FORGOT_PASSWORD_PAGE_URL; ?>" class="text-muted text-decoration-none">Forgot password</a></p>
    </form>
</div>

<?php get_footer(); ?>
