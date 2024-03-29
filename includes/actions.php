<?php

function wpse66094_no_admin_access() {
    if (wp_doing_ajax()) {
        return;
    }

    $current_user = wp_get_current_user();
    $user_roles = $current_user->roles;

    $redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : home_url('/');

    if (!in_array('administrator', $user_roles)) {
        exit(wp_redirect($redirect));
    }
}

add_action('admin_init', 'wpse66094_no_admin_access', 100);

function gsg_login() {
    if (!isset($_POST['login_nonce']) || !wp_verify_nonce($_POST['login_nonce'], 'gsg_login')) {
        wp_send_json_error(array('error_message' => 'Something went wrong...'), 500);
    }

    $errors = array();

    $email_address = $_POST['email_address'];
    $password = $_POST['password'];
    $remember = $_POST['remember'];

    if (empty($email_address)) {
        $errors['email_address'] = 'The email address field is required.';
    } else if (!filter_var($email_address, FILTER_VALIDATE_EMAIL)) {
        $errors['email_address'] = 'Please enter a valid email address.';
    }

    if (empty($password)) {
        $errors['password'] = 'The password field is required.';
    }

    if (!empty($errors)) {
        wp_send_json_error($errors, 400);
    }

    $login_data = array();

    $login_data['user_login'] = $email_address;
    $login_data['user_password'] = $password;
    $login_data['remember'] = $remember;

    $login = wp_signon($login_data, false);

    if (is_wp_error($login)) {
        wp_send_json_error(array('login_error' => 'Invalid email address and/or password.'), 401);
    }

    wp_send_json_success(array('message' => 'You have been logged in successfully.'));
}

add_action('wp_ajax_gsg_login', 'gsg_login');
add_action('wp_ajax_nopriv_gsg_login', 'gsg_login');

function gsg_logout() {
    check_ajax_referer('logout-nonce', 'logout_nonce');

    wp_clear_auth_cookie();
    wp_logout();

    ob_clean();

    wp_send_json_success();
}

add_action('wp_ajax_gsg_logout', 'gsg_logout');
add_action('wp_ajax_nopriv_gsg_logout', 'gsg_logout');

function gsg_register() {
    if (!isset($_POST['register_nonce']) || !wp_verify_nonce($_POST['register_nonce'], 'gsg_register')) {
        wp_send_json_error(array('error_message' => 'Something went wrong...'), 500);
    }

    $errors = array();

    $required_fields = array(
        'first_name',
        'last_name',
        'email_address',
        'contact_number',
        'password',
        'password_confirmation',
    );

    foreach ($_POST as $key => $value) {
        $field_name = ucfirst(str_replace('_', ' ', $key));

        if (in_array($key, $required_fields)) {
            if (empty($value)) {
                $errors[$key] = "$field_name field is required.";
            }
        }

        if (($key == 'first_name') || ($key == 'last_name')) {
            if ($_POST[$key] != '') {
                if (!preg_match("/^([a-zA-Z' ]+)$/", $_POST[$key])) {
                    $errors[$key] = "$field_name field contains invalid characters.";
                }
            }
        }

        if ($key == 'email_address') {
            if ($_POST[$key] != '') {
                if (!filter_var($_POST[$key], FILTER_VALIDATE_EMAIL)) {
                    $errors[$key] = "$field_name field must contain a valid email address.";
                }

                if (gsg_is_email_exists($_POST[$key])) {
                    $errors[$key] = "$field_name already exists.";
                }
            }
        }

        if ($key == 'contact_number') {
            if ($_POST[$key] != '') {
                if (!preg_match("/^((\+[0-9]{2})|0)[.\- ]?9[0-9]{2}[.\- ]?[0-9]{3}[.\- ]?[0-9]{4}$/", $_POST[$key])) {
                    $errors[$key] = "$field_name field is invalid.";
                }
            }
        }

        if ($key == 'password') {
            if ($_POST[$key] != '') {
                if ($_POST[$key] != $_POST['password_confirmation']) {
                    $errors[$key] = "Passwords do not match.";
                }

                if (!preg_match('/^(?=.*\d)(?=.*[@#\-_$%^&+=§!\?])(?=.*[a-z])(?=.*[A-Z])[0-9A-Za-z@#\-_$%^&+=§!\?]{8,20}$/', $_POST[$key])) {
                    $errors[$key] = "Password does not meet the requirements.";
                }
            }
        }
    }

    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $name = $first_name . ' ' . $last_name;
    $email_address = $_POST['email_address'];
    $contact_number = $_POST['contact_number'];
    $password = $_POST['password'];
    $password_confirmation = $_POST['password_confirmation'];

    if (!empty($errors)) {
        wp_send_json_error($errors, 400);
    }

    $user_id = wp_insert_user(array(
        'user_login' => strtolower(explode('@', $email_address)[0]),
        'user_pass' => $password,
        'user_email' => $email_address,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'display_name' => $name,
        'role' => 'student'
    ));

    update_user_meta($user_id, 'contact_number', $contact_number);

    $subject = 'Account Registration';

    $body = "<p>Hello $first_name, here's your login credentials:</p><p><strong>Email address:</strong> $email_address<br><strong>Password:</strong> $password</p>";
    $body .= '<p>You may login here: ' . LOGIN_PAGE_URL . '</p>';

    $headers = array();
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = 'From: Gottes Segen Grades <info@grades.gottes-segen.com>';

    wp_mail($email_address, $subject, $body, $headers);

    wp_send_json_success();
}

add_action('wp_ajax_gsg_register', 'gsg_register');
add_action('wp_ajax_nopriv_gsg_register', 'gsg_register');

function gsg_forgot_password() {
    if (!isset($_POST['forgot_password_nonce']) || !wp_verify_nonce($_POST['forgot_password_nonce'], 'gsg_forgot_password')) {
        wp_send_json_error(array('error_message' => 'Something went wrong...'), 500);
    }

    $errors = array();

    $email_address = $_POST['email_address'];

    if (empty($email_address)) {
        $errors['email_address'] = 'The email address field is required.';
    } else if (!filter_var($email_address, FILTER_VALIDATE_EMAIL)) {
        $errors['email_address'] = 'Please enter a valid email address.';
    } else if (!gsg_is_email_exists($email_address)) {
        $errors['email_address'] = 'Email address not found.';
    }

    if (!empty($errors)) {
        wp_send_json_error($errors, 400);
    }

    $user = get_user_by('email', $email_address);

    $first_name = $user->first_name;
    $user_login = $user->user_login;

    $password_reset_key = get_password_reset_key($user);

    $reset_password_link = RESET_PASSWORD_PAGE_URL . "?key=$password_reset_key&login=$user_login";

    $subject = 'Forgot Password';

    $body = "<p>Hello $first_name,</p>";
    $body .= "<p>Click the following link to set a new password for your account: $reset_password_link</p>";

    $headers = array();
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = 'From: Gottes Segen Grades <info@grades.gottes-segen.com>';

    wp_mail($email_address, $subject, $body, $headers);

    wp_send_json_success();
}

add_action('wp_ajax_gsg_forgot_password', 'gsg_forgot_password');
add_action('wp_ajax_nopriv_gsg_forgot_password', 'gsg_forgot_password');

function gsg_reset_password() {
    if (!isset($_POST['reset_password_nonce']) || !wp_verify_nonce($_POST['reset_password_nonce'], 'gsg_reset_password')) {
        wp_send_json_error(array('error_message' => 'Something went wrong...'), 500);
    }

    $errors = array();

    $user_login = $_POST['user_login'];
    $reset_password_key = $_POST['reset_password_key'];

    $user = check_password_reset_key($reset_password_key, $user_login);

    if (is_wp_error($user)) {
        wp_send_json_error(array('reset_password_error' => 'Something went wrong...'), 500);
    }

    $password = $_POST['password'];
    $password_confirmation = $_POST['password_confirmation'];

    if (empty($password)) {
        $errors['password'] = 'The new password field is required.';
    } else {
        if (!preg_match('/^(?=.*\d)(?=.*[@#\-_$%^&+=§!\?])(?=.*[a-z])(?=.*[A-Z])[0-9A-Za-z@#\-_$%^&+=§!\?]{8,20}$/', $password)) {
            $errors['password'] = "Password does not meet the requirements.";
        }
    }

    if (empty($password_confirmation)) {
        $errors['password_confirmation'] = 'The new password confirmation field is required.';
    }

    if ($password != $password_confirmation) {
        $errors['password'] = 'Passwords do not match.';
    }

    if (!empty($errors)) {
        wp_send_json_error($errors, 400);
    }

    wp_set_password($password, $user->ID);

    wp_send_json_success();
}

add_action('wp_ajax_gsg_reset_password', 'gsg_reset_password');
add_action('wp_ajax_nopriv_gsg_reset_password', 'gsg_reset_password');

