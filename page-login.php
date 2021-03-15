<?php

if (is_user_logged_in()) {
    wp_redirect(home_url());
    exit;
}

get_header();

?>

<div>
    <form id="gsg-login-form" action="" method="POST">
        <img class="mb-2 text-center" src="<?php echo GSG_IMAGES_URL; ?>/logo.png" alt="" width="72" height="57">

        <h1 class="h3 mb-4 fw-bold">Gottes Segen Grades</h1>

        <label for="email" class="visually-hidden">Email address</label>
        <input type="email" id="email" class="form-control" placeholder="Email address" autofocus>

        <label for="password" class="visually-hidden">Password</label>
        <input type="password" id="password" class="form-control mt-3" placeholder="Password">

        <div class="checkbox mb-2 mt-2">
            <label><input type="checkbox" id="remember" name="remember"> Remember me</label>
        </div>

        <?php wp_nonce_field('gsg_login', 'gsg_login_nonce_field'); ?>

        <button class="w-100 btn btn-lg btn-primary mt-3 position-relative" type="submit"><i class="fa-li fa fa-circle-o-notch fa-spin d-none position-relative start-0 top-0"></i> <span>Login</span></button>

        <p class="mt-4 mb-4 text-muted text-center">No account yet? <a href="#">Register now</a></p>
    </form>
</div>

<?php get_footer(); ?>
