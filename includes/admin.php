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

function gsg_custom_record_columns($columns) {
    //unset($columns['date']);
    unset($columns['categories']);

    $columns['class'] = 'Class Code';
    $columns['teacher'] = 'Teacher';
    $columns['student'] = 'Student';
    $columns['category'] = 'Category';
    $columns['type'] = 'Type';
    $columns['score'] = 'Score';
    $columns['total_score'] = 'Total Score';

    return $columns;
}

add_filter('manage_record_posts_columns', 'gsg_custom_record_columns');

function gsg_record_columns_data($column, $post_id) {
    switch ($column) {
        case 'class':
            $class = get_post(get_field('class'));  
            echo '<a href="' . get_edit_post_link($class->ID) . '" target="_blank" rel="noreferrer">' . $class->post_title . '</a>';
            break;
        case 'teacher':
            $teacher = get_user_by('ID', get_field('teacher'));
            echo '<a href="' . get_edit_user_link($teacher->ID) . '" target="_blank" rel="noreferrer">' . $teacher->display_name . '</a>';
            break;
        case 'student':
            $student = get_user_by('ID', get_field('student'));
            echo '<a href="' . get_edit_user_link($student->ID) . '" target="_blank" rel="noreferrer">' . $student->display_name . '</a>';
            break;
        case 'category':
            $category = get_category(get_field('category'));
            echo '<a href="' . get_edit_term_link($category->term_id) . '" target="_blank" rel="noreferrer">' . $category->name . '</a>';
            break;
        case 'type':
            $type = get_field('type');
            echo ucwords(str_replace('-', ' ', $type));
            break;
        case 'score':
            echo get_field('score');
            break;
        case 'total_score':
            echo get_field('total_score');
            break;
        default:
            break;
    }
}

add_action('manage_posts_custom_column' , 'gsg_record_columns_data', 10, 2);

function gsg_admin_init_entry_type() {
    global $typenow;
 
    if ($typenow === 'class') {
        add_filter('posts_search', 'gsg_classes_search_entry_type', 10, 2);
    }
}

add_action('admin_init', 'gsg_admin_init_entry_type');
 