function gsg_save_account_info() {
    if (!isset($_POST['save_account_info_nonce']) || !wp_verify_nonce($_POST['save_account_info_nonce'], 'gsg_save_account_info')) {
        wp_send_json_error(array('error_message' => 'Something went wrong...'), 500);
    }

    global $current_user;

    $errors = array();

    $required_fields = array(
        'first_name',
        'last_name',
        'email_address',
        'contact_number'
    );

    foreach ($_POST as $key => $value) {
        $field_name = ucfirst(str_replace('_', ' ', $key));

        if (in_array($key, $required_fields)) {
            if (empty($value)) {
                $errors[$key] = "$field_name field is required.";
            }
        }

        if (($key == 'first_name') || ($key == 'last_name')) {
            if ($_POST[$key] != '') {
                if (!preg_match("/^([a-zA-Z' ]+)$/", $_POST[$key])) {
                    $errors[$key] = "$field_name field contains invalid characters.";
                }
            }
        }

        if ($key == 'email_address') {
            if ($_POST[$key] != '') {
                if (!filter_var($_POST[$key], FILTER_VALIDATE_EMAIL)) {
                    $errors[$key] = "$field_name field must contain a valid email address.";
                }

                if (gsg_is_email_exists($_POST[$key]) && ($current_user->user_email != $_POST[$key])) {
                    $errors[$key] = "$field_name already exists.";
                }
            }
        }

        if ($key == 'contact_number') {
            if ($_POST[$key] != '') {
                if (!preg_match("/^((\+[0-9]{2})|0)[.\- ]?9[0-9]{2}[.\- ]?[0-9]{3}[.\- ]?[0-9]{4}$/", $_POST[$key])) {
                    $errors[$key] = "$field_name field is invalid.";
                }
            }
        }

        if ($key == 'password') {
            if ($_POST[$key] != '') {
                if ($_POST[$key] != $_POST['password_confirmation']) {
                    $errors[$key] = "Passwords do not match.";
                }

                if (!preg_match('/^(?=.*\d)(?=.*[@#\-_$%^&+=§!\?])(?=.*[a-z])(?=.*[A-Z])[0-9A-Za-z@#\-_$%^&+=§!\?]{8,20}$/', $_POST[$key])) {
                    $errors[$key] = "Password does not meet the requirements.";
                }
            }
        }
    }

    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $name = $first_name . ' ' . $last_name;
    $email_address = $_POST['email_address'];
    $contact_number = $_POST['contact_number'];
    $password = $_POST['password'];
    $password_confirmation = $_POST['password_confirmation'];

    if (!empty($errors)) {
        wp_send_json_error($errors, 400);
    }

    wp_update_user(array(
        'ID' => $current_user->ID,
        'first_name' => $first_name,
        'last_name' => $last_name,
        'display_name' => $name
    ));

    if (!empty($password)) {
        wp_update_user(array(
            'ID' => $current_user->ID,
            'user_pass' => $password
        ));
    }

    if ($current_user->user_email != $email_address) {
        wp_update_user(array(
            'ID' => $current_user->ID,
            'user_email' => $email_address
        ));
    }

    $current_contact_number = get_user_meta($current_user->ID, 'contact_number', true);

    if ($current_contact_number != $contact_number) {
        update_user_meta($current_user->ID, 'contact_number', $contact_number);
    }

    wp_send_json_success();
}

add_action('wp_ajax_gsg_save_account_info', 'gsg_save_account_info');
add_action('wp_ajax_nopriv_gsg_save_account_info', 'gsg_save_account_info');

function gsg_upload_update_profile_picture() {
    if (!isset($_POST['upload_update_profile_picture_nonce']) || !wp_verify_nonce($_POST['upload_update_profile_picture_nonce'], 'gsg_upload_update_profile_picture')) {
        wp_send_json_error(array('error_message' => 'Something went wrong...'), 500);
    }

    global $current_user;

    $errors = array();

    $profile_picture = $_FILES['profile_picture'];
    $filetype = mime_content_type($profile_picture['tmp_name']);

    if (is_null($profile_picture)) {
        $errors['profile_picture'] = 'Please select a file.';
    } else if (floatval($profile_picture['size'] / 1024) > 3000) {
        $errors['profile_picture'] = 'The file size must be less than 3MB.';
    } else {
        $allowed_filetypes = array('image/jpg', 'image/jpeg', 'image/png');

        if (!in_array($filetype, $allowed_filetypes)) {
            $errors['profile_picture'] = 'The file type must be an image and must be in JPG or PNG format only.';
        }
    }

    if (!empty($errors)) {
        wp_send_json_error($errors, 400);
    }

    $wp_upload_dir = wp_upload_dir();

    $file = $wp_upload_dir['basedir'] . '/profile-pictures/' . $current_user->user_login . '.' . pathinfo($profile_picture['name'], PATHINFO_EXTENSION);

    if (!move_uploaded_file($profile_picture['tmp_name'], $file)) {
        wp_send_json_error(array('upload_update_profile_picture_error' => 'Upload failed.'), 500);
    }

    update_user_meta($current_user->ID, 'profile_picture', basename($file));

    wp_send_json_success();
}

add_action('wp_ajax_gsg_upload_update_profile_picture', 'gsg_upload_update_profile_picture');
add_action('wp_ajax_nopriv_gsg_upload_update_profile_picture', 'gsg_upload_update_profile_picture');

function gsg_remove_profile_picture() {
    if (!isset($_POST['remove_profile_picture_nonce']) || !wp_verify_nonce($_POST['remove_profile_picture_nonce'], 'gsg_remove_profile_picture')) {
        wp_send_json_error(array('error_message' => 'Something went wrong...'), 500);
    }

    global $current_user;

    $wp_upload_dir = wp_upload_dir();

    $file = $wp_upload_dir['basedir'] . '/profile-pictures/' . get_user_meta($current_user->ID, 'profile_picture' , true);

    if (!unlink($file)) {
        wp_send_json_error(array('remove_profile_picture_error' => 'Upload failed.'), 500);
    }

    update_user_meta($current_user->ID, 'profile_picture', null);

    wp_send_json_success();
}

add_action('wp_ajax_gsg_remove_profile_picture', 'gsg_remove_profile_picture');
add_action('wp_ajax_nopriv_gsg_remove_profile_picture', 'gsg_remove_profile_picture');

function gsg_get_students() {
    check_ajax_referer('get-students-nonce', 'get_students_nonce');

    global $wpdb;

    $draw = intval($_GET['draw']);
    $offset = intval($_GET['start']);
    $limit = intval($_GET['length']);
    $search = trim($_GET['search']['value']);

    $order_column_index = intval($_GET['order'][0]['column']);
    $order_column = '';
    $order_column_meta_key = '';

    switch ($order_column_index) {
        case 0:
            $order_column = 'ID';
            break;
        case 1:
            $order_column = 'display_name';
            break;
        case 2:
            $order_column = 'user_email';
            break;
        case 3:
            $order_column = 'meta_value';
            $order_column_meta_key = 'contact_number';
            break;
        case 4:
            $order_column = 'user_registered';
            break;
        default:
            break;
    }

    $order_direction = $_GET['order'][0]['dir'];

    $users = array();

    $total_user_query_args = array('role' => 'student');
    $total_user_query = new WP_User_Query($total_user_query_args);

    $total_users = $total_user_query->get_total();
    $total_filtered_users = $total_users;

    $user_query = new WP_User_Query(array(
        'role' => 'student',
        'number' => $limit,
        'offset' => $offset,
        'orderby' => empty($order_column_index) ? 'display_name' : $order_column,
        'order' => empty($order_direction) ? 'ASC' : $order_direction,
        'meta_key' => $order_column_meta_key
    ));

    if (!empty($search)) {
        $total_user_query_search = new WP_User_Query(array(
            'role' => 'student',
            'search' => '*' . esc_attr($search) . '*'
        ));

        $total_user_query_meta_key = new WP_User_Query(array(
            'role' => 'student',
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'contact_number',
                    'value' => $search,
                    'compare' => 'LIKE'
                )
            )
        ));

        $total_user_query = new WP_User_Query();
        $total_user_query->results = array_unique(array_merge($total_user_query_search->results, $total_user_query_meta_key->results), SORT_REGULAR);
        $total_filtered_users = count($total_user_query->results);

        $user_query_search = new WP_User_Query(array(
            'role' => 'student',
            'number' => $limit,
            'offset' => $offset,
            'orderby' => empty($order_column_index) ? 'display_name' : $order_column,
            'order' => empty($order_direction) ? 'ASC' : $order_direction,
            'meta_key' => $order_column_meta_key,
            'search' => '*' . esc_attr($search) . '*'
        ));

        $user_query_meta_key = new WP_User_Query(array(
            'role' => 'student',
            'number' => $limit,
            'offset' => $offset,
            'orderby' => empty($order_column_index) ? 'display_name' : $order_column,
            'order' => empty($order_direction) ? 'ASC' : $order_direction,
            'meta_key' => $order_column_meta_key,
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'contact_number',
                    'value' => $search,
                    'compare' => 'LIKE'
                )
            )
        ));

        $user_query = new WP_User_Query();
        $user_query->results = array_unique(array_merge($user_query_search->results, $user_query_meta_key->results), SORT_REGULAR);
    }

    foreach ($user_query->results as $user) {
        $users[] = array(
            $user->ID,
            $user->display_name,
            $user->user_email,
            get_user_meta($user->ID, 'contact_number', true),
            date('F d, Y', strtotime($user->user_registered))
        );
    }

    wp_send_json(array(
        'draw' => $draw,
        'recordsTotal' => $total_users,
        'recordsFiltered' => $total_filtered_users,
        'data' => $users
    ));
}

add_action('wp_ajax_gsg_get_students', 'gsg_get_students');
add_action('wp_ajax_nopriv_gsg_get_students', 'gsg_get_students');

function gsg_get_student() {
    check_ajax_referer('get-student-nonce', 'get_student_nonce');

    $user = get_user_by('ID', intval($_GET['student_id']));

    if (!$user) {
        wp_send_json_error('User not found.', 404);
    }

    $userdata = array(
        'ID' => $user->ID,
        'display_name' => $user->display_name,
        'user_initials' => gsg_get_initials($user->display_name),
        'user_email' => $user->user_email,
        'contact_number' => get_user_meta($user->ID, 'contact_number', true),
        'profile_picture' => !empty(get_user_meta($user->ID, 'profile_picture', true)) ? wp_upload_dir()['baseurl'] . '/profile-pictures/' . get_user_meta($user->ID, 'profile_picture', true) : null,
        'user_registered' => $user->user_registered
    );

    wp_send_json_success($userdata);
}

add_action('wp_ajax_gsg_get_student', 'gsg_get_student');
add_action('wp_ajax_nopriv_gsg_get_student', 'gsg_get_student');

