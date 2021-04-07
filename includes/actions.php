<?php

/*{{{wpse66094_no_admin_access*/ 
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
/*}}}*/

/*{{{gsg_login*/ 
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
/*}}}*/

/*{{{gsg_logout*/
function gsg_logout() {
    check_ajax_referer('logout-nonce', 'logout_nonce');

    wp_clear_auth_cookie();
    wp_logout();

    ob_clean();

    wp_send_json_success();
}

add_action('wp_ajax_gsg_logout', 'gsg_logout');
add_action('wp_ajax_nopriv_gsg_logout', 'gsg_logout');
/*}}}*/

/*{{{gsg_register*/
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
/*}}}*/

/*{{{gsg_forgot_password*/
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
/*}}}*/

/*{{{gsg_reset_password*/
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
/*}}}*/

/*{{{gsg_save_account_info*/
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
/*}}}*/

/*{{{gsg_upload_update_profile_picture*/
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
/*}}}*/

/*{{{gsg_remove_profile_picture*/
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
/*}}}*/

/*{{{gsg_get_students*/
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
/*}}}*/

/*{{{gsg_get_student*/
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
/*}}}*/

/*{{{gsg_get_classes*/
function gsg_get_classes() {
    check_ajax_referer('get-classes-nonce', 'get_classes_nonce');

    global $wpdb;
    global $current_user;

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
            $order_column_meta_key = 'completion_hours';
            break;
        case 3:
            $order_column = 'meta_value';
            $order_column_meta_key = 'completed_hours';
            break;
        case 4:
            $order_column = 'meta_value';
            $order_column_meta_key = 'remaining_hours';
            break;
        case 5:
            $order_column = 'post_status';
            break;
        case 6:
            $order_column = 'post_date';
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
                    get_field('completion_hours', $post->ID),
                    get_field('completed_hours', $post->ID),
                    get_field('remaining_hours', $post->ID),
                    '<span class="badge bg-' . $post_status_badge_bg . '">' . ucfirst($post_status) . '</span>',
                    get_the_date('M d, Y', $post->ID)
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
                get_field('completion_hours'),
                get_field('completed_hours'),
                get_field('remaining_hours'),
                '<span class="badge bg-' . $post_status_badge_bg . '">' . ucfirst($post_status) . '</span>',
                get_the_date('M d, Y')
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
/*}}}*/

/*{{{gsg_create_class*/
function gsg_create_class() {
    if (!isset($_POST['create_class_nonce']) || !wp_verify_nonce($_POST['create_class_nonce'], 'gsg_create_class')) {
        wp_send_json_error(array('error_message' => 'Something went wrong...'), 500);
    }

    global $current_user;

    $errors = array();

    $completion_hours = intval($_POST['completion_hours']);

    if (empty($completion_hours)) {
        $errors['completion_hours'] = 'The completion hours field is required.';
    } else if (!is_numeric($completion_hours)) {
        $errors['completion_hours'] = 'The completion hours must be numeric.';
    } else if ($completion_hours < 1) {
        $errors['completion_hours'] = 'The completion hours must be greater than 0.';
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

    update_field('completion_hours', $completion_hours, $post_id);
    update_field('completed_hours', 0, $post_id);
    update_field('remaining_hours', $completion_hours, $post_id);

    wp_send_json_success(array('message' => 'Class has been created.'), 201);
}

add_action('wp_ajax_gsg_create_class', 'gsg_create_class');
add_action('wp_ajax_nopriv_gsg_create_class', 'gsg_create_class');
/*}}}*/

/*{{{gsg_delete_class*/
function gsg_delete_class() {
    check_ajax_referer('delete-class-nonce', 'delete_class_nonce');

    //global $current_user;

    $post_id = intval($_POST['class_id']);

    if (empty($post_id)) {
        wp_send_json_error(array('error_message' => 'Please select a class to delete.'), 500);
    }

    if (!wp_trash_post($post_id)) {
        wp_send_json_error(array('error_message' => 'Unable to delete class.'), 500);
    }

    wp_send_json_success();
}

add_action('wp_ajax_gsg_delete_class', 'gsg_delete_class');
add_action('wp_ajax_nopriv_gsg_delete_class', 'gsg_delete_class');
/*}}}*/
