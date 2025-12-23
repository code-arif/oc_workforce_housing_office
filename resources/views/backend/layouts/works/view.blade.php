{{-- Work View Modal --}}
<div class="modal fade" id="viewWorkModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary bg-opacity-10">
                <h5 class="modal-title text-white">
                    <i class="fas fa-info-circle me-2"></i>Work Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="workDetailsContent">
                {{-- Loading Spinner --}}
                <div id="workDetailsLoading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading work details...</p>
                </div>

                {{-- Work Details Content --}}
                <div id="workDetailsData" style="display: none;">
                    <div class="row g-3">
                        {{-- Title --}}
                        <div class="col-12">
                            <div class="d-flex align-items-start">
                                <strong class="me-3" style="min-width: 130px;">
                                    <i class="fa fa-heading text-primary"></i> Title:
                                </strong>
                                <span id="view_work_title" class="fw-semibold text-dark"></span>
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="col-12" id="view_description_wrapper" style="display: none;">
                            <div class="d-flex align-items-start">
                                <strong class="me-3" style="min-width: 130px;">
                                    <i class="fa fa-align-left text-info"></i> Description:
                                </strong>
                                <span id="view_work_description" class="text-muted"></span>
                            </div>
                        </div>

                        {{-- Date & Time --}}
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <strong class="me-3" style="min-width: 130px;">
                                    <i class="fa fa-calendar text-secondary"></i> Start Date:
                                </strong>
                                <span id="view_start_datetime" class="badge bg-secondary fs-6"></span>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <strong class="me-3" style="min-width: 130px;">
                                    <i class="fa fa-calendar-check text-success"></i> End Date:
                                </strong>
                                <span id="view_end_datetime" class="badge bg-success fs-6"></span>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <strong class="me-3" style="min-width: 130px;">
                                    <i class="fa fa-clock text-warning"></i> All Day:
                                </strong>
                                <span id="view_is_all_day"></span>
                            </div>
                        </div>

                        {{-- Location --}}
                        <div class="col-12" id="view_location_wrapper" style="display: none;">
                            <div class="d-flex align-items-start">
                                <strong class="me-3" style="min-width: 130px;">
                                    <i class="fa fa-map-marker-alt text-danger"></i> Location:
                                </strong>
                                <a href="javascript:void(0);" target="_blank" id="view_location_link"
                                    class="text-primary text-decoration-underline" style="cursor: pointer;">
                                    <span id="view_work_location"></span>
                                </a>
                            </div>
                        </div>

                        {{-- Category --}}
                        <div class="col-12" id="view_category_wrapper" style="display: none;">
                            <div class="d-flex align-items-center">
                                <strong class="me-3" style="min-width: 130px;">
                                    <i class="fa fa-tag text-info"></i> Category:
                                </strong>
                                <span id="view_work_category" class="badge bg-info"></span>
                            </div>
                        </div>

                        {{-- Team --}}
                        <div class="col-12" id="view_team_wrapper" style="display: none;">
                            <div class="d-flex align-items-center">
                                <strong class="me-3" style="min-width: 130px;">
                                    <i class="fa fa-users text-primary"></i> Team:
                                </strong>
                                <span id="view_work_team" class="badge bg-primary"></span>
                            </div>
                        </div>

                        {{-- Status --}}
                        <div class="col-12">
                            <div class="d-flex align-items-center">
                                <strong class="me-3" style="min-width: 130px;">
                                    <i class="fa fa-check-circle text-success"></i> Status:
                                </strong>
                                <span id="view_work_status"></span>
                            </div>
                        </div>

                        {{-- Note --}}
                        <div class="col-12" id="view_note_wrapper" style="display: none;">
                            <div class="d-flex align-items-start">
                                <strong class="me-3" style="min-width: 130px;">
                                    <i class="fa fa-sticky-note text-warning"></i> Note:
                                </strong>
                                <span id="view_work_note" class="text-muted fst-italic"></span>
                            </div>
                        </div>

                        {{-- Google Calendar Sync --}}
                        <div class="col-12" id="view_google_sync_wrapper" style="display: none;">
                            <div class="d-flex align-items-center">
                                <strong class="me-3" style="min-width: 130px;">
                                    <i class="fab fa-google text-danger"></i> Google Synced:
                                </strong>
                                <span id="view_google_synced" class="badge bg-success">
                                    <i class="fa fa-check"></i> Yes
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                    <i class="fa fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>
