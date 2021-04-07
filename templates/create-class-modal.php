<div id="create-class-modal" class="modal fade">
    <div class="modal-dialog modal-sm modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create new class</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="col">
                    <label for="completion-hours" class="mb-1 text-muted">Number of hours to complete</label>
                    <input type="number" id="completion-hours" class="form-control" required>
                </div>
            </div>

            <div class="modal-footer">
                <?php wp_nonce_field('gsg_create_class', 'gsg_create_class_nonce_field'); ?>

                <button class="btn btn-primary position-relative"><i class="fa-li fa fa-circle-o-notch fa-spin d-none position-relative start-0 top-0"></i> <span>Submit</span></button>
            </div>
        </form>
    </div>
</div>
