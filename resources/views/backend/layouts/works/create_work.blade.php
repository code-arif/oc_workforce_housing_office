<div class="modal fade" id="workModal" tabindex="-1" aria-labelledby="workModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="workForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="workID">

                <div class="modal-header">
                    <h5 class="modal-title" id="workModalLabel">Create Work</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        {{-- Title --}}
                        <div class="col-md-4">
                            <div class="p-3 rounded-2 bg-light equal-box">
                                <label class="form-label">Title</label>
                                <input type="text" class="form-control" name="title" id="work_title"
                                    placeholder="Enter Work Title">
                                <span class="text-danger error-text title_error"></span>
                            </div>
                        </div>

                        {{-- Team select --}}
                        <div class="col-md-4">
                            <div class="p-2 rounded-2 bg-light equal-box">
                                <label for="team_id">Select Team</label>
                                <select name="team_id" id="team_id" class="form-control">
                                    <option value="">-- Select Team --</option>
                                </select>
                            </div>
                        </div>

                        {{-- Category --}}
                        <div class="col-md-4 mb-3">
                            <div class="p-3 rounded-2 bg-light equal-box">
                                <label class="form-label">Select Category</label>
                                <select name="category_id" id="category_id" class="form-control">
                                    <option value="">-- Select Category --</option>
                                </select>
                                <input type="text" name="category_name" class="form-control mt-2"
                                    placeholder="Or create new category">
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control summernote" name="description" id="work_description" rows="3"
                                placeholder="Enter Description"></textarea>
                            <span class="text-danger error-text description_error"></span>
                        </div>

                        {{-- Work Date --}}
                        <div class="col-md-4 mt-3">
                            <label class="form-label">Work Date</label>
                            <input type="date" class="form-control" name="work_date" id="work_date">
                            <span class="text-danger error-text work_date_error"></span>
                        </div>

                        {{-- Start Time --}}
                        <div class="col-md-3 mt-3" id="start_time_wrapper">
                            <label class="form-label">Start Time</label>
                            <input type="text" class="form-control timepicker" name="start_time" id="start_time"
                                placeholder="02:30 PM">
                            <span class="text-danger error-text start_time_error"></span>
                        </div>

                        {{-- End Time --}}
                        <div class="col-md-3 mt-3" id="end_time_wrapper">
                            <label class="form-label">End Time</label>
                            <input type="text" class="form-control timepicker" name="end_time" id="end_time"
                                placeholder="04:30 PM">
                            <span class="text-danger error-text end_time_error"></span>
                        </div>

                        <!-- All Day Checkbox -->
                        <div class="col-md-2 mt-3">
                            <label class="form-label form-switch">All Day</label>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input custom-checkbox" name="is_all_day"
                                    id="is_all_day" value="1">
                                <label class="form-check-label" for="is_all_day">All Day Event</label>
                            </div>
                        </div>

                        {{-- Map Search Box --}}
                        <div class="col-md-12 mt-3">
                            <label class="form-label">Search Address</label>
                            <input type="text" class="form-control" id="map_search"
                                placeholder="Search for an address...">
                        </div>

                        {{-- Google Map --}}
                        <div class="col-md-12 mt-3">
                            <label class="form-label">Pick Location on Map</label>
                            <div id="map" style="height: 350px; width: 100%;"></div>
                        </div>

                        {{-- Location --}}
                        <div class="col-md-6 mt-3 d-none">
                            <label class="form-label">Location</label>
                            <input type="text" class="form-control" name="location" id="work_location"
                                placeholder="Work Location" readonly>
                            <span class="text-danger error-text location_error"></span>
                        </div>

                        {{-- Latitude --}}
                        <div class="col-md-3 mt-3 d-none">
                            <label class="form-label">Latitude</label>
                            <input type="text" class="form-control" name="latitude" id="work_latitude"
                                placeholder="Latitude" readonly>
                            <span class="text-danger error-text latitude_error"></span>
                        </div>

                        {{-- Longitude --}}
                        <div class="col-md-3 mt-3 d-none">
                            <label class="form-label">Longitude</label>
                            <input type="text" class="form-control" name="longitude" id="work_longitude"
                                placeholder="Longitude" readonly>
                            <span class="text-danger error-text longitude_error"></span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="workSubmitBtn">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
