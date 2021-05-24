<?php

if (!is_user_logged_in()) {
    wp_redirect(LOGIN_PAGE_URL);
    exit;
}

global $post, $wpdb, $current_user;

$level = get_field('level', $post->ID);
$completion_hours = intval(get_field('completion_hours', $post->ID));
$completed_hours = intval(get_field('completed_hours', $post->ID));
$remaining_hours = intval(get_field('remaining_hours', $post->ID));

$sessions = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}class_sessions WHERE class_id = %d ORDER BY created_at ASC", $post->ID));

$all_students = get_users(array(
    'role' => 'student',
    'orderby' => 'display_name',
    'order' => 'ASC'
));

$record_query = new WP_Query(array(
    'post_type' => 'record',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'author' => $current_user->ID
));

$students = array();

if (!empty($record_query->posts)) {
    foreach ($record_query->posts as $record) {
        $students[intval(get_field('student', $record->ID))] = get_user_by('ID', get_field('student', $record->ID))->display_name;
    }
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
                <div class="d-flex flex-row align-items-center justify-content-between mb-0">
                    <?php if ($remaining_hours <= 0): ?>
                        <h1 class="p-0 mb-0 fs-2">Manage <?php the_title(); ?> <span class="badge bg-success fs-6" style="position: relative; top: -6px; margin-left: 5px;">Completed</span></h1>
                        <button name="generate_report" id="generate-report" class="btn btn-outline-secondary "><i class="bi bi-file-earmark-ruled"></i> <span>Generate report</span></button>
                    <?php else: ?>
                        <h1 class="p-0 mb-0 fs-2">Manage <?php the_title(); ?> <span class="badge bg-secondary fs-6" style="position: relative; top: -6px; margin-left: 5px;">Ongoing</span></h1>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="main-content" class="container my-sm-5">
    <div class="row flex-row-reverse align-items-start">
        <input type="hidden" id="class-id" value="<?php echo $post->ID; ?>">

        <div id="details" class="col-12 col-lg-4">
            <div class="card bg-white shadow-sm mb-4">
                <div class="card-header">
                    <h4 class="mb-0">Details</h4>
                </div>

                <div class="card-body">
                    <div class="mb-2">
                        <label for="level" class="form-label text-muted">Level</label>
                        <input type="text" id="level" class="form-control" value="<?php echo esc_attr($level); ?>" required />
                    </div>

                    <div class="mb-2">
                        <label for="completion-hours" class="form-label text-muted">Number of hours to complete</label>
                        <input type="number" min=0 id="completion-hours" class="form-control" value="<?php echo esc_attr($completion_hours); ?>" required />
                    </div>

                    <div class="row mb-2">
                        <div class="col-12 col-md-6">
                            <label for="completed-hours" class="form-label text-muted">Completed hours</label>
                            <input type="number" min=0 id="completed-hours" class="form-control" value="<?php echo esc_attr($completed_hours); ?>" disabled />
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="remaining-hours" class="form-label text-muted">Remaining hours</label>
                            <input type="number" min=0 id="remaining-hours" class="form-control" value="<?php echo esc_attr($remaining_hours); ?>" disabled />
                        </div>
                    </div>
                </div>

                <div class="card-footer py-3">
                    <button id="save-changes" class="btn btn-primary position-relative"><i class="fa-li fa fa-circle-o-notch fa-spin d-none position-relative start-0 top-0"></i> <span>Save changes</span></button>
                </div>
            </div>
        </div>

        <div id="sessions" class="col-12 col-lg-8">
            <div class="card bg-white shadow-sm mb-4">
                <div class="card-header">
                    <div class="d-flex flex-row align-items-center justify-content-between mb-0">
                        <h4 class="mb-0">Sessions</h4>
                    </div>
                </div>

                <div class="card-body pb-2">
                    <?php if (count($sessions) > 0): ?>
                        <table class="table table-borderless">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th class="text-center">Start time</th>
                                    <th class="text-center">End time</th>
                                    <th class="text-center">Total hours</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($sessions as $session): ?>
                                    <tr>
                                        <td valign="middle"><?php echo date('F d, Y', strtotime($session->created_at)); ?></td>
                                        <td valign="middle" class="text-center"><?php echo date('g:i A', strtotime($session->start_time)); ?></td>
                                        <td valign="middle" class="text-center"><?php echo date('g:i A', strtotime($session->end_time)); ?></td>
                                        <td valign="middle" class="text-center"><?php echo $session->total_hours; ?></td>
                                        <td valign="middle" class="text-center">
                                            <button class="edit-session-button btn btn-outline-primary btn-sm me-1" title="Edit session" data-session-id="<?php echo esc_attr($session->id); ?>" data-session-class-id="<?php echo esc_attr($session->class_id); ?>" data-session-start-time="<?php echo esc_attr($session->start_time); ?>" data-session-end-time="<?php echo esc_attr($session->end_time); ?>" data-session-total-hours="<?php echo esc_attr($session->total_hours); ?>" <?php echo $remaining_hours <= 0 ? 'disabled' : '' ; ?>><i class="bi bi-pencil d-none"></i><i class="bi bi-pencil-fill"></i></button>

                                            <button class="delete-session-button btn btn-outline-danger btn-sm" title="Delete session" data-session-id="<?php echo esc_attr($session->id); ?>" data-class-id="<?php echo esc_attr($session->class_id); ?>"><i class="bi bi-trash d-none"></i><i class="bi bi-trash-fill"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="mb-0 text-muted text-center py-4">There are no sessions created yet.</p>
                    <?php endif; ?>
                </div>

                <div class="card-footer py-3">
                    <button id="create-session" class="btn btn-secondary" <?php echo $remaining_hours <= 0 ? 'disabled' : '' ; ?>>Create new session</button>
                </div>
            </div>
        </div>
    </div>

    <div id="records" class="row align-items-start">
        <div class="col-12">
            <div class="card bg-white shadow-sm">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h4 class="mb-0">Records</h4>
                    <button id="create-record" class="btn btn-secondary">Create new record</button>
                </div>

                <div class="card-body">
                    <div id="student-filter" class="d-flex align-items-center me-2">
                        <select id="student-filter-select" class="form-select form-select-sm fs-6">
                            <option value="">Select student</option>

                            <?php if (!empty($students)): ?>
                                <?php foreach ($students as $id => $display_name): ?>
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

                    <table id="records-table" class="table table-striped table-responsive w-100">
                        <thead>
                            <tr>
                                <th width="6%">ID</th>
                                <th width="15%">Student</th>
                                <th width="15%">Category</th>
                                <th width="8%">Type</th>
                                <th width="8%">Score</th>
                                <th width="10%">Total score</th>
                                <th width="15%">Date created</th>
                                <th width="15%">Last updated</th>
                                <th width="20%" class="text-center">Actions</th>
                            </tr>
                        </thead>

                        <tfoot>
                            <tr>
                                <th width="6%">ID</th>
                                <th width="15%">Student</th>
                                <th width="15%">Category</th>
                                <th width="8%">Type</th>
                                <th width="8%">Score</th>
                                <th width="10%">Total score</th>
                                <th width="15%">Date created</th>
                                <th width="15%">Last updated</th>
                                <th width="20%" class="text-center">Actions</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include_once 'templates/create-session-modal.php'; ?>
<?php include_once 'templates/update-session-modal.php'; ?>
<?php include_once 'templates/create-record-modal.php'; ?>
<?php include_once 'templates/update-record-modal.php'; ?>

<?php get_footer(); ?>