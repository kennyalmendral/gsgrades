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

function gsg_is_account_page() {
    return is_page(ACCOUNT_PAGE_ID);
}

function gsg_is_grades_page() {
    return is_page(GRADES_PAGE_ID);
}

function gsg_is_students_page() {
    return is_page(STUDENTS_PAGE_ID);
}
