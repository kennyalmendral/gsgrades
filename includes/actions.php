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
    global $wpdb;

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

                $email_exists = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->prefix" . "users WHERE user_email = '%s' LIMIT 1", $_POST[$key]));

                if ($email_exists > 0) {
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

    $to = $email_address;
    $subject = 'Account Registration';

    $body = "<p>Here's your login credentials:</p><p><strong>Email address:</strong> $email_address<br><strong>Password:</strong> $password</p>";
    $body .= '<p>You may login here: ' . LOGIN_PAGE_URL . '</p>';

    $headers = array();
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = 'From: Gottes Segen Grades <info@grades.gottes-segen.com>';
     
    wp_mail($to, $subject, $body, $headers);

    wp_send_json_success();
}

add_action('wp_ajax_gsg_register', 'gsg_register');
add_action('wp_ajax_nopriv_gsg_register', 'gsg_register');
