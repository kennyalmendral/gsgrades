<?php

function gsg_custom_class_columns($columns) {
    //unset($columns['date']);
    $columns['level'] = 'Level';
    $columns['completion_hours'] = '# of hours to complete';
    $columns['completed_hours'] = 'Completed hours';
    $columns['remaining_hours'] = 'Remaining hours';

    return $columns;
}

add_filter('manage_class_posts_columns', 'gsg_custom_class_columns');

function gsg_class_columns_data($column, $post_id) {
    switch ($column) {
        case 'level':
            echo get_field('level');
            break;
        case 'completion_hours':
            echo get_field('completion_hours');
            break;
        case 'completed_hours':
            echo get_field('completed_hours');
            break;
        case 'remaining_hours':
            echo get_field('remaining_hours');
            break;
        default:
            break;
    }
}

add_action('manage_posts_custom_column' , 'gsg_class_columns_data', 10, 2);

function gsg_admin_init_entry_type() {
    global $typenow;
 
    if ($typenow === 'class') {
        add_filter('posts_search', 'gsg_posts_search_entry_type', 10, 2);
    }
}
 
function gsg_posts_search_entry_type($search, $query) {
    global $wpdb;
 
    if ($query->is_main_query() && !empty($query->query['s'])) {
        $sql = "
            or EXISTS (
                SELECT * FROM {$wpdb->postmeta} WHERE post_id={$wpdb->posts}.ID
                AND meta_key IN ('level', 'completion_hours', 'completed_hours', 'remaining_hours')
                AND meta_value LIKE %s
            )
        ";

        $like = '%' . $wpdb->esc_like($query->query['s']) . '%';

        $search = preg_replace("#\({$wpdb->posts}.post_title LIKE [^)]+\)\K#", $wpdb->prepare($sql, $like), $search);
    }
 
    return $search;
}

add_action('admin_init', 'gsg_admin_init_entry_type');

function gsg_filter_by_the_author() {
    $params = array(
		'name' => 'author',
		'show_option_all' => 'All authors'
	);
 
	if (isset($_GET['user'])) {
		$params['selected'] = $_GET['user'];
    }
 
	wp_dropdown_users($params);
}

add_action('restrict_manage_posts', 'gsg_filter_by_the_author');
