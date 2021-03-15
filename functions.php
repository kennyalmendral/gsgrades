<?php

define('GSG_DEV_MODE', true);
define('GSG_THEME_URL', get_template_directory_uri());
define('GSG_VENDORS_URL', GSG_THEME_URL . '/vendors');
define('GSG_IMAGES_URL', GSG_THEME_URL . '/images');

include_once 'includes/setup.php';
include_once 'includes/misc.php';

function wpse66094_no_admin_access() {
    if (is_user_logged_in()) {
        $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : home_url('/');

        global $current_user;

        $user_roles = $current_user->roles;
        $user_role = array_shift($user_roles);

        if ($user_role != 'administrator') {
            exit(wp_redirect($redirect));
        }
    }
}

//add_action('admin_init', 'wpse66094_no_admin_access', 100);

function gsg_login_redirect($redirect_to, $request, $user) {
    if (isset($user->roles) && is_array($user->roles)) {
        if (in_array('administrator', $user->roles)) {
            return $redirect_to;
        } else {
            return home_url();
        }
    } else {
        return $redirect_to;
    }
}

add_filter('login_redirect', 'gsg_login_redirect', 10, 3);

function gsg_login() {
    if (!isset($_POST['gsg_login_nonce_field']) || !wp_verify_nonce($_POST['gsg_login_nonce_field'], 'gsg_login')) {
        wp_send_json_error(array('error_message' => 'Something went wrong...'), 500);
    }

    $errors = array();

    $email = $_POST['email'];
    $password = $_POST['password'];
    $remember = $_POST['remember'];

    if (empty($email)) {
        $errors['email'] = 'The email address field is required.';
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if (empty($password)) {
        $errors['password'] = 'The password field is required.';
    }

    if (!empty($errors)) {
        wp_send_json_error($errors, 400);
    }

    $login_data = array();

    $login_data['user_login'] = $email;
    $login_data['user_password'] = $password;
    $login_data['remember'] = $remember;

    $login = wp_signon($login_data, false);

    if (is_wp_error($login)) {
        wp_send_json_error(array('login_error' => 'Invalid email address and/or password.'), 401);
    }

    wp_send_json(array(
        'success' => true,
        'message' => 'You have been logged in successfully.'
    ));
}

add_action('wp_ajax_gsg_login', 'gsg_login');
add_action('wp_ajax_nopriv_gsg_login', 'gsg_login');
