<?php get_header(); ?>

<div>
    <form class="gsg-auth-form" action="" method="POST">
        <img class="mb-2 text-center" src="<?php echo GSG_IMAGES_URL; ?>/logo.png" alt="Gottes Segen Grades" width="72" height="57">

        <h1 class="h3 mb-4 fw-bold">Create an account</h1>

        <div class="bg-white p-4 p-sm-5 shadow-sm rounded">
            <div class="notice alert alert-warning text-start px-3 py-2">Fields marked with <strong class="text-danger">*</strong> are required.</div>

            <div class="row">
                <div class="col-sm mb-3">
                    <label for="first-name" class="d-block text-start mb-1 text-muted">First name <strong class="text-danger">*</strong></label>
                    <input type="text" id="first-name" class="form-control" required autofocus>
                </div>

                <div class="col-sm mb-3">
                    <label for="last-name" class="d-block text-start mb-1 text-muted">Last name <strong class="text-danger">*</strong></label>
                    <input type="text" id="last-name" class="form-control" required>
                </div>
            </div>

            <label for="email-address" class="d-block text-start mb-1 text-muted">Email address <strong class="text-danger">*</strong></label>
            <input type="email" id="email-address" class="form-control" required>

            <div class="mb-3">
                <label for="contact-number" class="d-block text-start mt-3 mb-1 text-muted">Contact number <strong class="text-danger">*</strong></label>
                <input type="text" id="contact-number" class="form-control" placeholder="09171234567" required>
            </div>

            <div class="row">
                <div class="col-sm mb-3">
                    <label for="password" class="d-block text-start mb-1 text-muted">Password <strong class="text-danger">*</strong></label>
                    <input type="password" id="password" class="form-control" required>
                </div>

                <div class="col-sm mb-3">
                    <label for="password-confirmation" class="d-block text-start mb-1 text-muted">Confirm password <strong class="text-danger">*</strong></label>
                    <input type="password" id="password-confirmation" class="form-control" required>
                </div>
            </div>

            <div class="notice alert alert-warning text-start px-3 py-2 mt-2">
                <p class="mb-0 fw-bold">Password requirements for security purposes</p>

                <ul class="unstyled mb-0 ps-3">
                    <li>Must have at least 1 lowercase and uppercase character.</li>
                    <li>Must have at least 1 special character.</li>
                    <li>Must be between 8 to 20 characters long.</li>
                </ul>
            </div>

            <?php wp_nonce_field('gsg_register', 'gsg_register_nonce_field'); ?>

            <button class="w-100 btn btn-lg btn-primary bg-gradient mt-2 position-relative" type="submit"><i class="fa-li fa fa-circle-o-notch fa-spin d-none position-relative start-0 top-0"></i> <span>Submit</span></button>
        </div>

        <p class="mt-4 mb-0 text-muted text-center"><a href="<?php echo LOGIN_PAGE_URL; ?>" class="text-muted text-decoration-none">Back to login</a></p>
    </form>
</div>

<?php get_footer(); ?>
