<?php

if (!is_user_logged_in()) {
    wp_redirect(LOGIN_PAGE_URL);
    exit;
}

global $current_user;

$name = explode(' ', $current_user->display_name);
$profile_picture_name = basename(gsg_current_user_profile_picture());

get_header();

?>

<div id="page-title" class="bg-white shadow-sm text-center text-sm-start py-3 py-sm-3 mb-3 mb-sm-0">
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="p-0 mb-0 fs-2">Account</h1>
            </div>
        </div>
    </div>
</div>

<div id="main-content" class="container my-sm-5">
    <div class="row mx-0 align-items-start">
        <div id="account-info-container" class="col-md-7 mb-3 mb-sm-4 mb-md-0 bg-white shadow-sm p-4 rounded">
            <?php if (isset($_GET['info_updated'])): ?>
                <div class="alert alert-success fs-8 px-3 py-2">Your account has been updated successfully.</div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="row">
                    <div class="col-sm mb-3">
                        <label for="first-name" class="d-block text-start mb-1 text-muted">First name</label>
                        <input type="text" id="first-name" class="form-control" value="<?php echo esc_attr($name[0]); ?>" required>
                    </div>

                    <div class="col-sm mb-3">
                        <label for="last-name" class="d-block text-start mb-1 text-muted">Last name</label>
                        <input type="text" id="last-name" class="form-control" value="<?php echo esc_attr($name[1]); ?>" required>
                    </div>
                </div>

                <label for="email-address" class="d-block text-start mb-1 text-muted">Email address</label>
                <input type="email" id="email-address" class="form-control" value="<?php echo esc_attr($current_user->user_email); ?>" required>

                <div class="mb-3">
                    <label for="contact-number" class="d-block text-start mt-3 mb-1 text-muted">Contact number</label>
                    <input type="text" id="contact-number" class="form-control" placeholder="09171234567" value="<?php echo esc_attr(get_user_meta($current_user->ID, 'contact_number', true)); ?>" required>
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
                
                <button id="save-changes-button" class="btn btn-primary mt-2 position-relative" type="submit"><i class="fa-li fa fa-circle-o-notch fa-spin d-none position-relative start-0 top-0"></i> <span>Save changes</span></button>
            </form>
        </div>

        <div id="profile-picture-container" class="col-md-5 mb-3 mb-sm-0 border-0 p-0 bg-white shadow-sm rounded">
            <?php if (gsg_current_user_has_profile_picture()): ?>
                <div id="image-wrap" class="py-5 py-md-4">
                    <img src="<?php echo gsg_current_user_profile_picture(); ?>" alt="<?php echo $current_user->display_name; ?>">
                </div>
            <?php else: ?>
                <div id="image-wrap" class="py-5 py-md-4">
                    <h4 class="m-0 text-muted"><?php echo gsg_get_initials($current_user->display_name); ?></h4>
                </div>
            <?php endif; ?>

            <div id="control-group" class="p-4">
                <?php if (isset($_GET['profile_picture_updated'])): ?>
                    <div class="alert alert-success fs-8 px-3 py-2">Your profile picture has been updated successfully.</div>
                <?php elseif (isset($_GET['profile_picture_uploaded'])): ?>
                    <div class="alert alert-success fs-8 px-3 py-2">Your profile picture has been uploaded successfully.</div>
                <?php elseif (isset($_GET['profile_picture_removed'])): ?>
                    <div class="alert alert-success fs-8 px-3 py-2">Your profile picture has been removed successfully.</div>
                <?php endif; ?>

                <label for="profile-picture" class="text-muted mb-1">Profile picture</label>

                <input type="file" id="profile-picture" class="form-control fs-6">

                <?php wp_nonce_field('gsg_upload_update_profile_picture', 'gsg_upload_update_profile_picture_nonce_field'); ?>
                <?php wp_nonce_field('gsg_remove_profile_picture', 'gsg_remove_profile_picture_nonce_field'); ?>

                <?php if (gsg_current_user_has_profile_picture()): ?>
                    <?php $button_text = 'Update'; ?>
                <?php else: ?>
                    <?php $button_text = 'Upload'; ?>
                <?php endif; ?>

                <div class="btn-group mt-3 w-100">
                    <button id="upload-update-button" class="btn btn-primary position-relative"><i class="fa-li fa fa-circle-o-notch fa-spin d-none position-relative start-0 top-0"></i> <span><?php echo $button_text; ?></span></button>
                    <button id="remove-button" class="btn btn-danger position-relative" data-filename="<?php echo esc_attr($profile_picture_name); ?>"><i class="fa-li fa fa-circle-o-notch fa-spin d-none position-relative start-0 top-0"></i> <span>Remove</span></button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
