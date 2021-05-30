<div id="update-class-student-modal" class="modal fade">
    <div class="modal-dialog modal-sm modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row mb-2">
                    <div class="col-12">
                        <label for="edit-class-student" class="mb-1 text-muted">Student</label>
                        
                        <select id="edit-class-student" class="form-select" required disabled>
                            <option value="">Select student</option>

                            <?php foreach ($all_students as $student): ?>
                                <option value="<?php echo $student->ID; ?>"><?php echo $student->display_name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-12">
                        <label for="edit-days-present" class="mb-1 text-muted">Days present</label>
                        
                        <input type="number" id="edit-days-present" min=0 class="form-control" required></input>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-12">
                        <label for="edit-status" class="mb-1 text-muted">Status</label>
                        
                        <select id="edit-status" class="form-select" required>
                            <option value="">Select status</option>
                            <option value="active">Active</option>
                            <option value="dropped">Dropped</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <input type="hidden" id="edit-class-student-id" value="" required />

                <button class="btn btn-primary position-relative"><i class="fa-li fa fa-circle-o-notch fa-spin d-none position-relative start-0 top-0"></i> <span>Save changes</span></button>
            </div>
        </form>
    </div>
</div>