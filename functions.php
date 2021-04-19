<?php

if (is_admin()) {
    include_once 'includes/admin.php';
}

include_once 'includes/constants.php';
include_once 'includes/setup.php';
include_once 'includes/misc.php';
include_once 'includes/helpers.php';
include_once 'includes/actions.php';
include_once 'includes/filters.php';

function gsg_ajax_login_redirect() {
    $current_user = wp_get_current_user();

    if (in_array('administrator', $current_user->roles)) {
        wp_redirect(admin_url());
        exit;
    } else if (in_array('student', $current_user->roles)) {
        wp_redirect(GRADES_PAGE_URL);
        exit;
    } else if (in_array('teacher', $current_user->roles)) {
        wp_redirect(CLASSES_PAGE_URL);
        exit;
    }
}
