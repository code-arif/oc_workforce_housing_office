{{-- Property Type Create/Edit Modal --}}
<div class="modal fade" id="unitModal" tabindex="-1" aria-labelledby="unitModalLabel" aria-hidden="true">
    <div class="modal-dialog ">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="unitForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="unitId">
                <div class="modal-body">

                    <div class="row">
                        <!-- Name -->
                        <div class="col-12 mb-3">
                            <label for="name" class="form-label">Unit Name / Unit Number <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control"
                                placeholder="Enter Unit name or unit number" required>
                            <span class="text-danger error-text name_error"></span>
                        </div>

                        <!-- Description -->
                        <div class="col-12 mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" 
                                placeholder="Enter Unit description" rows="4"></textarea>
                            <span class="text-danger error-text description_error"></span>
                        </div>

                        <!-- Is Active -->
                        <div class="col-12 mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="isActive" name="is_active" value="1" checked>
                                <label class="form-check-label" for="isActive">
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>