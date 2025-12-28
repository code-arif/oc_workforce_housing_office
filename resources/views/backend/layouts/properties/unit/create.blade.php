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
                        <div class="col-12 mb-3">
                            <label for="name" class="form-label">Property Name <span
                                    class="text-danger">*</span></label>
                            @php
                                $properties = App\Models\Property::where('is_active',1)->get();
                            @endphp

                            <select name="property_id" id="property_id" class="form-control select3 @error('property_id') is-invalid @enderror">
                                <option value="">-- Select Property --</option>
                                @foreach ($properties as $property)
                                    <option value="{{ $property->id }}" {{ old('property_id') == $property->id ? 'selected' : '' }}>{{ $property->name }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger error-text name_error"></span>
                        </div>
                        <!-- Name -->
                        <div class="col-12 mb-3">
                            <label for="name" class="form-label">Unit Name / Unit Number <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control"
                                placeholder="Enter Unit name or unit number" required>
                            <span class="text-danger error-text name_error"></span>
                        </div>

                        <!-- Gender Designation -->
                        <div class="col-12 mb-3">
                            <label for="gender_designation" class="form-label">Gender Designation (optional)</label>
                            <select id="gender_designation" name="gender_designation"
                                class="form-control @error('gender_designation') is-invalid @enderror">
                                <option value="">-- Select --</option>
                                <option value="male" {{ old('gender_designation') == 'male' ? 'selected' : '' }}> Male</option>
                                <option value="female" {{ old('gender_designation') == 'female' ? 'selected' : '' }}> Female</option>
                                
                            </select>
                            @error('gender_designation')
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