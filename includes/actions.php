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

    $body = "<p>Here's your login credentials:</p><p><strong>Email address:</strong> $email_address<br><strong>Password:</strong> $password</p>";
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
