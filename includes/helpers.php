<?php

function gsg_is_administrator() {
    $current_user = wp_get_current_user();

    return in_array('administrator', $current_user->roles);
}

function gsg_is_teacher() {
    $current_user = wp_get_current_user();

    return in_array('teacher', $current_user->roles);
}

function gsg_is_student() {
    $current_user = wp_get_current_user();

    return in_array('student', $current_user->roles);
}

function gsg_is_login_page() {
    return is_page(LOGIN_PAGE_ID);
}

function gsg_is_register_page() {
    return is_page(REGISTER_PAGE_ID);
}

function gsg_is_forgot_password_page() {
    return is_page(FORGOT_PASSWORD_PAGE_ID);
}

function gsg_is_reset_password_page() {
    return is_page(RESET_PASSWORD_PAGE_ID);
}

function gsg_is_account_page() {
    return is_page(ACCOUNT_PAGE_ID);
}

function gsg_is_records_page() {
    return is_page(RECORDS_PAGE_ID);
}

function gsg_is_grades_page() {
    return is_page(GRADES_PAGE_ID);
}

function gsg_is_students_page() {
    return is_page(STUDENTS_PAGE_ID);
}

function gsg_get_current_page() {
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        $url = 'https://';
    } else {
        $url = 'http://'; 
    }

    $url .= $_SERVER['HTTP_HOST']; 
    $url .= $_SERVER['REQUEST_URI']; 

    return basename($url);
}

function gsg_is_email_exists($email_address) {
    global $wpdb;

    $email_exists = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->prefix" . "users WHERE user_email = '%s' LIMIT 1", $email_address));

    return $email_exists > 0;
}

function gsg_get_initials($string = null) {
    return array_reduce(
        explode(' ', $string),
        function ($initials, $word) {
            return sprintf('%s%s', $initials, substr($word, 0, 1));
        },
        ''
    );
}
