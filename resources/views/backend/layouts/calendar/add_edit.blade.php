<div class="modal fade" id="createWorkModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">
                    <i class="fas fa-calendar-plus me-2"></i>Create New Work Schedule
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="workForm">
                    <input type="hidden" id="workId" name="work_id">
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">

                    <div class="row">
                        <!-- NEW: Calendar Selection -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">
                                <i class="fas fa-calendar"></i> Calendar *
                            </label>
                            <select class="form-select" name="calendar_id" id="calendar_id" required>
                                <option value="">Select Calendar</option>
                                <!-- Will be populated by JavaScript -->
                            </select>
                            <span class="text-danger error-text calendar_id_error"></span>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">
                                <i class="fas fa-heading"></i> Title *
                            </label>
                            <input type="text" class="form-control form-control-sm" name="title" id="title"
                                required>
                            <span class="text-danger error-text title_error"></span>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">
                                <i class="fas fa-align-left"></i> Description
                            </label>
                            <textarea class="form-control" name="description" id="description" rows="3"></textarea>
                        </div>

                        {{-- Work Date --}}
                        <div class="col-md-3 mt-3">
                            <label class="form-label">Work Date *</label>
                            <input type="date" class="form-control form-control-sm" name="work_date" id="work_date"
                                required>
                            <span class="text-danger error-text work_date_error"></span>
                        </div>

                        {{-- Start Time --}}
                        <div class="col-md-3 mt-3" id="start_time_wrapper">
                            <label class="form-label">Start Time</label>
                            <input type="text" class="form-control form-control-sm timepicker" name="start_time"
                                id="start_time" placeholder="02:30 PM">
                            <span class="text-danger error-text start_time_error"></span>
                        </div>

                        {{-- End Time --}}
                        <div class="col-md-3 mt-3" id="end_time_wrapper">
                            <label class="form-label">End Time</label>
                            <input type="text" class="form-control form-control-sm timepicker" name="end_time"
                                id="end_time" placeholder="04:30 PM">
                            <span class="text-danger error-text end_time_error"></span>
                        </div>

                        <!-- All Day Checkbox -->
                        <div class="col-md-3 mt-3">
                            <label class="form-label form-switch">All Day</label>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input custom-checkbox" name="is_all_day"
                                    id="is_all_day" value="1">
                                <label class="form-check-label" for="is_all_day">All Day Event</label>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">
                                <i class="fas fa-map-marker-alt"></i> Location
                            </label>
                            <input type="text" class="form-control" name="location" id="location"
                                placeholder="Search for a location...">
                        </div>

                        {{-- team and category selection --}}
                        <div class="form-section">
                            <div class="col-md-6 equal-box">
                                <label class="form-label">
                                    <i class="fas fa-users"></i> Team
                                </label>
                                <select class="form-select" name="team_id" id="team_id">
                                    <option value="">Select Team</option>
                                    @foreach ($teams as $team)
                                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 equal-box">
                                <label class="form-label">
                                    <i class="fas fa-tag"></i> Category
                                </label>
                                <select class="form-select" name="category_id" id="category_id">
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <input type="text" name="category_name" id="category_name"
                                    class="form-control mt-2" placeholder="Or create new category">
                            </div>
                        </div>

                        <!-- Note Field -->
                        <div class="col-md-12 mt-3">
                            <label class="form-label">
                                <i class="fas fa-sticky-note"></i> Note
                            </label>
                            <textarea class="form-control" name="note" id="note" rows="2" placeholder="Additional notes..."></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-primary" id="saveWorkBtn">
                    <i class="fas fa-save me-2"></i>Save
                </button>
            </div>
        </div>
    </div>
</div>

{{-- checkbox style --}}
<style>
    /* Make checkbox bigger */
    .custom-checkbox {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }

    /* Change check color */
    .custom-checkbox:checked {
        background-color: #13bfa6;
        border-color: #13bfa6;
    }

    /* Optional: adjust label alignment */
    .form-check-label {
        font-size: 16px;
        padding-left: 10px;
        padding-top: 4px;
    }
</style>