function gsg_get_classes() {
    check_ajax_referer('get-classes-nonce', 'get_classes_nonce');

    global $wpdb, $current_user;

    $draw = intval($_GET['draw']);
    $offset = intval($_GET['start']);
    $limit = intval($_GET['length']);
    $search = trim($_GET['search']['value']);
    $status = trim($_GET['status']);

    $order_column_index = intval($_GET['order'][0]['column']);
    $order_column = '';
    $order_column_meta_key = '';

    switch ($order_column_index) {
        case 0:
            $order_column = 'ID';
            break;
        case 1:
            $order_column = 'post_title';
            break;
        case 2:
            $order_column = 'meta_value';
            $order_column_meta_key = 'level';
            break;
        case 3:
            $order_column = 'meta_value';
            $order_column_meta_key = 'completion_hours';
            break;
        case 4:
            $order_column = 'meta_value';
            $order_column_meta_key = 'completed_hours';
            break;
        case 5:
            $order_column = 'meta_value';
            $order_column_meta_key = 'remaining_hours';
            break;
        case 6:
            $order_column = 'meta_value';
            $order_column_meta_key = 'duration';
            break;
        case 7:
            $order_column = 'post_status';
            break;
        default:
            break;
    }

    $order_direction = $_GET['order'][0]['dir'];

    $classes = array();

    $total_class_query = new WP_Query(array(
        'post_type' => 'class',
        'post_status' => empty($status) ? array('ongoing', 'completed') : array($status),
        'posts_per_page' => -1,
        'author' => $current_user->ID
    ));

    $total_classes = $total_class_query->found_posts;
    $total_filtered_classes = $total_classes;

    $class_query = new WP_Query(array(
        'post_type' => 'class',
        'post_status' => empty($status) ? array('ongoing', 'completed') : array($status),
        'posts_per_page' => $limit,
        'author' => $current_user->ID,
        'offset' => $offset,
        'orderby' => empty($order_column_index) ? 'post_date' : $order_column,
        'order' => empty($order_direction) ? 'DESC' : $order_direction,
        'meta_key' => $order_column_meta_key
    ));

    if (!empty($search)) {
        $total_class_query_search = new WP_Query(array(
            'post_type' => 'class',
            'post_status' => empty($status) ? array('ongoing', 'completed') : array($status),
            'posts_per_page' => -1,
            'author' => $current_user->ID,
            's' => esc_attr($search)
        ));

        $total_class_query_meta_key = new WP_Query(array(
            'post_type' => 'class',
            'post_status' => empty($status) ? array('ongoing', 'completed') : array($status),
            'posts_per_page' => -1,
            'author' => $current_user->ID,
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'completion_hours',
                    'value' => $search,
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'completed_hours',
                    'value' => $search,
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'remaining_hours',
                    'value' => $search,
                    'compare' => 'LIKE'
                ),
            )
        ));

        $total_class_query = new WP_Query();
        $total_class_query->posts = array_unique(array_merge($total_class_query_search->posts, $total_class_query_meta_key->posts), SORT_REGULAR);
        $total_filtered_classes = count($total_class_query->posts);

        $class_query_search = new WP_Query(array(
            'post_type' => 'class',
            'post_status' => empty($status) ? array('ongoing', 'completed') : array($status),
            'posts_per_page' => $limit,
            'author' => $current_user->ID,
            'offset' => $offset,
            'orderby' => empty($order_column_index) ? 'post_date' : $order_column,
            'order' => empty($order_direction) ? 'DESC' : $order_direction,
            'meta_key' => $order_column_meta_key,
            's' => esc_attr($search)
        ));

        $class_query_meta_key = new WP_Query(array(
            'post_type' => 'class',
            'post_status' => empty($status) ? array('ongoing', 'completed') : array($status),
            'posts_per_page' => $limit,
            'author' => $current_user->ID,
            'offset' => $offset,
            'orderby' => empty($order_column_index) ? 'post_date' : $order_column,
            'order' => empty($order_direction) ? 'DESC' : $order_direction,
            'meta_key' => $order_column_meta_key,
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key' => 'completion_hours',
                    'value' => $search,
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'completed_hours',
                    'value' => $search,
                    'compare' => 'LIKE'
                ),
                array(
                    'key' => 'remaining_hours',
                    'value' => $search,
                    'compare' => 'LIKE'
                ),
            )
        ));

        $class_query = new WP_Query();
        $class_query->posts = array_unique(array_merge($class_query_search->posts, $class_query_meta_key->posts), SORT_REGULAR);

        if (!empty($class_query->posts)) {
            foreach ($class_query->posts as $post) {
                $post_status = get_post_status($post->ID);

                if ($post_status == 'ongoing') {
                    $post_status_badge_bg = 'secondary';
                } else if ($post_status == 'completed') {
                    $post_status_badge_bg = 'success';
                }

                $classes[] = array(
                    $post->ID,
                    $post->post_title,
                    get_field('level', $post->ID),
                    get_field('duration', $post->ID) . ' days',
                    '<span class="badge bg-' . $post_status_badge_bg . '">' . ucfirst($post_status) . '</span>',
                    get_the_date('M d, Y', $post->ID),
                    get_the_modified_date('M d, Y', $post->ID)
                );
            }
        }
    } else {
        while ($class_query->have_posts()) {
            $class_query->the_post();

            $post_status = get_post_status();

            if ($post_status == 'ongoing') {
                $post_status_badge_bg = 'secondary';
            } else if ($post_status == 'completed') {
                $post_status_badge_bg = 'success';
            }

            $classes[] = array(
                get_the_ID(),
                get_the_title(),
                get_field('level'),
                get_field('duration', $post->ID) . ' days',
                '<span class="badge bg-' . $post_status_badge_bg . '">' . ucfirst($post_status) . '</span>',
                get_the_date('M d, Y'),
                get_the_modified_date('M d, Y')
            );
        }

        wp_reset_postdata();
    }

    wp_send_json(array(
        'draw' => $draw,
        'recordsTotal' => $total_classes,
        'recordsFiltered' => $total_filtered_classes,
        'data' => $classes
    ));
}

add_action('wp_ajax_gsg_get_classes', 'gsg_get_classes');
add_action('wp_ajax_nopriv_gsg_get_classes', 'gsg_get_classes');

function gsg_create_class() {
    if (!isset($_POST['create_class_nonce']) || !wp_verify_nonce($_POST['create_class_nonce'], 'gsg_create_class')) {
        wp_send_json_error(array('error_message' => 'Something went wrong...'), 500);
    }

    global $current_user;

    $errors = array();

    $level = trim($_POST['level']);
    $passing_grade = intval($_POST['passing_grade']);
    $completion_hours = intval($_POST['completion_hours']);
    $duration = intval($_POST['duration']);

    if (empty($level)) {
        $errors['level'] = 'The level field is required.';
    } else if (preg_match('/[\'^£$%&*()}{@#~?><>,|=_+¬-]/', $level)) {
        $errors['level'] = 'The level field must contain alphanumeric characters only.';
    }

    if (empty($passing_grade)) {
        $errors['passing_grade'] = 'The passing grade field is required.';
    } else if ($passing_grade < 1) {
        $errors['passing_grade'] = 'The passing grade field must be greater than 0.';
    }

    if (empty($completion_hours)) {
        $errors['completion_hours'] = 'The completion hours field is required.';
    } else if (!is_numeric($completion_hours)) {
        $errors['completion_hours'] = 'The completion hours field must be numeric.';
    } else if ($completion_hours < 1) {
        $errors['completion_hours'] = 'The completion hours field must be greater than 0.';
    }

    if (empty($duration)) {
        $errors['duration'] = 'The duration field is required.';
    } else if (!is_numeric($duration)) {
        $errors['duration'] = 'The duration must be numeric.';
    } else if ($duration < 1) {
        $errors['duration'] = 'The duration must be greater than 0.';
    }

    if (!empty($errors)) {
        wp_send_json_error($errors, 400);
    }

    $random_string = gsg_generate_random_string();

    if (!is_null(get_page_by_title($random_string, OBJECT, 'class'))) {
        $random_string = gsg_generate_random_string();
    }

    $post_id = wp_insert_post(array(
        'post_type' => 'class',
        'post_status' => 'ongoing',
        'post_title' => $random_string,
        'post_author' => $current_user->ID
    ));

    update_field('level', strtoupper($level), $post_id);
    update_field('passing_grade', $passing_grade, $post_id);
    update_field('completion_hours', $completion_hours, $post_id);
    update_field('completed_hours', 0, $post_id);
    update_field('remaining_hours', $completion_hours, $post_id);
    update_field('duration', $duration, $post_id);

    wp_send_json_success(array(
        'message' => 'Class has been created.',
        'class_permalink' => get_permalink($post_id)
    ), 201);
}

add_action('wp_ajax_gsg_create_class', 'gsg_create_class');
add_action('wp_ajax_nopriv_gsg_create_class', 'gsg_create_class');

function gsg_update_class() {
    check_ajax_referer('update-class-nonce', 'update_class_nonce');

    $errors = array();

    $class_id = intval($_POST['class_id']);
    $level = $_POST['level'];
    $passing_grade = intval($_POST['passing_grade']);
    $completion_hours = intval($_POST['completion_hours']);
    $duration = intval($_POST['duration']);

    if (empty($class_id)) {
        wp_send_json_error(array('update_class_error' => 'The class ID field is required.'), 204);
    }

    if (empty($level)) {
        $errors['level'] = 'The level field is required.';
    }

    if (empty($passing_grade)) {
        $errors['passing_grade'] = 'The passing grade field is required.';
    } else if ($passing_grade < 1) {
        $errors['passing_grade'] = 'The passing grade field must be greater than 0.';
    }

    if (empty($completion_hours)) {
        $errors['completion_hours'] = 'The completion hours field is required.';
    } else if ($completion_hours < 1) {
        $errors['completion_hours'] = 'The completion hours field must be greater than 0.';
    }

    if (empty($duration)) {
        $errors['duration'] = 'The duration field is required.';
    } else if ($duration < 1) {
        $errors['duration'] = 'The duration field must be greater than 0.';
    }

    $sum_total_hours = gsg_get_class_sum_total_hours($class_id);

    if ($completion_hours < $sum_total_hours) {
        $errors['completion_hours'] = 'The completion hours must be greater than or equal to the completed hours.';
    }

    if (!empty($errors)) {
        wp_send_json_error($errors, 400);
    }

    update_field('level', $level, $class_id);
    update_field('passing_grade', $passing_grade, $class_id);
    update_field('completion_hours', $completion_hours, $class_id);
    update_field('duration', $duration, $class_id);
    
    if ($sum_total_hours > 0) {
        gsg_update_class_hours($class_id, $sum_total_hours);
    } else {
        update_field('remaining_hours', $completion_hours, $class_id);
    }

    wp_send_json_success("Details has been updated successfully.");
}

add_action('wp_ajax_gsg_update_class', 'gsg_update_class');
add_action('wp_ajax_nopriv_gsg_update_class', 'gsg_update_class');

function gsg_archive_class() {
    check_ajax_referer('archive-class-nonce', 'archive_class_nonce');

    $post_id = intval($_POST['class_id']);

    if (empty($post_id)) {
        wp_send_json_error(array('error_message' => 'Please select a class to archive.'), 500);
    }

    if (!wp_update_post(array(
        'ID' => $post_id,
        'post_status' => 'archived'
    ))) {
        wp_send_json_error(array('error_message' => 'Unable to archive class.'), 500);
    }

    wp_send_json_success();
}

