<div id="update-session-modal" class="modal fade">
    <div class="modal-dialog modal-sm modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update session</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row mb-2">
                    <div class="col mb-2">
                        <label for="edit-start-time" class="mb-1 text-muted">Start time</label>
                        <input type="time" id="edit-start-time" class="form-control" required>
                    </div>

                    <div class="col">
                        <label for="edit-end-time" class="mb-1 text-muted">End time</label>
                        <input type="time" id="edit-end-time" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <input type="hidden" id="edit-class-id" required>
                <input type="hidden" id="edit-session-id" required>

                <button class="btn btn-primary position-relative"><i class="fa-li fa fa-circle-o-notch fa-spin d-none position-relative start-0 top-0"></i> <span>Save changes</span></button>
            </div>
        </form>
    </div>
</div>