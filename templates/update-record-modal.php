<div id="update-record-modal" class="modal fade">
    <div class="modal-dialog modal-sm modal-dialog-centered modal-dialog-scrollable">
        <form class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row mb-2">
                    <div class="col-12">
                        <label for="edit-student" class="mb-1 text-muted">Student</label>
                        
                        <select id="edit-student" class="form-select" required>
                            <option value="">Select student</option>

                            <?php foreach ($all_students as $student): ?>
                                <option value="<?php echo $student->ID; ?>"><?php echo $student->display_name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-12">
                        <label for="edit-category" class="mb-1 text-muted">Category</label>
                        
                        <select id="edit-category" class="form-select" required>
                            <option value="">Select category</option>

                            <?php foreach ($categories as $id => $display_name): ?>
                                <option value="<?php echo $id; ?>"><?php echo $display_name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-12">
                        <label for="edit-type" class="mb-1 text-muted">Type</label>
                        
                        <select id="edit-type" class="form-select" required>
                            <option value="">Select type</option>
                            <option value="quiz">Quiz</option>
                            <option value="exam">Exam</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-2">
                    <div class="col-12">
                        <label for="edit-score" class="mb-1 text-muted">Score</label>
                        <input type="number" min=0 id="edit-score" class="form-control" value="0" required>
                    </div>
                </div>
                
                <div class="row mb-2">
                    <div class="col-12">
                        <label for="edit-total-score" class="mb-1 text-muted">Total score</label>
                        <input type="number" min=0 id="edit-total-score" class="form-control" value="0" required>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary position-relative"><i class="fa-li fa fa-circle-o-notch fa-spin d-none position-relative start-0 top-0"></i> <span>Save changes</span></button>
            </div>
        </form>
    </div>
</div>