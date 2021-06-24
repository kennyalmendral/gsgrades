<?php

if (!is_user_logged_in()) {
    wp_redirect(LOGIN_PAGE_URL);
    exit;
}

$get_teachers = get_users(array(
    'role' => 'teacher',
    'orderby' => 'display_name',
    'order' => 'ASC'
));

$teachers = array();

foreach ($get_teachers as $teacher) {
    $teachers[$teacher->ID] = $teacher->display_name;
}

$get_categories = $wpdb->get_results("SELECT term_id, name, slug FROM $wpdb->terms WHERE term_id NOT IN(1, 2, 8, 9, 10) ORDER BY name ASC");
$categories = array();

foreach ($get_categories as $category) {
    $categories[$category->term_id] = $category->name;
}

get_header();

?>

<div id="page-title" class="bg-white shadow-sm text-center text-sm-start py-3 py-sm-3 mb-3 mb-sm-0">
    <div class="container">
        <div class="row">
            <div class="col">
                <h1 class="p-0 mb-0 fs-2">Grades</h1>
            </div>
        </div>
    </div>
</div>

<div class="container my-sm-5">
    <div id="main-content" class="bg-white shadow-sm p-4 rounded d-none">
        <div id="teacher-filter" class="d-flex align-items-center me-2">
            <select id="teacher-filter-select" class="form-select form-select-sm fs-6">
                <option value="">Select teacher</option>

                <?php if (!empty($teachers)): ?>
                    <?php foreach ($teachers as $id => $display_name): ?>
                        <option value="<?php echo esc_attr($id); ?>"><?php echo $display_name; ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div id="category-filter" class="d-flex align-items-center me-2">
            <select id="category-filter-select" class="form-select form-select-sm fs-6">
                <option value="">Select category</option>

                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $id => $name): ?>
                        <option value="<?php echo esc_attr($id); ?>"><?php echo $name; ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>

        <div id="type-filter" class="d-flex align-items-center me-2">
            <select id="type-filter-select" class="form-select form-select-sm fs-6">
                <option value="">Select type</option>
                <option value="quiz">Quiz</option>
                <option value="exam">Exam</option>
            </select>
        </div>

        <table id="grades-table" class="table table-striped table-responsive w-100">
            <thead>
                <tr>
                    <th width="8%">ID</th>
                    <th width="12%">Class Code</th>
                    <th width="10%">Teacher</th>
                    <th width="13%">Category</th>
                    <th width="7%">Type</th>
                    <th width="7%">Score</th>
                    <th width="10%">Date created</th>
                    <th width="10%">Last updated</th>
                    <!-- <th width="15%"class="text-center">Actions</th> -->
                </tr>
            </thead>

            <tfoot>
                <tr>
                    <th width="8%">ID</th>
                    <th width="12%">Class Code</th>
                    <th width="10%">Teacher</th>
                    <th width="13%">Category</th>
                    <th width="7%">Type</th>
                    <th width="7%">Score</th>
                    <th width="10%">Date created</th>
                    <th width="10%">Last updated</th>
                    <!-- <th width="15%"class="text-center">Actions</th> -->
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<?php include_once 'templates/view-student-class-grades.php'; ?>

<?php get_footer(); ?>
