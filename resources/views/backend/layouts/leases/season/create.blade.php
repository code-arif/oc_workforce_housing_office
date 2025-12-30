{{-- Property Type Create/Edit Modal --}}
<div class="modal fade" id="seasonModal" tabindex="-1" aria-labelledby="seasonModalLabel" aria-hidden="true">
    <div class="modal-dialog ">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Season</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="seasonForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="seasonId">
                <div class="modal-body">
                    <div class="row">
                        <!-- Name -->
                        <div class="col-12 mb-3">
                            <label for="name" class="form-label">Season Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control"
                                placeholder="Enter season name " required>
                            <span class="text-danger error-text name_error"></span>
                        </div>

                        <!-- Gender Designation -->
                        <div class="col-12 col-md-6 col-lg-6 col-xl-6 mb-3">
                            <label for="blanket_start_date" class="form-label"> Start Date</label>
                            <input type="text" class="form-control datepicker2" name="blanket_start_date" id="blanket_start_date" placeholder="Enter start date" required>
                            @error('blanket_start_date')
                                <div class="invalid-feedback" style="display: block;">
                                    <i class="bi bi-exclamation-circle"></i> {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-6 col-lg-6 col-xl-6 mb-3">
                            <label for="blanket_end_date" class="form-label"> End Date</label>
                            <input type="text" class="form-control datepicker2" name="blanket_end_date" id="blanket_end_date" placeholder="Enter End date" required>
                            @error('blanket_end_date')
                                <div class="invalid-feedback" style="display: block;">
                                    <i class="bi bi-exclamation-circle"></i> {{ $message }}
                                </div>
                            @enderror
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