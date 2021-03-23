<?php

function gsg_setup_theme() {
	add_theme_support('post-thumbnails');
    add_theme_support('title-tag');

	add_theme_support('html5', array(
        'comment-list',
        'comment-form',
        'search-form',
        'gallery',
        'caption'
    ));

	register_nav_menu('primary_menu', 'Primary Menu');
}

add_action('after_setup_theme', 'gsg_setup_theme');

function gsg_enqueue() {
	global $wpdb;

	$uri = get_theme_file_uri();
	$version = GSG_DEV_MODE ? time() : false;

	/*wp_register_style('google-font', 'https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap', array(), null);
    wp_enqueue_style('google-font');*/

	wp_register_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css', array(), null);
    wp_enqueue_style('bootstrap');

	wp_register_style('bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.0/font/bootstrap-icons.css', array(), null);
    wp_enqueue_style('bootstrap-icons');

	wp_register_style('font-awesome', 'https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', array(), null);
    wp_enqueue_style('font-awesome');

	wp_register_style('gsg-style', "$uri/style.css", array(), $version);
	wp_enqueue_style('gsg-style');

    if (gsg_is_login_page() || gsg_is_register_page() || gsg_is_forgot_password_page()) {
        wp_register_style('gsg-auth-style', "$uri/css/auth.css", array(), $version);
        wp_enqueue_style('gsg-auth-style');
    }

	wp_register_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js', array('jquery'), null, true);
	wp_enqueue_script('bootstrap');

	wp_register_script('gsg-script', "$uri/script.js", array('jquery'), $version, true);

	//$entry_locations = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}entry_locations`");

	wp_localize_script('gsg-script', 'gsg', array(
		'ajaxUrl' => admin_url('admin-ajax.php'),
        'homeUrl' => home_url('/'),
        'loginUrl' => LOGIN_PAGE_URL,
        'registerUrl' => REGISTER_PAGE_URL,
        'accountUrl' => ACCOUNT_PAGE_URL,
        'gradesUrl' => GRADES_PAGE_URL,
        'recordsUrl' => RECORDS_PAGE_URL,
        'isLoginPage' => gsg_is_login_page() ? true : false,
        'isRegisterPage' => gsg_is_register_page() ? true : false,
        'logoutNonce' => wp_create_nonce('logout-nonce'),
    ));

	wp_enqueue_script('gsg-script');
}

add_action('wp_enqueue_scripts', 'gsg_enqueue');
