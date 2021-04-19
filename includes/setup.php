<?php

function gsg_setup_theme() {
	add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
	add_theme_support('html5', array(
        'comment-list',
        'comment-form',
        'search-form',
        'gallery',
        'caption'
    ));

	register_nav_menu('primary_menu', 'Primary Menu');
}

add_action('after_setup_theme', 'gsg_setup_theme');

function gsg_enqueue() {
	global $wpdb;

	$uri = get_theme_file_uri();
	$version = GSG_DEV_MODE ? time() : false;

	wp_register_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css', array(), null);
    wp_enqueue_style('bootstrap');

	wp_register_style('bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.0/font/bootstrap-icons.css', array(), null);
    wp_enqueue_style('bootstrap-icons');

	wp_register_style('font-awesome', 'https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', array(), null);
    wp_enqueue_style('font-awesome');

    if (gsg_is_students_page() || gsg_is_classes_page()) {
        wp_register_style('bootstrap-datatables', 'https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css', array('bootstrap'), $version);
        wp_enqueue_style('bootstrap-datatables');
    }

	wp_register_style('gsg-style', "$uri/style.css", array(), $version);
	wp_enqueue_style('gsg-style');

    if (gsg_is_login_page() || gsg_is_register_page() || gsg_is_forgot_password_page() || gsg_is_reset_password_page()) {
        wp_register_style('gsg-auth-style', "$uri/css/auth.css", array(), $version);
        wp_enqueue_style('gsg-auth-style');
    }

	wp_register_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js', array('jquery'), null, true);
	wp_enqueue_script('bootstrap');

    if (gsg_is_students_page() || gsg_is_classes_page()) {
        wp_register_script('datatables', 'https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js', array('jquery'), null, true);
        wp_enqueue_script('datatables');

        wp_register_script('bootstrap-datatables', 'https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js', array('jquery', 'datatables'), null, true);
        wp_enqueue_script('bootstrap-datatables');
    }

	wp_register_script('gsg-script', "$uri/script.js", array('jquery'), $version, true);

    $has_profile_picture = false;

    if (is_user_logged_in()) {
        global $current_user;

        $has_profile_picture = empty(get_user_meta($current_user->ID, 'profile_picture', true)) ? false : true;
        $current_user_name_initials = gsg_get_initials($current_user->display_name);
    }

	wp_localize_script('gsg-script', 'gsg', array(
		'ajaxUrl' => admin_url('admin-ajax.php'),
        'homeUrl' => home_url('/'),
        'loginUrl' => LOGIN_PAGE_URL,
        'registerUrl' => REGISTER_PAGE_URL,
        'forgotPasswordUrl' => FORGOT_PASSWORD_PAGE_URL,
        'resetPasswordUrl' => RESET_PASSWORD_PAGE_URL,
        'accountUrl' => ACCOUNT_PAGE_URL,
        'gradesUrl' => GRADES_PAGE_URL,
        'recordsUrl' => RECORDS_PAGE_URL,
        'classesUrl' => CLASSES_PAGE_URL,
        'studentsUrl' => STUDENTS_PAGE_URL,
        'isLoginPage' => gsg_is_login_page() ? true : false,
        'isRegisterPage' => gsg_is_register_page() ? true : false,
        'isForgotPasswordPage' => gsg_is_forgot_password_page() ? true : false,
        'isResetPasswordPage' => gsg_is_reset_password_page() ? true : false,
        'isAccountPage' => gsg_is_account_page() ? true : false,
        'isClassesPage' => gsg_is_classes_page() ? true : false,
        'isClassPage' => is_singular('class') ? true : false,
        'isStudentsPage' => gsg_is_students_page() ? true : false,
        'logoutNonce' => wp_create_nonce('logout-nonce'),
        'getClassPermalinkNonce' => wp_create_nonce('get-class-permalink-nonce'),
        'getStudentsNonce' => wp_create_nonce('get-students-nonce'),
        'getClassesNonce' => wp_create_nonce('get-classes-nonce'),
        'getStudentNonce' => wp_create_nonce('get-student-nonce'),
        'updateClassNonce' => wp_create_nonce('update-class-nonce'),
        'archiveClassNonce' => wp_create_nonce('archive-class-nonce'),
        'createSessionNonce' => wp_create_nonce('create-session-nonce'),
        'currentUser' => is_user_logged_in() ? $current_user : null,
        'currentUserNameInitials' => is_user_logged_in() ? $current_user_name_initials : null,
        'currentUserHasProfilePicture' => $has_profile_picture
    ));

	wp_enqueue_script('gsg-script');
}

add_action('wp_enqueue_scripts', 'gsg_enqueue');
