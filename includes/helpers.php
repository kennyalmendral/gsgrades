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

function gsg_is_classes_page() {
    return is_page(CLASSES_PAGE_ID);
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

function gsg_current_user_has_profile_picture() {
    $current_user = wp_get_current_user();

    return empty(get_user_meta($current_user->ID, 'profile_picture', true)) ? false : true;
}

function gsg_current_user_profile_picture() {
    $current_user = wp_get_current_user();
    $wp_upload_dir = wp_upload_dir();

    return $wp_upload_dir['baseurl'] . '/profile-pictures/' . get_user_meta($current_user->ID, 'profile_picture', true);
}

function gsg_generate_random_string() {
    $string = '0123456789ABCDEFGHILJKLMNOPQRSTUVWXYZ0123456789';

    $random_string = str_repeat($string, 10);
    $random_string = str_shuffle($random_string);

    $n = 10;

    $random_string = substr($random_string, 0, $n);

    return $random_string;
}

function gsg_get_class_sum_total_hours($class_id) {
    global $wpdb;

    $sum_total_hours = $wpdb->get_var("SELECT SUM(total_hours) FROM {$wpdb->prefix}class_sessions WHERE class_id={$class_id}");

    return intval($sum_total_hours);
}

function gsg_get_class_completion_hours($class_id) {
	$completion_hours = intval(get_field('completion_hours', $class_id));

    return $completion_hours;
}

function gsg_update_class_hours($class_id, $sum_total_hours) {
	global $wpdb;

	update_field('completed_hours', $sum_total_hours, $class_id);

	$completion_hours = intval(get_field('completion_hours', $class_id));
	$completed_hours = intval(get_field('completed_hours', $class_id));

	update_field('remaining_hours', $completion_hours - $completed_hours , $class_id);

    $remaining_hours = intval(get_field('remaining_hours'));

    if ($remaining_hours <= 0) {
        gsg_update_class_status($class_id, 'completed');
    }
}

function gsg_update_class_status($class_id, $status) {
    global $wpdb;

    $wpdb->update(
        $wpdb->posts,
        array('post_status' => $status),
        array('ID' => $class_id),
        array('%s'),
        array('%d')
    );
}