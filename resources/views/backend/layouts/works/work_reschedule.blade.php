@extends('backend.app')

@section('title', 'Work Reschedule')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                {{-- Page Header --}}
                <div class="page-header">
                    <div class="card shadow-sm mb-2 border-0">
                        <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <h1 class="page-title mb-0">
                                    <i class="fas fa-clock-rotate-left text-warning"></i> Work Reschedule
                                </h1>
                                <p class="text-muted mb-0 mt-1">Update work schedule date and time</p>
                            </div>
                            <div class="text-end">
                                <a href="{{ route('work.list') }}" class="btn btn-secondary btn-sm">
                                    <i class="fa fa-arrow-left"></i> Back to List
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Main Content --}}
                <div class="row">
                    <div class="col-12">
                        <div class="card box-shadow-0">
                            <div class="card-body">
                                <form id="WorkRescheduleForm">
                                    @csrf
                                    <input type="hidden" name="work_id" id="workRescheduleID"
                                        value="{{ $work->id ?? '' }}">

                                    {{-- Current Work Details Section --}}
                                    <div class="card mb-4 border-info">
                                        <div class="card-header bg-info bg-opacity-10">
                                            <h5 class="mb-0 text-info">
                                                <i class="fa fa-info-circle"></i> Current Work Details
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                {{-- Title --}}
                                                <div class="col-md-12">
                                                    <div class="d-flex align-items-start">
                                                        <strong class="me-3" style="min-width: 120px;">Title:</strong>
                                                        <span id="current_work_title"
                                                            class="text-primary fw-semibold"></span>
                                                    </div>
                                                </div>

                                                {{-- Description --}}
                                                <div class="col-md-12" id="current_description_wrapper"
                                                    style="display: none;">
                                                    <div class="d-flex align-items-start">
                                                        <strong class="me-3"
                                                            style="min-width: 120px;">Description:</strong>
                                                        <span id="current_work_description" class="text-muted"></span>
                                                    </div>
                                                </div>

                                                {{-- Date & Time --}}
                                                <div class="col-md-12">
                                                    <div class="d-flex align-items-center">
                                                        <strong class="me-3" style="min-width: 120px;">
                                                            <i class="fa fa-calendar text-secondary"></i> Date:
                                                        </strong>
                                                        <span id="current_work_date" class="badge bg-secondary fs-6"></span>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="d-flex align-items-center">
                                                        <strong class="me-3" style="min-width: 120px;">
                                                            <i class="fa fa-clock text-info"></i> Time:
                                                        </strong>
                                                        <span id="current_work_time" class="badge bg-info fs-6"></span>
                                                    </div>
                                                </div>

                                                {{-- Location --}}
                                                <div class="col-md-12" id="current_location_wrapper" style="display: none;">
                                                    <div class="d-flex align-items-start">
                                                        <strong class="me-3" style="min-width: 120px;">
                                                            <i class="fa fa-map-marker-alt text-danger"></i> Location:
                                                        </strong>
                                                        <a href="javascript:void(0);" target="_blank"
                                                            id="current_location_link"
                                                            class="text-primary text-decoration-underline"
                                                            style="cursor: pointer;">
                                                            <span id="current_work_location"></span>
                                                        </a>
                                                    </div>
                                                </div>

                                                {{-- Category & Team --}}
                                                <div class="col-md-12" id="current_category_wrapper" style="display: none;">
                                                    <div class="d-flex align-items-center">
                                                        <strong class="me-3" style="min-width: 120px;">
                                                            <i class="fa fa-tag text-success"></i> Category:
                                                        </strong>
                                                        <span id="current_work_category" class="badge bg-success"></span>
                                                    </div>
                                                </div>

                                                <div class="col-md-12" id="current_team_wrapper" style="display: none;">
                                                    <div class="d-flex align-items-center">
                                                        <strong class="me-3" style="min-width: 120px;">
                                                            <i class="fa fa-users text-primary"></i> Team:
                                                        </strong>
                                                        <span id="current_work_team" class="badge bg-primary"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-4">

                                    {{-- New Schedule Section --}}
                                    <div class="card border-warning">
                                        <div class="card-header bg-warning bg-opacity-10">
                                            <h5 class="mb-0 text-warning">
                                                <i class="fa fa-calendar-alt"></i> New Schedule
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                {{-- New Work Date --}}
                                                <div class="col-md-3">
                                                    <label class="form-label fw-bold">
                                                        <i class="fa fa-calendar-day"></i> New Date
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="date" class="form-control" name="work_date"
                                                        id="reschedule_work_date" required>
                                                    <span class="text-danger error-text work_date_error"></span>
                                                </div>

                                                {{-- Time Fields (hidden if all_day) --}}
                                                <div class="col-md-3" id="start_time_wrapper">
                                                    <label class="form-label fw-bold">
                                                        <i class="fa fa-clock"></i> Start Time
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" class="form-control timepicker"
                                                        name="start_time" id="reschedule_start_time"
                                                        placeholder="09:00 AM">
                                                    <span class="text-danger error-text start_time_error"></span>
                                                </div>

                                                <div class="col-md-3" id="end_time_wrapper">
                                                    <label class="form-label fw-bold">
                                                        <i class="fa fa-clock"></i> End Time
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" class="form-control timepicker" name="end_time"
                                                        id="reschedule_end_time" placeholder="05:00 PM">
                                                    <span class="text-danger error-text end_time_error"></span>
                                                </div>

                                                {{-- All Day Checkbox --}}
                                                <div class="col-md-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input custom-checkbox" type="checkbox"
                                                            id="reschedule_is_all_day" name="is_all_day" value="1">
                                                        <label class="form-check-label" for="reschedule_is_all_day">
                                                            <strong>All Day Event</strong>
                                                            <small class="d-block text-muted">Enable this for all-day
                                                                events (no specific time)</small>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Action Buttons --}}
                                    <div class="card-footer bg-transparent border-0 mt-4">
                                        <div class="d-flex justify-content-end align-items-center">
                                            <a href="{{ route('work.list') }}" class="btn btn-secondary btn-sm mx-3">
                                                <i class="fa fa-times"></i> Cancel
                                            </a>
                                            <button type="submit" class="btn btn-warning btn-sm"
                                                id="workRescheduleSubmitBtn">
                                                <i class="fa fa-check"></i> Reschedule Work
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Timepicker JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-timepicker/1.13.18/jquery.timepicker.min.js"></script>

    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                }
            });

            // handle timepicker
            $('.timepicker').timepicker({
                timeFormat: 'h:i A', // 12-hour format with AM/PM
                interval: 15, // 15 minute intervals
                dynamic: false,
                dropdown: true,
                scrollbar: true,
                startTime: '12:00 AM',
                endTime: '11:45 PM'
            });

            // Load work data on page load
            let workId = $('#workRescheduleID').val();
            if (workId) {
                loadWorkData(workId);
            }

            // Function to load work data
            function loadWorkData(id) {
                let url = "{{ route('work.reschedule.edit', ':id') }}".replace(':id', id);

                $.get(url, function(response) {
                    if (response.success) {
                        let work = response.data;

                        // Display current work details
                        $('#current_work_title').text(work.title || '---');
                        $('#current_work_date').text(work.formatted_date);
                        $('#current_work_time').text(work.formatted_time);

                        // Description
                        if (work.description) {
                            $('#current_description_wrapper').show();
                            $('#current_work_description').text(work.description);
                        }

                        // Location
                        if (work.location) {
                            $('#current_location_wrapper').show();
                            $('#current_work_location').text(work.location);

                            if (work.latitude && work.longitude) {
                                let mapUrl =
                                    `https://www.google.com/maps/search/?api=1&query=${work.latitude},${work.longitude}`;
                                $('#current_location_link').attr('href', mapUrl);
                            } else {
                                $('#current_location_link').removeAttr('href');
                            }
                        }

                        // Category
                        if (work.category) {
                            $('#current_category_wrapper').show();
                            $('#current_work_category').text(work.category);
                        }

                        // Team
                        if (work.team) {
                            $('#current_team_wrapper').show();
                            $('#current_work_team').text(work.team);
                        }

                        // Pre-fill form
                        $('#reschedule_work_date').val(work.work_date);
                        $('#reschedule_start_time').val(work.start_time || '');
                        $('#reschedule_end_time').val(work.end_time || '');
                        $('#reschedule_is_all_day').prop('checked', work.is_all_day);

                        // Show/hide time fields
                        if (work.is_all_day) {
                            $('#start_time_wrapper, #end_time_wrapper').hide();
                        } else {
                            $('#start_time_wrapper, #end_time_wrapper').show();
                        }
                    } else {
                        toastr.error(response.message || 'Failed to load work details.');
                        window.location.href = "{{ route('work.list') }}";
                    }
                }).fail(function() {
                    toastr.error('Failed to load work details.');
                    window.location.href = "{{ route('work.list') }}";
                });
            }

            // Handle All Day checkbox toggle
            $('#reschedule_is_all_day').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#start_time_wrapper').hide();
                    $('#end_time_wrapper').hide();
                    $('#start_time').val('').prop('required', false);
                    $('#end_time').val('').prop('required', false);
                } else {
                    $('#start_time_wrapper').show();
                    $('#end_time_wrapper').show();
                    $('#start_time').prop('required', true);
                    $('#end_time').prop('required', true);
                }
            });

            // Handle reschedule form submission
            $('#WorkRescheduleForm').on('submit', function(e) {
                e.preventDefault();

                let formData = new FormData(this);
                let workId = $('#workRescheduleID').val();
                let url = "{{ route('work.reschedule.update', ':id') }}".replace(':id', workId);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        $('span.error-text').text('');
                        $('#workRescheduleSubmitBtn')
                            .prop('disabled', true)
                            .html('<i class="fa fa-spinner fa-spin"></i> Rescheduling...');
                    },
                    success: function(response) {
                        $('#workRescheduleSubmitBtn')
                            .prop('disabled', false)
                            .html('<i class="fa fa-check"></i> Reschedule Work');

                        if (response.status) {
                            toastr.success(response.message);
                            setTimeout(function() {
                                window.location.href = "{{ route('work.list') }}";
                            }, 1500);
                        } else {
                            if (response.errors) {
                                $.each(response.errors, function(field, messages) {
                                    $('span.' + field + '_error').text(messages[0]);
                                });
                            }
                            toastr.error(response.message || 'Validation failed.');
                        }
                    },
                    error: function(xhr) {
                        $('#workRescheduleSubmitBtn')
                            .prop('disabled', false)
                            .html('<i class="fa fa-check"></i> Reschedule Work');

                        if (xhr.status === 422 && xhr.responseJSON.errors) {
                            $.each(xhr.responseJSON.errors, function(field, messages) {
                                $('span.' + field + '_error').text(messages[0]);
                            });
                        } else {
                            toastr.error(xhr.responseJSON?.message ||
                                'Failed to reschedule work.');
                        }
                    }
                });
            });
        });
    </script>

    {{-- Helper function to format time in 12-hour format --}}
    <script>
        function formatTime12Hour(date) {
            let hours = date.getHours();
            let minutes = date.getMinutes();
            let ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12; // 0 should be 12
            minutes = minutes < 10 ? '0' + minutes : minutes;
            return hours + ':' + minutes + ' ' + ampm;
        }
    </script>
@endpush

{{-- styles push --}}
@push('styles')
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/jquery-timepicker/1.13.18/jquery.timepicker.min.css">

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

        .form-check {
            margin-top: 28px;
        }
    </style>
@endpush