add_action('wp_ajax_gsg_archive_class', 'gsg_archive_class');
add_action('wp_ajax_nopriv_gsg_archive_class', 'gsg_archive_class');

function gsg_get_class_permalink() {
    check_ajax_referer('get-class-permalink-nonce', 'get_class_permalink_nonce');

    $class_id = intval($_POST['class_id']);

    if (empty($class_id)) {
        wp_send_json_error(array('error_message' => 'Class ID is required.'), 500);
    }

    $permalink = get_permalink($class_id);

    wp_send_json_success($permalink);
}

add_action('wp_ajax_gsg_get_class_permalink', 'gsg_get_class_permalink');
add_action('wp_ajax_nopriv_gsg_get_class_permalink', 'gsg_get_class_permalink');

function gsg_create_session() {
    check_ajax_referer('create-session-nonce', 'create_session_nonce');

    $errors = array();

    $class_id = intval($_POST['class_id']);
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    if (empty($class_id)) {
        wp_send_json_error(array('create_session_error' => 'The class ID field is required.'), 204);
    }

    if (empty($start_time)) {
        $errors['start_time'] = 'The start time field is required.';
    }

    if (empty($end_time)) {
        $errors['end_time'] = 'The end time field is required.';
    }
    
    $total_hours = round(abs(strtotime($start_time) - strtotime($end_time)) / 3600, 2);

    if ($total_hours == 0) {
        wp_send_json_error(array('create_session_error' => 'The end time must be greater than the start time. Please try again.'), 409);
    }   

    $sum_total_hours = gsg_get_class_sum_total_hours($class_id);
    $completion_hours = gsg_get_class_completion_hours($class_id);

    if ($sum_total_hours > 0) {
        $new_sum_total_hours = intval($sum_total_hours + $total_hours);

        if (($completion_hours < $new_sum_total_hours) && ($completion_hours != $new_sum_total_hours)) {
            wp_send_json_error(array('create_session_error' => 'Unable to create session because the current class completion hours must be greater than or equal to the total session hour(s). Please try again.'), 409);
        }
    } else {
        if ($completion_hours < $total_hours) {
            wp_send_json_error(array('create_session_error' => 'Unable to create session because the current class completion hours must be greater than or equal to the total session hour(s). Please try again.'), 409);
        }
    }
    
    if (!empty($errors)) {
        wp_send_json_error($errors, 400);
    }

    global $wpdb;

    $wpdb->insert($wpdb->prefix . 'class_sessions', array(
        'class_id' => $class_id,
        'start_time' => $start_time,
        'end_time' => $end_time,
        'total_hours' => $total_hours,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ), array('%d', '%s', '%s', '%d', '%s', '%s'));

    $sum_total_hours = gsg_get_class_sum_total_hours($class_id);

    gsg_update_class_hours($class_id, $sum_total_hours);

    wp_send_json_success(array('message' => 'Session has been created.'), 201);
}

add_action('wp_ajax_gsg_create_session', 'gsg_create_session');
add_action('wp_ajax_nopriv_gsg_create_session', 'gsg_create_session');

function gsg_update_session() {
    check_ajax_referer('update-session-nonce', 'update_session_nonce');

    $errors = array();

    $session_id = intval($_POST['session_id']);
    $class_id = intval($_POST['class_id']);
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    if (empty($session_id)) {
        wp_send_json_error(array('update_session_error' => 'The session ID field is required.'), 204);
    }

    if (empty($class_id)) {
        wp_send_json_error(array('update_session_error' => 'The class ID field is required.'), 204);
    }

    if (empty($start_time)) {
        $errors['start_time'] = 'The start time field is required.';
    }

    if (empty($end_time)) {
        $errors['end_time'] = 'The end time field is required.';
    }

    $total_hours = round(abs(strtotime($start_time) - strtotime($end_time)) / 3600, 2);

    if ($total_hours == 0) {
        wp_send_json_error(array('update_session_error' => 'The end time must be greater than the start time. Please try again.'), 409);
    }

    $sum_total_hours = gsg_get_class_sum_total_hours($class_id);
    $completion_hours = gsg_get_class_completion_hours($class_id);

    if ($sum_total_hours > 0) {
        $new_sum_total_hours = intval($sum_total_hours + $total_hours);

        if (($completion_hours < $new_sum_total_hours) && ($completion_hours != $new_sum_total_hours)) {
            wp_send_json_error(array('update_session_error' => 'Unable to update session because the current class completion hours must be greater than or equal to the total session hour(s). Please try again.'), 409);
        }
    } else {
        if ($completion_hours < $total_hours) {
            wp_send_json_error(array('update_session_error' => 'Unable to update session because the current class completion hours must be greater than or equal to the total session hour(s). Please try again.'), 409);
        }
    }
    
    if (!empty($errors)) {
        wp_send_json_error($errors, 400);
    }

    global $wpdb;

    $wpdb->update(
        $wpdb->prefix . 'class_sessions',
        array(
            'class_id' => $class_id,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'total_hours' => $total_hours,
            'updated_at' => date('Y-m-d H:i:s')
        ),
        array('id' => $session_id),
        array('%d', '%s', '%s', '%d', '%s'),
        array('%d')
    );

    $sum_total_hours = gsg_get_class_sum_total_hours($class_id);

    gsg_update_class_hours($class_id, $sum_total_hours);

    wp_send_json_success(array('message' => 'Session has been updated.'));
}

add_action('wp_ajax_gsg_update_session', 'gsg_update_session');
add_action('wp_ajax_nopriv_gsg_update_session', 'gsg_update_session');

function gsg_delete_session() {
    check_ajax_referer('delete-session-nonce', 'delete_session_nonce');

    $session_id = intval($_POST['session_id']);
    $class_id = intval($_POST['class_id']);

    if (empty($session_id)) {
        wp_send_json_error(array('delete_session_error' => 'The session ID field is required.'), 204);
    }

    if (empty($class_id)) {
        wp_send_json_error(array('delete_session_error' => 'The class ID field is required.'), 204);
    }

    global $wpdb;

    $wpdb->delete($wpdb->prefix . 'class_sessions', array('ID' => $session_id), array('%d'));

    $sum_total_hours = gsg_get_class_sum_total_hours($class_id);

    gsg_update_class_hours($class_id, $sum_total_hours);

    wp_send_json_success();
}

add_action('wp_ajax_gsg_delete_session', 'gsg_delete_session');
add_action('wp_ajax_nopriv_gsg_delete_session', 'gsg_delete_session');

function gsg_get_class_students() {
    check_ajax_referer('get-class-students-nonce', 'get_class_students_nonce');

    global $wpdb;

    $draw = intval($_GET['draw']);
    $offset = intval($_GET['start']);
    $limit = intval($_GET['length']);
    $search = trim($_GET['search']['value']);
    $class_id = intval($_GET['class_id']);
    $student = intval($_GET['student']);
    $status = $_GET['status'];

    $order_column_index = intval($_GET['order'][0]['column']);
    $order_column = '';
    $order_column_meta_key = '';

    switch ($order_column_index) {
        case 0:
            $order_column = 'ID';
            break;
        case 1:
            $order_column = 'meta_value';
            $order_column_meta_key = 'student';
            break;
        case 2:
            $order_column = 'meta_value';
            $order_column_meta_key = 'days_present';
            break;
        case 3:
            $order_column = 'meta_value';
            $order_column_meta_key = 'status';
            break;
        case 4:
            $order_column = 'post_date';
            break;
        case 5:
            $order_column = 'post_modified';
            break;
        default:
            break;
    }

    $order_direction = $_GET['order'][0]['dir'];

    $students = array();

    $total_student_query = new WP_Query(array(
        'post_type' => 'student',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'class',
                'value' => $class_id
            )
        )
    ));

    $total_students = $total_student_query->found_posts;
    $total_filtered_students = $total_students;

    $student_query = new WP_Query(array(
        'post_type' => 'student',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'offset' => $offset,
        'orderby' => empty($order_column_index) ? 'display_name' : $order_column,
        'order' => empty($order_direction) ? 'ASC' : $order_direction,
        'meta_key' => $order_column_meta_key,
        'meta_query' => array(
            array(
                'key' => 'class',
                'value' => $class_id
            )
        )
    ));

    if (!empty($search) || !empty($student) || !empty($status)) {
        $total_student_query_search = new WP_Query(array(
            'post_type' => 'student',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            's' => esc_attr($search),
            'meta_query' => array(
                array(
                    'key' => 'class',
                    'value' => $class_id
                )
            )
        ));

        if (!empty($student) && empty($status)) {
            $meta_query = array(
                array(
                    'key' => 'student',
                    'value' => $student
                ),
            );
        } else if (empty($student) && !empty($status)) {
            $meta_query = array(
                array(
                    'key' => 'status',
                    'value' => $status
                ),
            );
        } else if (!empty($student) && !empty($status)) {
            $meta_query = array(
                'relation' => 'AND',
                array(
                    'key' => 'student',
                    'value' => $student
                ),
                array(
                    'key' => 'status',
                    'value' => $status
                ),
            );
        }

        $meta_query[] = array(
            'key' => 'class',
            'value' => $class_id
        );

        $total_student_query_meta_key = new WP_Query(array(
            'post_type' => 'student',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => $meta_query
        ));

        $total_student_query = new WP_Query();

        if (!empty($search)) {
            $total_student_query->posts = array_unique($total_student_query_search->posts, SORT_REGULAR);
        } else {
            $total_student_query->posts = array_unique($total_student_query_meta_key->posts, SORT_REGULAR);
        }

        $total_filtered_students = count($total_student_query->posts);

        $student_query_search = new WP_Query(array(
            'post_type' => 'student',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'offset' => $offset,
            'orderby' => empty($order_column_index) ? 'display_name' : $order_column,
            'order' => empty($order_direction) ? 'ASC' : $order_direction,
            'meta_key' => $order_column_meta_key,
            's' => esc_attr($search),
            'meta_query' => array(
                array(
                    'key' => 'class',
                    'value' => $class_id
                )
            )
        ));

        $student_query_meta_key = new WP_Query(array(
            'post_type' => 'student',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'offset' => $offset,
            'orderby' => empty($order_column_index) ? 'display_name' : $order_column,
            'order' => empty($order_direction) ? 'ASC' : $order_direction,
            'meta_key' => $order_column_meta_key,
            'meta_query' => $meta_query
        ));

        $student_query = new WP_Query();

        if (!empty($search)) {
            $student_query->posts = array_unique($student_query_search->posts, SORT_REGULAR);
        } else {
            $student_query->posts = array_unique($student_query_meta_key->posts, SORT_REGULAR);
        }

        if (!empty($student_query->posts)) {
            foreach ($student_query->posts as $post) {
                $user = get_user_by('ID', get_field('student', $post->ID));
                $days_present = intval(get_field('days_present', $post->ID));
                $status = get_field('status', $post->ID);
                $date_created = get_the_date('M d, Y', $post->ID);
                $last_updated = get_the_modified_date('M d, Y', $post->ID);

                $students[] = array(
                    $post->ID,
                    $user->display_name,
                    $days_present,
                    ucwords($status),
                    $date_created,
                    $last_updated
                );
            }
        }
    } else {
        while ($student_query->have_posts()) {
            $student_query->the_post();

            $user = get_user_by('ID', get_field('student'));
            $days_present = intval(get_field('days_present'));
            $status = get_field('status');
            $date_created = get_the_date('M d, Y');
            $last_updated = get_the_modified_date('M d, Y');

            $students[] = array(
                get_the_ID(),
                $user->display_name,
                $days_present,
                ucwords($status),
                $date_created,
                $last_updated
            );
        }

        wp_reset_postdata();
    }

    wp_send_json(array(
        'draw' => $draw,
        'recordsTotal' => $total_students,
        'recordsFiltered' => $total_filtered_students,
        'data' => $students
    ));
}

