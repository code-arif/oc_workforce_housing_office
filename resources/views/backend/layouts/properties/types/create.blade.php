{{-- Property Type Create/Edit Modal --}}
<div class="modal fade" id="propertyTypeModal" tabindex="-1" aria-labelledby="propertyTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Property Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="propertyTypeForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="propertyTypeId">
                <div class="modal-body">

                    <div class="row">
                        <!-- Name -->
                        <div class="col-12 mb-3">
                            <label for="name" class="form-label">Property Type Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control"
                                placeholder="Enter property type name" required>
                            <span class="text-danger error-text name_error"></span>
                        </div>

                        <!-- Slug -->
                        {{-- <div class="col-12 mb-3">
                            <label for="slug" class="form-label">Slug</label>
                            <input type="text" id="slug" name="slug" class="form-control"
                                placeholder="Enter slug (auto-generated if empty)">
                            <small class="form-text text-muted">Leave empty to auto-generate from name</small>
                            <span class="text-danger error-text slug_error"></span>
                        </div> --}}

                        <!-- Description -->
                        <div class="col-12 mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" 
                                placeholder="Enter property type description" rows="4"></textarea>
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
                    <button type="submit" class="btn btn-primary" id="submitBtn">Add Property Type</button>
                </div>
            </form>
        </div>
    </div>
</div>