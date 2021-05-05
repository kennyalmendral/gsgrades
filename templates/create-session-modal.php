<div id="create-session-modal" class="modal fade">
    <div class="modal-dialog modal-sm modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create new session</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row mb-2">
                    <div class="col mb-2">
                        <label for="start-time" class="mb-1 text-muted">Start time</label>
                        <input type="time" id="start-time" class="form-control" required>
                    </div>

                    <div class="col">
                        <label for="end-time" class="mb-1 text-muted">End time</label>
                        <input type="time" id="end-time" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary position-relative"><i class="fa-li fa fa-circle-o-notch fa-spin d-none position-relative start-0 top-0"></i> <span>Submit</span></button>
            </div>
        </form>
    </div>
</div>
