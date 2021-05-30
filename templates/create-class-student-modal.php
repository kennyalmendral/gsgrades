<div id="create-class-student-modal" class="modal fade">
    <div class="modal-dialog modal-sm modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add student</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row mb-2">
                    <div class="col-12">
                        <label for="class-student" class="mb-1 text-muted">Student</label>
                        
                        <select id="class-student" class="form-select" required>
                            <option value="">Select student</option>

                            <?php foreach ($all_students as $student): ?>
                                <option value="<?php echo $student->ID; ?>"><?php echo $student->display_name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-12">
                        <label for="days-present" class="mb-1 text-muted">Days present</label>
                        
                        <input type="number" id="days-present" min=0 class="form-control" required></input>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-12">
                        <label for="status" class="mb-1 text-muted">Status</label>
                        
                        <select id="status" class="form-select" required>
                            <option value="">Select status</option>
                            <option value="active">Active</option>
                            <option value="dropped">Dropped</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary position-relative"><i class="fa-li fa fa-circle-o-notch fa-spin d-none position-relative start-0 top-0"></i> <span>Submit</span></button>
            </div>
        </form>
    </div>
</div>