function gsg_classes_search_entry_type($search, $query) {
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

function gsg_custom_admin_filters() {
    global $wpdb, $table_prefix;

    $post_type = (isset($_GET['post_type'])) ? trim($_GET['post_type']) : 'post';

    if ($post_type == 'record') {
        global $wpdb;

        $teachers = array();
        $students = array();

        $types = array(
            'Quiz' => 'quiz',
            'Exam' => 'exam'
        );

        $categories = array();

        $get_teachers = get_users(array(
            'role' => 'teacher',
            'orderby' => 'display_name',
            'order' => 'ASC'
        ));

        $get_students = get_users(array(
            'role' => 'student',
            'orderby' => 'display_name',
            'order' => 'ASC'
        ));

        $get_categories = $wpdb->get_results("SELECT term_id, name, slug FROM $wpdb->terms ORDER BY name ASC");

        foreach ($get_teachers as $teacher) {
            $teachers[$teacher->display_name] = $teacher->ID;
        }

        foreach ($get_students as $student) {
            $students[$student->display_name] = $student->ID;
        }

        foreach ($get_categories as $category) {
            $skip_ids = array(1, 2, 8, 9, 10);

            if (in_array($category->term_id, $skip_ids)) {
                continue;
            }

            $categories[$category->name] = $category->term_id;
        }

        $current_teacher = isset($_GET['filter_teacher'])? trim($_GET['filter_teacher']) : '';
        $current_student = isset($_GET['filter_student'])? trim($_GET['filter_student']) : '';
        $current_type = isset($_GET['filter_type'])? trim($_GET['filter_type']) : '';
        $current_category = isset($_GET['filter_category'])? trim($_GET['filter_category']) : '';

        echo '<select name="filter_teacher">';
            echo '<option value="">All teachers</option>';

            foreach ($teachers as $label => $value) {
                printf(
                    '<option value="%d"%s>%s</option>',
                    $value,
                    $value == $current_teacher ? ' selected="selected"' : '',
                    $label
                );
            }
        echo '</select>';

        echo '<select name="filter_student">';
            echo '<option value="">All students</option>';

            foreach ($students as $label => $value) {
                printf(
                    '<option value="%d"%s>%s</option>',
                    $value,
                    $value == $current_student ? ' selected="selected"' : '',
                    $label
                );
            }
        echo '</select>';

        echo '<select name="filter_category">';
            echo '<option value="">All categories</option>';

            foreach ($categories as $label => $value) {
                printf(
                    '<option value="%s"%s>%s</option>',
                    $value,
                    $value == $current_category ? ' selected="selected"' : '',
                    $label
                );
            }
        echo '</select>';

        echo '<select name="filter_type">';
            echo '<option value="">All types</option>';

            foreach ($types as $label => $value) {
                printf(
                    '<option value="%s"%s>%s</option>',
                    $value,
                    $value == $current_type ? ' selected="selected"' : '',
                    $label
                );
            }
        echo '</select>';
    }

    if ($post_type == 'class') {
        $teachers = array();

        $get_teachers = get_users(array(
            'role' => 'teacher',
            'orderby' => 'display_name',
            'order' => 'ASC'
        ));

        foreach ($get_teachers as $teacher) {
            $teachers[$teacher->display_name] = $teacher->ID;
        }

        $current_teacher = isset($_GET['filter_teacher'])? trim($_GET['filter_teacher']) : '';

        echo '<select name="filter_teacher">';
            echo '<option value="">All teachers</option>';

            foreach ($teachers as $label => $value) {
                printf(
                    '<option value="%d"%s>%s</option>',
                    $value,
                    $value == $current_teacher ? ' selected="selected"' : '',
                    $label
                );
            }
        echo '</select>';
    }
}

add_action('restrict_manage_posts', 'gsg_custom_admin_filters');

function gsg_custom_admin_filters_parse_query($query) {
    global $post_type, $pagenow;

    if (isset($_GET['action']) && $_GET['action'] != 'edit') {
        $post_type = (isset($_GET['post_type'])) ? trim($_GET['post_type']) : 'post';

        if ($post_type == 'record' && $pagenow == 'edit.php') {
            if (isset($_GET['filter_teacher']) && !empty($_GET['filter_teacher'])) {
                $query->query_vars['meta_key'] = 'teacher';
                $query->query_vars['meta_value'] = $_GET['filter_teacher'];
            }
        
            if (isset($_GET['filter_student']) && !empty($_GET['filter_student'])) {
                $query->query_vars['meta_key'] = 'student';
                $query->query_vars['meta_value'] = $_GET['filter_student'];
            }

            if (isset($_GET['filter_type']) && !empty($_GET['filter_type'])) {
                $query->query_vars['meta_key'] = 'type';
                $query->query_vars['meta_value'] = $_GET['filter_type'];
            }

            if (isset($_GET['filter_category']) && !empty($_GET['filter_category'])) {
                $query->query_vars['meta_key'] = 'category';
                $query->query_vars['meta_value'] = $_GET['filter_category'];
            }
        }

        if ($post_type == 'class' && $pagenow == 'edit.php') {
            if (isset($_GET['filter_teacher']) && !empty($_GET['filter_teacher'])) {
                $query->query_vars['author'] = $_GET['filter_teacher'];
            }
        }
    }
}

add_filter('parse_query', 'gsg_custom_admin_filters_parse_query');

function gsg_custom_admin_head() {
    global $current_user;

    if ($current_user->ID != 2) {
        echo '<style type="text/css">
            #adminmenu #menu-appearance,
            #adminmenu #menu-pages,
            #adminmenu #menu-plugins,
            #adminmenu #menu-tools,
            #adminmenu #menu-settings,
            #adminmenu #toplevel_page_duplicator,
            #adminmenu #toplevel_page_cptui_main_menu,
            #adminmenu #toplevel_page_edit-post_type-acf-field-group,
            #adminmenu #menu-dashboard .wp-submenu li:last-of-type {
                display: none;
            }
        </style>';        
    }

    echo '<style type="text/css">
        .post-type-record #posts-filter #cat,
        .post-type-record #posts-filter #author {
            display: none;
        }
    </style>';
}

add_action('admin_head', 'gsg_custom_admin_head');