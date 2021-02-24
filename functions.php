<?php

define('GSG_DEV_MODE', true);
define('GSG_THEME_URL', get_template_directory_uri());
define('GSG_VENDORS_URL', GSG_THEME_URL . '/vendors');
define('GSG_IMAGES_URL', GSG_THEME_URL . '/images');

include_once 'includes/setup.php';
include_once 'includes/misc.php';

function wpse66094_no_admin_access() {
    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : home_url('/');

    global $current_user;

    $user_roles = $current_user->roles;
    $user_role = array_shift($user_roles);

    if ($user_role != 'administrator') {
        exit(wp_redirect($redirect));
    }
}

add_action('admin_init', 'wpse66094_no_admin_access', 100);

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
