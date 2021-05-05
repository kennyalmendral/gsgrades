<?php

if (!is_user_logged_in()) {
    wp_redirect(LOGIN_PAGE_URL);
    exit;
}

global $post;
global $wpdb;

$level = get_field('level', $post->ID);
$completion_hours = get_field('completion_hours', $post->ID);
$completed_hours = get_field('completed_hours', $post->ID);
$remaining_hours = get_field('remaining_hours', $post->ID);

$sessions = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}class_sessions WHERE class_id = %d ORDER BY created_at ASC", $post->ID));

get_header();

?>

<div id="page-title" class="bg-white shadow-sm text-center text-sm-start py-3 py-sm-3 mb-3 mb-sm-0">
    <div class="container">
        <div class="row">
            <div class="col">
                <div class="d-flex flex-row align-items-center justify-content-between mb-0">
                    <h1 class="p-0 mb-0 fs-2">Manage <?php the_title(); ?></h1>

                    <button name="generate_class_summary" id="generate-class-summary" class="btn btn-outline-secondary"><i class="bi bi-file-earmark-ruled"></i> Generate class summary</button>
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
                                    <th class="text-center">Action</th>
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
                                            <button class="edit-session-button btn btn-outline-primary btn-sm me-1" title="Edit session" data-session-id="<?php echo esc_attr($session->id); ?>" data-session-class-id="<?php echo esc_attr($session->class_id); ?>" data-session-start-time="<?php echo esc_attr($session->start_time); ?>" data-session-end-time="<?php echo esc_attr($session->end_time); ?>" data-session-total-hours="<?php echo esc_attr($session->total_hours); ?>"><i class="bi bi-pencil d-none"></i><i class="bi bi-pencil-fill"></i></button>

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
                    <button id="create-session" class="btn btn-secondary">Create new session</button>
                </div>
            </div>

            <div class="card bg-white shadow-sm">
                <div class="card-header">
                    <h4 class="mb-0">Records</h4>
                </div>

                <div class="card-body">
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'templates/create-session-modal.php'; ?>
<?php include_once 'templates/update-session-modal.php'; ?>

<?php get_footer(); ?>