add_action('wp_ajax_gsg_get_class_students', 'gsg_get_class_students');
add_action('wp_ajax_nopriv_gsg_get_class_students', 'gsg_get_class_students');

function gsg_get_class_student() {
    check_ajax_referer('get-class-student-nonce', 'get_class_student_nonce');

    $class_student_id = intval($_POST['class_student_id']);
    $class_student = get_post($class_student_id);

    if (!$class_student) {
        wp_send_json_error('Student not found.', 404);
    }

    $class_student_data = array(
        'id' => $class_student->ID,
        'student' => intval(get_field('student', $class_student->ID)),
        'days_present' => get_field('days_present', $class_student->ID),
        'status' => get_field('status', $class_student->ID)
    );

    wp_send_json_success($class_student_data);
}

add_action('wp_ajax_gsg_get_class_student', 'gsg_get_class_student');
add_action('wp_ajax_nopriv_gsg_get_class_student', 'gsg_get_class_student');

function gsg_create_class_student() {
    check_ajax_referer('create-class-student-nonce', 'create_class_student_nonce');

    global $current_user;

    $errors = array();

    $class_id = intval($_POST['class_id']);
    $student = intval($_POST['student']);
    $days_present = intval($_POST['days_present']);
    $status = $_POST['status'];

    if (empty($class_id)) {
        wp_send_json_error(array('create_class_student_error' => 'The class ID field is required.'), 204);
    }

    if (empty($student)) {
        $errors['student'] = 'Please select a student.';
    }

    if (empty($days_present)) {
        $errors['days_present'] = 'The days present field is required.';
    }else if ($days_present < 0) {
        $errors['days_present'] = 'The days present field must not be less than 0.';
    }

    if (empty($status)) {
        $errors['status'] = 'Please select a status.';
    }

    if (!empty($errors)) {
        wp_send_json_error($errors, 400);
    }

    $student_query = new WP_Query(array(
        'post_type' => 'student',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'class',
                'value' => $class_id
            ),
            array(
                'key' => 'student',
                'value' => $student
            )
        )
    ));

    if ($student_query->found_posts > 0) {
        wp_send_json_error(array('create_class_student_error' => 'The student already exists.'), 409);
    }

    $post_id = wp_insert_post(array(
        'post_type' => 'student',
        'post_status' => 'publish',
        'post_author' => $current_user->ID
    ));

    wp_update_post(array(
        'ID' => $post_id,
        'post_title' => $post_id
    ));

    update_field('class', $class_id, $post_id);
    update_field('student', $student, $post_id);
    update_field('days_present', $days_present, $post_id);
    update_field('status', $status, $post_id);

    wp_send_json_success(array('message' => 'Student has been added.'), 201);
}

add_action('wp_ajax_gsg_create_class_student', 'gsg_create_class_student');
add_action('wp_ajax_nopriv_gsg_create_class_student', 'gsg_create_class_student');

function gsg_update_class_student() {
    check_ajax_referer('update-class-student-nonce', 'update_class_student_nonce');

    $errors = array();

    $class_id = intval($_POST['class_id']);
    $class_student_id = intval($_POST['class_student_id']);
    $student = intval($_POST['student']);
    $days_present = intval($_POST['days_present']);
    $status = $_POST['status'];

    if (empty($class_id)) {
        wp_send_json_error(array('update_class_student_error' => 'The class ID field is required.'), 204);
    }

    if (empty($class_student_id)) {
        wp_send_json_error(array('update_class_student_error' => 'The class student ID field is required.'), 204);
    }

    if (empty($student)) {
        $errors['student'] = 'Please select a student.';
    }

    if (empty($days_present)) {
        $errors['days_present'] = 'The days present field is required.';
    }else if ($days_present < 0) {
        $errors['days_present'] = 'The days present field must not be less than 0.';
    }

    if (empty($status)) {
        $errors['status'] = 'Please select a status.';
    }

    if (!empty($errors)) {
        wp_send_json_error($errors, 400);
    }

    update_field('class', $class_id, $class_student_id);
    update_field('student', $student, $class_student_id);
    update_field('days_present', $days_present, $class_student_id);
    update_field('status', $status, $class_student_id);

    wp_send_json_success(array('message' => 'Student has been updated.'));
}

add_action('wp_ajax_gsg_update_class_student', 'gsg_update_class_student');
add_action('wp_ajax_nopriv_gsg_update_class_student', 'gsg_update_class_student');

function gsg_remove_class_student() {
    check_ajax_referer('remove-class-student-nonce', 'remove_class_student_nonce');

    $class_student_id = intval($_POST['class_student_id']);

    if (empty($class_student_id)) {
        wp_send_json_error(array('remove_class_student_error' => 'The student ID field is required.'), 204);
    }

    wp_delete_post($class_student_id);

    wp_send_json_success();
}

add_action('wp_ajax_gsg_remove_class_student', 'gsg_remove_class_student');
add_action('wp_ajax_nopriv_gsg_remove_class_student', 'gsg_remove_class_student');

function gsg_get_records() {
    check_ajax_referer('get-records-nonce', 'get_records_nonce');

    global $wpdb, $current_user;

    $draw = intval($_GET['draw']);
    $offset = intval($_GET['start']);
    $limit = intval($_GET['length']);
    $search = trim($_GET['search']['value']);
    $student = intval($_GET['student']);
    $category = intval($_GET['category']);
    $type = trim($_GET['type']);
    $class_id = intval($_GET['class_id']);

    $order_column_index = intval($_GET['order'][0]['column']);
    $order_column = '';
    $order_column_meta_key = '';

    switch ($order_column_index) {
        case 0:
            $order_column = 'ID';
            break;
        case 1:
            $order_column = 'meta_value';
            $order_column_meta_key = 'student';
            break;
        case 2:
            $order_column = 'meta_value';
            $order_column_meta_key = 'category';
            break;
        case 3:
            $order_column = 'meta_value';
            $order_column_meta_key = 'type';
            break;
        case 4:
            $order_column = 'meta_value';
            $order_column_meta_key = 'score';
            break;
        case 5:
            $order_column = 'meta_value';
            $order_column_meta_key = 'total_score';
            break;
        case 6:
            $order_column = 'post_date';
            break;
        case 7:
            $order_column = 'post_modified';
            break;
        default:
            break;
    }

    $order_direction = $_GET['order'][0]['dir'];

    $records = array();

    $total_record_query = new WP_Query(array(
        'post_type' => 'record',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'class',
                'value' => $class_id
            )
        )
    ));

    $total_records = $total_record_query->found_posts;
    $total_filtered_records = $total_records;

    $record_query = new WP_Query(array(
        'post_type' => 'record',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'offset' => $offset,
        'orderby' => empty($order_column_index) ? 'post_date' : $order_column,
        'order' => empty($order_direction) ? 'DESC' : $order_direction,
        'meta_key' => $order_column_meta_key,
        'meta_query' => array(
            array(
                'key' => 'class',
                'value' => $class_id
            )
        )
    ));

    if (!empty($search) || !empty($student) || !empty($category) || !empty($type)) {
        $total_record_query_search = new WP_Query(array(
            'post_type' => 'record',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            's' => esc_attr($search),
            'meta_query' => array(
                array(
                    'key' => 'class',
                    'value' => $class_id
                )
            )
        ));

        if (!empty($student) && empty($category) && empty($type)) {
            $meta_query = array(
                array(
                    'key' => 'student',
                    'value' => $student
                ),
            );
        } else if (!empty($student) && !empty($category) && empty($type)) {
            $meta_query = array(
                'relation' => 'AND',
                array(
                    'key' => 'student',
                    'value' => $student
                ),
                array(
                    'key' => 'category',
                    'value' => $category
                ),
            );
        } else if (!empty($student) && empty($category) && !empty($type)) {
            $meta_query = array(
                'relation' => 'AND',
                array(
                    'key' => 'student',
                    'value' => $student
                ),
                array(
                    'key' => 'type',
                    'value' => $type
                ),
            );
        } else if (empty($student) && !empty($category) && empty($type)) {
            $meta_query = array(
                array(
                    'key' => 'category',
                    'value' => $category
                ),
            );
        } else if (empty($student) && !empty($category) && !empty($type)) {
            $meta_query = array(
                'relation' => 'AND',
                array(
                    'key' => 'category',
                    'value' => $category
                ),
                array(
                    'key' => 'type',
                    'value' => $type
                ),
            );
        } else if (empty($student) && empty($category) && !empty($type)) {
            $meta_query = array(
                array(
                    'key' => 'type',
                    'value' => $type
                ),
            );
        } else if (!empty($student) && !empty($category) && !empty($type)) {
            $meta_query = array(
                'relation' => 'AND',
                array(
                    'key' => 'student',
                    'value' => $student
                ),
                array(
                    'key' => 'category',
                    'value' => $category
                ),
                array(
                    'key' => 'type',
                    'value' => $type
                ),
            );
        }

        $meta_query[] = array(
            'key' => 'class',
            'value' => $class_id
        );

        $total_record_query_meta_key = new WP_Query(array(
            'post_type' => 'record',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => $meta_query
        ));

        $total_record_query = new WP_Query();

        if (!empty($search)) {
            $total_record_query->posts = array_unique($total_record_query_search->posts, SORT_REGULAR);
        } else {
            $total_record_query->posts = array_unique($total_record_query_meta_key->posts, SORT_REGULAR);
        }

        $total_filtered_records = count($total_record_query->posts);

        $record_query_search = new WP_Query(array(
            'post_type' => 'record',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'offset' => $offset,
            'orderby' => empty($order_column_index) ? 'post_date' : $order_column,
            'order' => empty($order_direction) ? 'DESC' : $order_direction,
            'meta_key' => $order_column_meta_key,
            's' => esc_attr($search),
            'meta_query' => array(
                array(
                    'key' => 'class',
                    'value' => $class_id
                )
            )
        ));

        $record_query_meta_key = new WP_Query(array(
            'post_type' => 'record',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'offset' => $offset,
            'orderby' => empty($order_column_index) ? 'post_date' : $order_column,
            'order' => empty($order_direction) ? 'DESC' : $order_direction,
            'meta_key' => $order_column_meta_key,
            'meta_query' => $meta_query
        ));

        $record_query = new WP_Query();

        if (!empty($search)) {
            $record_query->posts = array_unique($record_query_search->posts, SORT_REGULAR);
        } else {
            $record_query->posts = array_unique($record_query_meta_key->posts, SORT_REGULAR);
        }

        if (!empty($record_query->posts)) {
            foreach ($record_query->posts as $post) {
                $user = get_user_by('ID', get_field('student', $post->ID));
                $category_name = get_field('category', $post->ID) ? get_category(get_field('category', $post->ID))->name : 'N/A';
                $type = ucwords(str_replace('-', ' ', get_field('type', $post->ID)));
                $score = intval(get_field('score', $post->ID));
                $total_score = intval(get_field('total_score', $post->ID));
                $date_created = get_the_date('M d, Y', $post->ID);
                $last_updated = get_the_modified_date('M d, Y', $post->ID);

                $records[] = array(
                    $post->ID,
                    $user->display_name,
                    $type,
                    $category_name,
                    $score,
                    $total_score,
                    $date_created,
                    $last_updated
                );
            }
        }
    } else {
        while ($record_query->have_posts()) {
            $record_query->the_post();

            $user = get_user_by('ID', get_field('student'));
            $category_name = get_field('category') ? get_category(get_field('category'))->name : 'N/A';
            $type = ucwords(str_replace('-', ' ', get_field('type')));
            $score = intval(get_field('score'));
            $total_score = intval(get_field('total_score'));
            $date_created = get_the_date('M d, Y');
            $last_updated = get_the_modified_date('M d, Y');

            $records[] = array(
                get_the_ID(),
                $user->display_name,
                $type,
                $category_name,
                $score,
                $total_score,
                $date_created,
                $last_updated
            );
        }

        wp_reset_postdata();
    }

    wp_send_json(array(
        'draw' => $draw,
        'recordsTotal' => $total_records,
        'recordsFiltered' => $total_filtered_records,
        'data' => $records
    ));
}

