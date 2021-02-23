<?php

function gsg_setup_theme() {
	add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
	add_theme_support('automatic-feed-links');
	add_theme_support('admin-bar', array('callback' => '__return_false'));

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
	$uri = get_theme_file_uri();
	$version = GSG_DEV_MODE ? time() : false;

	/*wp_register_style('google-font', 'https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap', array(), null);
    wp_enqueue_style('google-font');*/

	wp_register_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/css/bootstrap.min.css', array(), null);
    wp_enqueue_style('bootstrap');

	wp_register_style('bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.0/font/bootstrap-icons.css', array(), null);
    wp_enqueue_style('bootstrap-icons');

	wp_register_style('gsg-style', "$uri/style.css", array(), $version);
	wp_enqueue_style('gsg-style');

	wp_register_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta2/dist/js/bootstrap.bundle.min.js', array('jquery'), null, true);
	wp_enqueue_script('bootstrap');

	wp_register_script('gsg-script', "$uri/script.js", array('jquery'), $version, true);

	/*global $wpdb;

	$entry_locations = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}entry_locations`");

	wp_localize_script('gsg-script', 'gsg', array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'home_url' => home_url('/'),
		'join_page_url' => get_permalink(62),
		'congratulations_page_url' => get_permalink(64),
        'entry_locations' => $entry_locations,
        'registration_closed' => get_option('gsg_close_registration')
    ));*/

	wp_enqueue_script('gsg-script');
}

add_action('wp_enqueue_scripts', 'gsg_enqueue');
