<?php

if (!is_user_logged_in()) {
    wp_redirect(LOGIN_PAGE_URL);
    exit;
}

global $current_user;

$name = explode(' ', $current_user->display_name);

get_header();

?>

<div id="page-title" class="bg-white shadow-sm pt-xs-2 pt-sm-2 pb-sm-3">
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="p-0 mb-0 fs-2">Account</h1>
            </div>
        </div>
    </div>
</div>

<div id="main-content" class="container my-sm-5">
    <div class="row align-items-start">
        <div id="account-info-container" class="col-md-7 mb-md-0 mb-sm-4 bg-white shadow-sm p-4 rounded">
            <?php if (isset($_GET['info_updated'])): ?>
                <div class="alert alert-success fs-8 px-3 py-2">Your account has been updated successfully.</div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="row">
                    <div class="col-sm mb-3">
                        <label for="first-name" class="d-block text-start mb-1 text-muted">First name</label>
                        <input type="text" id="first-name" class="form-control" value="<?php echo esc_attr($name[0]); ?>">
                    </div>

                    <div class="col-sm mb-3">
                        <label for="last-name" class="d-block text-start mb-1 text-muted">Last name</label>
                        <input type="text" id="last-name" class="form-control" value="<?php echo esc_attr($name[1]); ?>">
                    </div>
                </div>

                <label for="email-address" class="d-block text-start mb-1 text-muted">Email address</label>
                <input type="email" id="email-address" class="form-control" value="<?php echo esc_attr($current_user->user_email); ?>">

                <div class="mb-3">
                    <label for="contact-number" class="d-block text-start mt-3 mb-1 text-muted">Contact number</label>
                    <input type="text" id="contact-number" class="form-control" placeholder="09171234567" value="<?php echo esc_attr(get_user_meta($current_user->ID, 'contact_number', true)); ?>">
                </div>

                <div class="row">
                    <div class="col-sm mb-3">
                        <label for="password" class="d-block text-start mb-1 text-muted">Password</label>
                        <input type="password" id="password" class="form-control">
                    </div>

                    <div class="col-sm mb-3">
                        <label for="password-confirmation" class="d-block text-start mb-1 text-muted">Confirm password</label>
                        <input type="password" id="password-confirmation" class="form-control">
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

                <?php wp_nonce_field('gsg_save_account_info', 'gsg_save_account_info_nonce_field'); ?>
                
                <button id="save-changes-button" class="btn btn-primary bg-gradient mt-2 position-relative" type="submit"><i class="fa-li fa fa-circle-o-notch fa-spin d-none position-relative start-0 top-0"></i> <span>Save changes</span></button>
            </form>
        </div>

        <div id="profile-picture-container" class="card col-md-4 offset-md-1 border-0 p-0 bg-white shadow-sm rounded">
            <form action="" method="POST">
                <div id="placeholder" class="card-img-top">
                    <h4 class="m-0 text-muted fs-1 fw-bold"><?php echo gsg_get_initials($current_user->display_name); ?></h4>
                </div>
    
                <!--<img src="https://via.placeholder.com/286x180" alt="Profile Picture" class="card-img-top">-->

                <div class="card-body p-4">
                    <label for="profile-picture" class="card-title text-muted mb-1">Profile Picture</label>

                    <input type="file" id="profile-picture" class="form-control fs-6">

                    <?php wp_nonce_field('gsg_update_profile_picture', 'gsg_update_profile_picture_nonce_field'); ?>

                    <button id="upload-update-button" class="btn btn-primary bg-gradient w-100 mt-3 position-relative"><i class="fa-li fa fa-circle-o-notch fa-spin d-none position-relative start-0 top-0"></i> <span>Upload</span></button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php get_footer(); ?>