add_action('wp_ajax_gsg_get_records', 'gsg_get_records');
add_action('wp_ajax_nopriv_gsg_get_records', 'gsg_get_records');

function gsg_get_record() {
    check_ajax_referer('get-record-nonce', 'get_record_nonce');

    global $wpdb, $current_user;

    $record_id = intval($_POST['record_id']);

    $get_record = get_post($record_id);
    $record = array();

    if (is_null($get_record)) {
        wp_send_json_error(array('get_record_error' => 'Record not found.'), 404); 
    }

    $record = array(
        'id' => $get_record->ID,
        'student' => get_field('student', $get_record->ID),
        'category' => get_field('category', $get_record->ID) ? get_field('category', $get_record->ID) : 'N/A',
        'type' => get_field('type', $get_record->ID),
        'score' => intval(get_field('score', $get_record->ID)),
        'total_score' => intval(get_field('total_score', $get_record->ID))
    );

    wp_send_json_success($record);
}

add_action('wp_ajax_gsg_get_record', 'gsg_get_record');
add_action('wp_ajax_nopriv_gsg_get_record', 'gsg_get_record');

function gsg_create_record() {
    check_ajax_referer('create-record-nonce', 'create_record_nonce');

    global $current_user;

    $errors = array();

    $class_id = intval($_POST['class_id']);
    $teacher = intval($current_user->ID);
    $student = intval($_POST['student']);
    $category = intval($_POST['category']);
    $type = trim($_POST['type']);
    $score = intval($_POST['score']);
    $total_score = intval($_POST['total_score']);

    if (empty($class_id)) {
        wp_send_json_error(array('create_record_error' => 'The class ID field is required.'), 204);
    }

    if (empty($student)) {
        $errors['student'] = 'Please select a student.';
    }

    if ($type == 'quiz') {
        if (empty($category)) {
            $errors['category'] = 'Please select a category.';
        }
    }

    if (empty($type)) {
        $errors['type'] = 'Please select a type.';
    }

    if ($score < 0) {
        $errors['score'] = 'The score field must not be less than 0.';
    } else if ($score > $total_score) {
        $errors['score'] = 'The score field must not be greater than the total score.';
    }

    if ($total_score < 0) {
        $errors['total_score'] = 'The total score field must not be less than 0.';
    }

    if (!empty($errors)) {
        wp_send_json_error($errors, 400);
    }

    $post_id = wp_insert_post(array(
        'post_type' => 'record',
        'post_status' => 'publish',
        'post_author' => $current_user->ID
    ));

    wp_update_post(array(
        'ID' => $post_id,
        'post_title' => $post_id
    ));

    update_field('class', $class_id, $post_id);
    update_field('teacher', $teacher, $post_id);
    update_field('student', $student, $post_id);

    if ($type == 'quiz') {
        update_field('category', $category, $post_id);
    } else {
        if (get_field('category', $post_id)) {
            delete_field('category', $post_id);
        }
    }

    update_field('type', $type, $post_id);
    update_field('score', $score, $post_id);
    update_field('total_score', $total_score, $post_id);

    wp_send_json_success(array('message' => 'Record has been created.'), 201);
}

add_action('wp_ajax_gsg_create_record', 'gsg_create_record');
add_action('wp_ajax_nopriv_gsg_create_record', 'gsg_create_record');

function gsg_update_record() {
    check_ajax_referer('update-record-nonce', 'update_record_nonce');

    global $current_user;

    $errors = array();

    $record_id = intval($_POST['record_id']);
    $student = intval($_POST['student']);
    $category = intval($_POST['category']);
    $type = trim($_POST['type']);
    $score = intval($_POST['score']);
    $total_score = intval($_POST['total_score']);

    if (empty($record_id)) {
        wp_send_json_error(array('update_record_error' => 'The record ID field is required.'), 204);
    }

    if (empty($student)) {
        $errors['student'] = 'Please select a student.';
    }

    if ($type == 'quiz') {
        if (empty($category)) {
            $errors['category'] = 'Please select a category.';
        }
    }

    if (empty($type)) {
        $errors['type'] = 'Please select a type.';
    }

    if ($score < 0) {
        $errors['score'] = 'The score field must not be less than 0.';
    } else if ($score > $total_score) {
        $errors['score'] = 'The score field must not be greater than the total score.';
    }

    if ($total_score < 0) {
        $errors['total_score'] = 'The total score field must not be less than 0.';
    }

    if (!empty($errors)) {
        wp_send_json_error($errors, 400);
    }

    update_field('student', $student, $record_id);

    if ($type == 'quiz') {
        update_field('category', $category, $record_id);
    } else {
        if (get_field('category', $record_id)) {
            delete_field('category', $record_id);
        }
    }
    
    update_field('type', $type, $record_id);
    update_field('score', $score, $record_id);
    update_field('total_score', $total_score, $record_id);

    wp_send_json_success(array('message' => 'Record has been updated.'));
}

add_action('wp_ajax_gsg_update_record', 'gsg_update_record');
add_action('wp_ajax_nopriv_gsg_update_record', 'gsg_update_record');

function gsg_delete_record() {
    check_ajax_referer('delete-record-nonce', 'delete_record_nonce');

    $record_id = intval($_POST['record_id']);

    if (empty($record_id)) {
        wp_send_json_error(array('delete_record_error' => 'The record ID field is required.'), 204);
    }

    wp_delete_post($record_id);

    wp_send_json_success();
}

add_action('wp_ajax_gsg_delete_record', 'gsg_delete_record');
add_action('wp_ajax_nopriv_gsg_delete_record', 'gsg_delete_record');

function gsg_generate_report() {
    check_ajax_referer('generate-report-nonce', 'generate_report_nonce');

    $class_id = intval($_POST['class_id']);

    if (empty($class_id)) {
        wp_send_json_error(array('generate_report_error' => 'The class ID field is required.'), 204);
    }

    $class = get_post($class_id);

    $code = $class->post_title;
    $teacher = get_user_by('ID', $class->post_author);
    $level = get_field('level', $class_id);
    $passing_grade = floatval(get_field('passing_grade', $class_id));

    global $wpdb;

    $record_query = new WP_Query(array(
        'post_type' => 'record',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'author' => $teacher->ID,
        'meta_query' => array(
            array(
                'key' => 'class',
                'value' => $class_id
            )
        )
    ));

    $records = array();
    
    $raw_student_ids = array();
    
    foreach ($record_query->posts as $record) {
        $student = get_user_by('ID', get_field('student', $record->ID));

        $raw_student_ids[] = $student->ID;
    }

    $student_ids = array_unique($raw_student_ids, SORT_REGULAR);

    foreach ($student_ids as $student) {
        /* Start Leseverstehen und Wortschatz */
        $leseverstehen_quiz_query = new WP_Query(array(
            'post_type' => 'record',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'author' => $teacher->ID,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'class',
                    'value' => $class_id
                ),
                array(
                    'key' => 'student',
                    'value' => $student
                ),
                array(
                    'key' => 'category',
                    'value' => 3
                ),
                array(
                    'key' => 'type',
                    'value' => 'quiz'
                )
            )
        ));

        $leseverstehen_quiz_scores = array();
        $leseverstehen_quiz_total_scores = array();

        foreach ($leseverstehen_quiz_query->posts as $item) {
            $leseverstehen_quiz_scores[] = get_field('score', $item->ID);
            $leseverstehen_quiz_total_scores[] = get_field('total_score', $item->ID);
        }

        $total_leseverstehen_quiz_score = array_sum($leseverstehen_quiz_scores);
        $total_leseverstehen_quiz_percentage_score = round((($total_leseverstehen_quiz_score / array_sum($leseverstehen_quiz_total_scores)) * 0.20) * 100, 2);
        /* End Leseverstehen und Wortschatz */

        /* Start Horverstehen */
        $horverstehen_quiz_query = new WP_Query(array(
            'post_type' => 'record',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'author' => $teacher->ID,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'class',
                    'value' => $class_id
                ),
                array(
                    'key' => 'student',
                    'value' => $student
                ),
                array(
                    'key' => 'category',
                    'value' => 4
                ),
                array(
                    'key' => 'type',
                    'value' => 'quiz'
                )
            )
        ));

        $horverstehen_quiz_scores = array();
        $horverstehen_quiz_total_scores = array();

        foreach ($horverstehen_quiz_query->posts as $item) {
            $horverstehen_quiz_scores[] = get_field('score', $item->ID);
            $horverstehen_quiz_total_scores[] = get_field('total_score', $item->ID);
        }

        $total_horverstehen_quiz_score = array_sum($horverstehen_quiz_scores);
        $total_horverstehen_quiz_percentage_score = round((($total_horverstehen_quiz_score / array_sum($horverstehen_quiz_total_scores)) * 0.20) * 100, 2);
        /* End Horverstehen */

        /* Start Grammatik */
        $grammatik_quiz_query = new WP_Query(array(
            'post_type' => 'record',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'author' => $teacher->ID,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'class',
                    'value' => $class_id
                ),
                array(
                    'key' => 'student',
                    'value' => $student
                ),
                array(
                    'key' => 'category',
                    'value' => 5
                ),
                array(
                    'key' => 'type',
                    'value' => 'quiz'
                )
            )
        ));

        $grammatik_quiz_scores = array();
        $grammatik_quiz_total_scores = array();

        foreach ($grammatik_quiz_query->posts as $item) {
            $grammatik_quiz_scores[] = get_field('score', $item->ID);
            $grammatik_quiz_total_scores[] = get_field('total_score', $item->ID);
        }

        $total_grammatik_quiz_score = array_sum($grammatik_quiz_scores);
        $total_grammatik_quiz_percentage_score = round((($total_grammatik_quiz_score / array_sum($grammatik_quiz_total_scores)) * 0.20) * 100, 2);
        /* End Grammatik */

        /* Start Mundlich */
        $mundlich_quiz_query = new WP_Query(array(
            'post_type' => 'record',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'author' => $teacher->ID,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'class',
                    'value' => $class_id
                ),
                array(
                    'key' => 'student',
                    'value' => $student
                ),
                array(
                    'key' => 'category',
                    'value' => 6
                ),
                array(
                    'key' => 'type',
                    'value' => 'quiz'
                )
            )
        ));

        $mundlich_quiz_scores = array();
        $mundlich_quiz_total_scores = array();

        foreach ($mundlich_quiz_query->posts as $item) {
            $mundlich_quiz_scores[] = get_field('score', $item->ID);
            $mundlich_quiz_total_scores[] = get_field('total_score', $item->ID);
        }

        $total_mundlich_quiz_score = array_sum($mundlich_quiz_scores);
        $total_mundlich_quiz_percentage_score = round((($total_mundlich_quiz_score / array_sum($mundlich_quiz_total_scores)) * 0.20) * 100, 2);
        /* End Mundlich */

        /* Start Aufsatz */
        $aufsatz_quiz_query = new WP_Query(array(
            'post_type' => 'record',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'author' => $teacher->ID,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'class',
                    'value' => $class_id
                ),
                array(
                    'key' => 'student',
                    'value' => $student
                ),
                array(
                    'key' => 'category',
                    'value' => 7
                ),
                array(
                    'key' => 'type',
                    'value' => 'quiz'
                )
            )
        ));

        $aufsatz_quiz_scores = array();
        $aufsatz_quiz_total_scores = array();

        foreach ($aufsatz_quiz_query->posts as $item) {
            $aufsatz_quiz_scores[] = get_field('score', $item->ID);
            $aufsatz_quiz_total_scores[] = get_field('total_score', $item->ID);
        }

        $total_aufsatz_quiz_score = array_sum($aufsatz_quiz_scores);
        $total_aufsatz_quiz_percentage_score = round((($total_aufsatz_quiz_score / array_sum($aufsatz_quiz_total_scores)) * 0.20) * 100, 2);
        /* End Aufsatz */

        $exam_query = new WP_Query(array(
            'post_type' => 'record',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'author' => $teacher->ID,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'class',
                    'value' => $class_id
                ),
                array(
                    'key' => 'student',
                    'value' => $student
                ),
                array(
                    'key' => 'type',
                    'value' => 'exam'
                )
            )
        ));

        $exam_scores = array();
        $exam_total_scores = array();

        foreach ($exam_query->posts as $item) {
            $exam_scores[] = get_field('score', $item->ID);
            $exam_total_scores[] = get_field('total_score', $item->ID);
        }

        $total_exam_score = array_sum($exam_scores);
        $total_exam_percentage_score = round((($total_exam_score / array_sum($exam_total_scores)) * 0.30) * 100, 2);

        $student_query = new WP_Query(array(
            'post_type' => 'student',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'author' => $teacher->ID,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => 'class',
                    'value' => $class_id
                ),
                array(
                    'key' => 'student',
                    'value' => $student
                )
            )
        ));

        $student_id = $student_query->posts[0]->ID;
        $days_present = intval(get_field('days_present', $student_id));
        $duration = intval(get_field('duration', $class_id));

        $records[] = array(
            'student' => get_user_by('ID', $student)->display_name,

            'leseverstehen' => $total_leseverstehen_quiz_score,
            'leseverstehen_percentage' => "$total_leseverstehen_quiz_percentage_score%",
            'leseverstehen_total' => array_sum($leseverstehen_quiz_total_scores),

            'horverstehen' => $total_horverstehen_quiz_score,
            'horverstehen_percentage' => "$total_horverstehen_quiz_percentage_score%",
            'horverstehen_total' => array_sum($horverstehen_quiz_total_scores),

            'grammatik' => $total_grammatik_quiz_score,
            'grammatik_percentage' => "$total_grammatik_quiz_percentage_score%",
            'grammatik_total' => array_sum($grammatik_quiz_total_scores),

            'mundlich' => $total_mundlich_quiz_score,
            'mundlich_percentage' => "$total_mundlich_quiz_percentage_score%",
            'mundlich_total' => array_sum($mundlich_quiz_total_scores),

            'aufsatz' => $total_aufsatz_quiz_score,
            'aufsatz_percentage' => "$total_aufsatz_quiz_percentage_score%",
            'aufsatz_total' => array_sum($aufsatz_quiz_total_scores),

            'total' => $total_leseverstehen_quiz_score + $total_horverstehen_quiz_score + $total_grammatik_quiz_score + $total_mundlich_quiz_score + $total_aufsatz_quiz_score,
            'total_percentage' => round((($total_leseverstehen_quiz_percentage_score + $total_horverstehen_quiz_percentage_score + $total_grammatik_quiz_percentage_score + $total_mundlich_quiz_percentage_score + $total_aufsatz_quiz_percentage_score) * 0.60), 2) . '%',
            'quizzes_total' => array_sum($leseverstehen_quiz_total_scores) + array_sum($horverstehen_quiz_total_scores) + array_sum($grammatik_quiz_total_scores) + array_sum($mundlich_quiz_total_scores) + array_sum($aufsatz_quiz_total_scores),

            'exam' => $total_exam_score,
            'exam_percentage' => "$total_exam_percentage_score%",
            'exam_total' => array_sum($exam_total_scores),

            'attendance' => $days_present,
            'attendance_percentage' => round(((($days_present / $duration) * 0.10) * 100), 2) . '%',
            'attendance_total' => $duration,

            'final_grade' => round((($total_leseverstehen_quiz_percentage_score + $total_horverstehen_quiz_percentage_score + $total_grammatik_quiz_percentage_score + $total_mundlich_quiz_percentage_score + $total_aufsatz_quiz_percentage_score) * 0.60), 2) + $total_exam_percentage_score + round(((($days_present / $duration) * 0.10) * 100), 2)
        );
    }

    foreach ($records as $record) {
        $record_row_html .= '<tr>';
            $record_row_html .= '<td>' . $record['student'] . '</td>';
            $record_row_html .= '<td>' . $record['leseverstehen'] . ' (' . $record['leseverstehen_percentage'] . ')</td>';
            $record_row_html .= '<td>' . $record['horverstehen'] . ' (' . $record['horverstehen_percentage'] . ')</td>';
            $record_row_html .= '<td>' . $record['grammatik'] . ' (' . $record['grammatik_percentage'] . ')</td>';
            $record_row_html .= '<td>' . $record['mundlich'] . ' (' . $record['mundlich_percentage'] . ')</td>';
            $record_row_html .= '<td>' . $record['aufsatz'] . ' (' . $record['aufsatz_percentage'] . ')</td>';
            $record_row_html .= '<td>' . $record['total'] . ' (' . $record['total_percentage'] . ')</td>';
            $record_row_html .= '<td>' . $record['exam'] . ' (' . $record['exam_percentage'] . ')</td>';
            $record_row_html .= '<td>' . $record['attendance'] . ' (' . $record['attendance_percentage'] . ')</td>';

            if ($record['final_grade'] < $passing_grade) {
                $record_row_html .= '<td><strong style="color: red;">' . $record['final_grade']  . '%</strong></td>';
            } else {
                $record_row_html .= '<td><strong style="color: green;">' . $record['final_grade']  . '%</strong></td>';
            }
        $record_row_html .= '</tr>';
    }

    $record_row_html .= '<tr>';
        $record_row_html .= '<th>Over</th>';
        $record_row_html .= '<th>' . $record['leseverstehen_total'] . '</th>';
        $record_row_html .= '<th>' . $record['horverstehen_total'] . '</th>';
        $record_row_html .= '<th>' . $record['grammatik_total'] . '</th>';
        $record_row_html .= '<th>' . $record['mundlich_total'] . '</th>';
        $record_row_html .= '<th>' . $record['aufsatz_total'] . '</th>';
        $record_row_html .= '<th>' . $record['quizzes_total'] . '</th>';
        $record_row_html .= '<th>' . $record['exam_total'] . '</th>';
        $record_row_html .= '<th>' . $record['attendance_total'] . '</th>';
        $record_row_html .= '<th></th>';
    $record_row_html .= '</tr>';

    include_once GSG_VENDORS_PATH . '/dompdf/autoload.inc.php';

    $options = new \Dompdf\Options();
    $options->setIsRemoteEnabled(true);
    $options->isHtml5ParserEnabled(true);

    $dompdf = new \Dompdf\Dompdf($options);
    $dompdf->setPaper('A4', 'landscape');

    $html_template_raw = file_get_contents(GSG_VENDORS_PATH . '/dompdf/pdf_template.html');
    $html_template = '';

    $variables = array(
        '[CODE]' => $code,
        '[LEVEL]' => $level,
        '[TEACHER]' => $teacher->display_name,
        '[RECORDS]' => $record_row_html
    );

    // wp_send_json(array(
    //     'variables' => $variables
    // ));

    // die(0);

    foreach ($variables as $key => $value) {
        $html_template = strtr($html_template_raw, $variables);
    }

    foreach ($variables as $key => $value) {
        $html_template = strtr($html_template_raw, $variables);
    }

    $dompdf->loadHtml($html_template, 'UTF-8');
    $dompdf->render();

    file_put_contents(GSG_UPLOADS_PATH . "/reports/$code.pdf", $dompdf->output());

    $post_exists = get_page_by_title($code, OBJECT, 'report');

    if (is_null($post_exists)) {
        $report_id = wp_insert_post(array(
            'post_title' => $code,
            'post_type' => 'report',
            'post_status' => 'publish',
            'post_author' => $class->post_author,
        ));

        update_field('file_url', GSG_UPLOADS_URL . "/reports/$code.pdf", $report_id);
    }

    wp_send_json_success(array(
        'file_url' => GSG_UPLOADS_URL . "/reports/$code.pdf"
    ));
}

add_action('wp_ajax_gsg_generate_report', 'gsg_generate_report');
add_action('wp_ajax_nopriv_gsg_generate_report', 'gsg_generate_report');

function gsg_get_student_grades() {
    check_ajax_referer('get-student-grades-nonce', 'get_student_grades_nonce');

    global $wpdb;

    $draw = intval($_GET['draw']);
    $offset = intval($_GET['start']);
    $limit = intval($_GET['length']);
    $search = trim($_GET['search']['value']);
    $student = intval($_GET['student']);
    $teacher = intval($_GET['teacher']);
    $category = intval($_GET['category']);
    $type = trim($_GET['type']);
    $class_id = intval($_GET['class_id']);

    $order_column_index = intval($_GET['order'][0]['column']);
    $order_column = '';
    $order_column_meta_key = '';

    switch ($order_column_index) {
        case 0:
            $order_column = 'ID';
            break;
        case 1:
            $order_column = 'meta_value';
            $order_column_meta_key = 'teacher';
            break;
        case 2:
            $order_column = 'meta_value';
            $order_column_meta_key = 'category';
            break;
        case 3:
            $order_column = 'meta_value';
            $order_column_meta_key = 'type';
            break;
        case 4:
            $order_column = 'post_date';
            break;
        case 5:
            $order_column = 'post_modified';
            break;
        default:
            break;
    }

    $order_direction = $_GET['order'][0]['dir'];

    $records = array();

    $total_record_query = new WP_Query(array(
        'post_type' => 'record',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => 'student',
                'value' => $student
            )
        )
    ));

    $total_records = $total_record_query->found_posts;
    $total_filtered_records = $total_records;

    $record_query = new WP_Query(array(
        'post_type' => 'record',
        'post_status' => 'publish',
        'posts_per_page' => $limit,
        'offset' => $offset,
        'orderby' => empty($order_column_index) ? 'post_date' : $order_column,
        'order' => empty($order_direction) ? 'DESC' : $order_direction,
        'meta_key' => $order_column_meta_key,
        'meta_query' => array(
            array(
                'key' => 'student',
                'value' => $student
            )
        )
    ));

    if (!empty($search) || !empty($teacher) || !empty($category) || !empty($type)) {
        $total_record_query_search = new WP_Query(array(
            'post_type' => 'record',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            's' => esc_attr($search),
            'meta_query' => array(
                array(
                    'key' => 'student',
                    'value' => $student
                )
            )
        ));

        if (!empty($teacher) && empty($category) && empty($type)) {
            $meta_query = array(
                array(
                    'key' => 'teacher',
                    'value' => $teacher
                ),
            );
        } else if (!empty($teacher) && !empty($category) && empty($type)) {
            $meta_query = array(
                'relation' => 'AND',
                array(
                    'key' => 'teacher',
                    'value' => $teacher
                ),
                array(
                    'key' => 'category',
                    'value' => $category
                ),
            );
        } else if (!empty($teacher) && empty($category) && !empty($type)) {
            $meta_query = array(
                'relation' => 'AND',
                array(
                    'key' => 'teacher',
                    'value' => $teacher
                ),
                array(
                    'key' => 'type',
                    'value' => $type
                ),
            );
        } else if (empty($teacher) && !empty($category) && empty($type)) {
            $meta_query = array(
                array(
                    'key' => 'category',
                    'value' => $category
                ),
            );
        } else if (empty($teacher) && !empty($category) && !empty($type)) {
            $meta_query = array(
                'relation' => 'AND',
                array(
                    'key' => 'category',
                    'value' => $category
                ),
                array(
                    'key' => 'type',
                    'value' => $type
                ),
            );
        } else if (empty($teacher) && empty($category) && !empty($type)) {
            $meta_query = array(
                array(
                    'key' => 'type',
                    'value' => $type
                ),
            );
        } else if (!empty($teacher) && !empty($category) && !empty($type)) {
            $meta_query = array(
                'relation' => 'AND',
                array(
                    'key' => 'teacher',
                    'value' => $student
                ),
                array(
                    'key' => 'category',
                    'value' => $category
                ),
                array(
                    'key' => 'type',
                    'value' => $type
                ),
            );
        }

        $meta_query[] = array(
            'key' => 'student',
            'value' => $student
        );

        $total_record_query_meta_key = new WP_Query(array(
            'post_type' => 'record',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => $meta_query
        ));

        $total_record_query = new WP_Query();

        if (!empty($search)) {
            $total_record_query->posts = array_unique($total_record_query_search->posts, SORT_REGULAR);
        } else {
            $total_record_query->posts = array_unique($total_record_query_meta_key->posts, SORT_REGULAR);
        }

        $total_filtered_records = count($total_record_query->posts);

        $record_query_search = new WP_Query(array(
            'post_type' => 'record',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'offset' => $offset,
            'orderby' => empty($order_column_index) ? 'post_date' : $order_column,
            'order' => empty($order_direction) ? 'DESC' : $order_direction,
            'meta_key' => $order_column_meta_key,
            's' => esc_attr($search),
            'meta_query' => array(
                array(
                    'key' => 'student',
                    'value' => $student
                )
            )
        ));

        $record_query_meta_key = new WP_Query(array(
            'post_type' => 'record',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'offset' => $offset,
            'orderby' => empty($order_column_index) ? 'post_date' : $order_column,
            'order' => empty($order_direction) ? 'DESC' : $order_direction,
            'meta_key' => $order_column_meta_key,
            'meta_query' => $meta_query
        ));

        $record_query = new WP_Query();

        if (!empty($search)) {
            $record_query->posts = array_unique($record_query_search->posts, SORT_REGULAR);
        } else {
            $record_query->posts = array_unique($record_query_meta_key->posts, SORT_REGULAR);
        }

        if (!empty($record_query->posts)) {
            foreach ($record_query->posts as $post) {
                $teacher = get_user_by('ID', get_field('teacher', $post->ID));
                $category_name = get_field('category', $post->ID) ? get_category(get_field('category', $post->ID))->name : 'N/A';
                $type = ucwords(str_replace('-', ' ', get_field('type', $post->ID)));
                $score = intval(get_field('score', $post->ID));
                $total_score = intval(get_field('total_score', $post->ID));
                $date_created = get_the_date('M d, Y', $post->ID);
                $last_updated = get_the_modified_date('M d, Y', $post->ID);

                $class = get_post(intval(get_field('class', $post->ID)));

                $records[] = array(
                    $post->ID,
                    $class->post_title,
                    $teacher->display_name,
                    $category_name,
                    $type,
                    "$score / $total_score",
                    $date_created,
                    $last_updated
                );
            }
        }
    } else {
        while ($record_query->have_posts()) {
            $record_query->the_post();

            $teacher = get_user_by('ID', get_field('teacher'));
            $category_name = get_field('category') ? get_category(get_field('category'))->name : 'N/A';
            $type = ucwords(str_replace('-', ' ', get_field('type')));
            $score = intval(get_field('score'));
            $total_score = intval(get_field('total_score'));
            $date_created = get_the_date('M d, Y');
            $last_updated = get_the_modified_date('M d, Y');

            $class = get_post(intval(get_field('class')));

            $records[] = array(
                get_the_ID(),
                $class->post_title,
                $teacher->display_name,
                $category_name,
                $type,
                "$score / $total_score",
                $date_created,
                $last_updated
            );
        }

        wp_reset_postdata();
    }

    wp_send_json(array(
        'draw' => $draw,
        'recordsTotal' => $total_records,
        'recordsFiltered' => $total_filtered_records,
        'data' => $records
    ));
}

add_action('wp_ajax_gsg_get_student_grades', 'gsg_get_student_grades');
add_action('wp_ajax_nopriv_gsg_get_student_grades', 'gsg_get_student_grades');