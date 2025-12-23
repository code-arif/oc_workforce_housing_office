@extends('backend.app')

@section('title', 'Work List')

@section('content')
    {{-- work data table --}}
    <div class="app-content main-content mt-0">
        <div class="side-app">

            <div class="main-container container-fluid">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Work List</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Employee</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Work</li>
                        </ol>
                    </div>
                </div>

                {{-- Alert message --}}
                @if (!empty($scheduleRequest) && $scheduleRequest > 0)
                    <div class="alert alert-success alert-dismissible d-flex justify-content-between align-items-center fade show"
                        role="alert">
                        <div class="d-flex align-items-center gap-2">
                            <strong>Reschedule Request</strong>
                            <span class="badge rounded-circle bg-primary text-white"
                                style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                {{ $scheduleRequest }}
                            </span>
                            works have reschedule request
                            <a class="btn btn-sm btn-primary ms-2" href="{{ route('reschedule.work.list') }}">Check It
                                Out</a>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">&times;</button>
                    </div>
                @endif


                <div class="row">
                    {{-- work table section --}}
                    <div class="col-12 col-md-12 col-sm-12">
                        <div class="card box-shadow-0">
                            <div class="card-body">

                                <div class="card-header border-bottom mb-3">
                                    <div class="card-options ms-auto d-flex align-items-center gap-2">
                                        <!-- Completed Filter -->
                                        <select id="filter_completed" class="form-select form-select-sm"
                                            style="width: 180px;">
                                            <option value="">-- Completed Filter --</option>
                                            <option value="1">Completed</option>
                                            <option value="0">Not Completed</option>
                                        </select>

                                        <!-- Rescheduled Filter -->
                                        <select id="filter_rescheduled" class="form-select form-select-sm"
                                            style="width: 180px;">
                                            <option value="">-- Rescheduled Filter --</option>
                                            <option value="1">Rescheduled</option>
                                            <option value="0">Not Rescheduled</option>
                                        </select>


                                        <!-- Add Button -->
                                        {{-- <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#workModal" id="addworkBtn">
                                            Add Work
                                        </button> --}}
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table text-nowrap mb-0 table-bordered" id="datatable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Title</th>
                                                <th>Category</th>
                                                <th>Team</th>
                                                <th>Start Time</th>
                                                <th>End Time</th>
                                                <th>Work Date</th>
                                                <th>Completed</th>
                                                <th>Rescheduled</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Add/Edit work Modal --}}
    @include('backend.layouts.works.create_work')

    {{-- View work details modal --}}
    @include('backend.layouts.works.view')
@endsection

@push('styles')
    <!-- Timepicker CSS -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/jquery-timepicker/1.13.18/jquery.timepicker.min.css">

    <style>
        #map {
            height: 300px;
            border-radius: 8px;
        }

        .equal-box {
            min-height: 95px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Style the autocomplete dropdown */
        .pac-container {
            z-index: 10000 !important;
        }
    </style>

    {{-- style work complation button --}}
    <style>
        .completion-toggle {
            display: inline-flex;
            border: 1px solid #ccc;
            border-radius: 5px;
            overflow: hidden;
            cursor: pointer;
            user-select: none;
        }

        .completion-toggle .toggle-option {
            padding: 3px 10px;
            font-size: 12px;
            font-weight: 600;
            color: #555;
            background: #f8f9fa;
            transition: all 0.2s ease;
        }

        .completion-toggle .toggle-option.left {
            border-right: 1px solid #ccc;
        }

        .completion-toggle .toggle-option.active {
            color: #fff;
            background-color: #13bfa6;
            /* green */
        }

        .completion-toggle .toggle-option.left.active {
            background-color: #e984b1;
            /* red for NO */
        }
    </style>

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
@endpush

@push('scripts')
    {{-- Google Maps JavaScript API --}}
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBfGOjmqKtEBRsfVN9szUo_tac20wcI9HM&libraries=places">
    </script>

    <!-- Timepicker JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-timepicker/1.13.18/jquery.timepicker.min.js"></script>

    <script>
        // Global variables
        let map, marker, geocoder, autocomplete;
        let mapInitialized = false;

        // Function to initialize Google Map
        function initializeMap() {
            if (mapInitialized) return;

            // Default to Dhaka coordinates
            const defaultLocation = {
                lat: 23.8103,
                lng: 90.4125
            };

            // Initialize map
            map = new google.maps.Map(document.getElementById('map'), {
                center: defaultLocation,
                zoom: 13,
                mapTypeControl: true,
                streetViewControl: false,
                fullscreenControl: true
            });

            // Initialize geocoder
            geocoder = new google.maps.Geocoder();

            // Add marker
            marker = new google.maps.Marker({
                position: defaultLocation,
                map: map,
                draggable: true,
                animation: google.maps.Animation.DROP
            });

            // Initialize autocomplete
            const searchInput = document.getElementById('map_search');
            autocomplete = new google.maps.places.Autocomplete(searchInput, {
                fields: ['formatted_address', 'geometry', 'name'],
                types: ['geocode', 'establishment']
            });

            // Handle autocomplete selection
            autocomplete.addListener('place_changed', function() {
                const place = autocomplete.getPlace();

                if (!place.geometry || !place.geometry.location) {
                    toastr.error('No location found for this place');
                    return;
                }

                const lat = place.geometry.location.lat();
                const lng = place.geometry.location.lng();
                const address = place.formatted_address || place.name;

                updateLocation(lat, lng, address);
            });

            // Handle marker drag
            marker.addListener('dragend', function() {
                const position = marker.getPosition();
                reverseGeocode(position.lat(), position.lng());
            });

            // Handle click on map
            map.addListener('click', function(e) {
                marker.setPosition(e.latLng);
                reverseGeocode(e.latLng.lat(), e.latLng.lng());
            });

            mapInitialized = true;
        }

        // Update location fields and marker
        function updateLocation(lat, lng, address) {
            $('#work_latitude').val(lat.toFixed(6));
            $('#work_longitude').val(lng.toFixed(6));
            $('#work_location').val(address || '');

            // Move marker and center map
            const position = {
                lat: lat,
                lng: lng
            };
            marker.setPosition(position);
            map.setCenter(position);
            map.setZoom(15);

            // Add bounce animation
            marker.setAnimation(google.maps.Animation.BOUNCE);
            setTimeout(() => marker.setAnimation(null), 750);
        }

        // Reverse geocode coordinates to get address
        function reverseGeocode(lat, lng) {
            const latlng = {
                lat: lat,
                lng: lng
            };

            geocoder.geocode({
                location: latlng
            }, function(results, status) {
                if (status === 'OK') {
                    if (results[0]) {
                        updateLocation(lat, lng, results[0].formatted_address);
                    } else {
                        updateLocation(lat, lng, '');
                        toastr.warning('No address found for this location');
                    }
                } else {
                    console.error('Geocoder failed: ' + status);
                    updateLocation(lat, lng, '');
                }
            });
        }

        // When modal opens
        $('#workModal').on('shown.bs.modal', function() {
            if (!mapInitialized) {
                initializeMap();
            } else {
                // Trigger resize to fix display issues
                google.maps.event.trigger(map, 'resize');
                if (marker) {
                    map.setCenter(marker.getPosition());
                }
            }
        });

        //document ready functionq
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                }
            });

            let dTable = $('#datatable').DataTable({
                order: [],
                lengthMenu: [
                    [20, 50, 100, 300, 500],
                    [20, 50, 100, 300, "All"]
                ],
                processing: true,
                serverSide: true,
                language: {
                    processing: `<div class="text-center">
                                    <img src="{{ asset('default/loader.gif') }}" alt="Loader" style="width: 50px;">
                                </div>`
                },
                ajax: {
                    url: "{{ route('work.list') }}",
                    type: "GET",
                    data: function(d) {
                        d.is_completed = $('#filter_completed').val();
                        d.is_rescheduled = $('#filter_rescheduled').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'title'
                    },
                    {
                        data: 'category'
                    },
                    {
                        data: 'team'
                    },
                    {
                        data: 'start_time'
                    },
                    {
                        data: 'end_time'
                    },
                    {
                        data: 'work_date'
                    },
                    {
                        data: 'is_completed'
                    },
                    {
                        data: 'is_rescheduled'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // reload table on filter change
            $('#filter_completed, #filter_rescheduled').change(function() {
                dTable.ajax.reload();
            });

            // Open modal for new work
            $('#addworkBtn').click(function() {
                $('#workModalLabel').text('Create work');
                $('#workForm')[0].reset();
                $('#workSubmitBtn').prop('disabled', false).html('Save changes');
                $('#workID').val('');
                $('.error-text').text('');
                $('#map_search').val('');

                // Reset summernote
                $('#work_description').summernote('code', '');

                // Reset map to default location
                if (mapInitialized) {
                    const defaultLocation = {
                        lat: 23.8103,
                        lng: 90.4125
                    };
                    marker.setPosition(defaultLocation);
                    map.setCenter(defaultLocation);
                    map.setZoom(13);
                    $('#work_latitude').val('');
                    $('#work_longitude').val('');
                    $('#work_location').val('');
                }

                // Load teams dynamically
                $.get("{{ route('team.list.work') }}", function(response) {
                    if (response.status) {
                        let options = '<option value="">-- Select Team --</option>';
                        response.data.forEach(function(team) {
                            options +=
                                `<option value="${team.id}">${team.name}</option>`;
                        });
                        $('#team_id').html(options);
                    }
                });

                // Load category dynamically
                $.get("{{ route('work.categroy') }}", function(response) {
                    if (response.status) {
                        let options = '<option value="">-- Select Category--</option>';
                        response.data.forEach(function(category) {
                            options +=
                                `<option value="${category.id}">${category.name}</option>`;
                        });
                        $('#category_id').html(options);
                    }
                });

                $('#workModal').modal('show');
            });

            // Handle form submission
            $('#workForm').on('submit', function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                let id = $('#workID').val();
                let url = id ?
                    "{{ route('work.update', ':id') }}".replace(':id', id) :
                    "{{ route('work.store') }}";

                if (id) {
                    formData.append('_method', 'POST');
                }

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        $('span.error-text').text('');
                        $('#workSubmitBtn').prop('disabled', true).html('Processing...');
                    },
                    success: function(response) {
                        if (response.status == 0) {
                            $.each(response.errors, function(prefix, val) {
                                $('span.' + prefix + '_error').text(val[0]);
                            });
                        } else {
                            $('#workModal').modal('hide');
                            $('#workForm')[0].reset();
                            toastr.success(response.message);
                            $('#datatable').DataTable().ajax.reload();
                        }
                        $('#workSubmitBtn').prop('disabled', false).html('Save changes');
                    },
                    error: function(xhr) {
                        $('#workSubmitBtn').prop('disabled', false).html('Save changes');
                        if (xhr.status === 422) {
                            $.each(xhr.responseJSON.errors, function(prefix, val) {
                                prefix = prefix.replace(/\./g, '_');
                                $('span.' + prefix + '_error').text(val[0]);
                            });
                        } else {
                            toastr.error(xhr.responseJSON.message ||
                                'Something went wrong. Please try again.');
                        }
                    }
                });
            });

            // Edit work - Load existing data
            $(document).on('click', '.editwork', function() {
                var id = $(this).data('id');
                var url = "{{ route('work.edit', ':id') }}".replace(':id', id);

                $.get(url, function(response) {
                    $('#workModalLabel').text('Edit work');
                    $('#workID').val(response.data.id);

                    // Fill simple fields
                    $('#work_title').val(response.data.title);
                    $('#work_location').val(response.data.location);
                    $('#work_latitude').val(response.data.latitude);
                    $('#work_longitude').val(response.data.longitude);

                    // Set Summernote content
                    $('#work_description').summernote('reset');
                    $('#work_description').summernote('code', response.data.description || '');

                    // Set work date from start_datetime
                    if (response.data.start_datetime) {
                        let date = new Date(response.data.start_datetime);
                        $('#work_date').val(date.toISOString().split('T')[0]);
                    }

                    // Set all day checkbox
                    $('#is_all_day').prop('checked', response.data.is_all_day);

                    if (response.data.is_all_day) {
                        $('#start_time, #end_time').prop('disabled', true).val('');
                        $('#start_time_wrapper, #end_time_wrapper').hide();
                    } else {
                        $('#start_time, #end_time').prop('disabled', false);
                        $('#start_time_wrapper, #end_time_wrapper').show();

                        // Set time in 12-hour format
                        if (response.data.start_datetime) {
                            let startTime = new Date(response.data.start_datetime);
                            $('#start_time').val(formatTime12Hour(startTime));
                        }

                        if (response.data.end_datetime) {
                            let endTime = new Date(response.data.end_datetime);
                            $('#end_time').val(formatTime12Hour(endTime));
                        }
                    }

                    // Load teams and set selected value
                    $.get("{{ route('team.list.work') }}", function(teamResponse) {
                        if (teamResponse.status) {
                            let options = '<option value="">-- Select Team --</option>';
                            teamResponse.data.forEach(function(team) {
                                options +=
                                    `<option value="${team.id}">${team.name}</option>`;
                            });
                            $('#team_id').html(options);

                            if (response.data.team_id) {
                                $('#team_id').val(response.data.team_id);
                            }
                        }

                        // Load categories and set selected value
                        $.get("{{ route('work.categroy') }}", function(categoryResponse) {
                            if (categoryResponse.status) {
                                let options =
                                    '<option value="">-- Select Category --</option>';
                                categoryResponse.data.forEach(function(category) {
                                    options +=
                                        `<option value="${category.id}">${category.name}</option>`;
                                });
                                $('#category_id').html(options);

                                if (response.data.category_id) {
                                    $('#category_id').val(response.data
                                        .category_id);
                                }
                            }
                        });

                        // Show modal and set map if coordinates exist
                        $('#workModal').modal('show');

                        if (response.data.latitude && response.data.longitude) {
                            const lat = parseFloat(response.data.latitude);
                            const lng = parseFloat(response.data.longitude);

                            $('#workModal').one('shown.bs.modal', function() {
                                if (mapInitialized) {
                                    updateLocation(lat, lng, response.data
                                        .location);
                                }
                            });
                        }
                    });
                });
            });

            // Initialize 12-hour time picker
            $('.timepicker').timepicker({
                timeFormat: 'h:i A', // 12-hour format with AM/PM
                interval: 15, // 15 minute intervals
                dynamic: false,
                dropdown: true,
                scrollbar: true,
                startTime: '12:00 AM',
                endTime: '11:45 PM'
            });

            // All day checkbox toggle
            $('#is_all_day').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#start_time, #end_time').prop('disabled', true).val('');
                    $('#start_time_wrapper, #end_time_wrapper').hide();
                } else {
                    $('#start_time, #end_time').prop('disabled', false);
                    $('#start_time_wrapper, #end_time_wrapper').show();
                }
            });

            // show work details modal
            $(document).on('click', '.viewBtn', function() {
                var id = $(this).data('id');
                var url = "{{ route('work.edit', ':id') }}".replace(':id', id);

                // Show modal
                $('#viewWorkModal').modal('show');

                // Show loading, hide content
                $('#workDetailsLoading').show();
                $('#workDetailsData').hide();

                // Reset all optional fields
                $('#view_description_wrapper, #view_location_wrapper, #view_category_wrapper, #view_team_wrapper, #view_note_wrapper, #view_google_sync_wrapper')
                    .hide();

                // Fetch work data
                $.get(url, function(response) {
                    if (response.success) {
                        let work = response.data;

                        // Title
                        $('#view_work_title').text(work.title || '---');

                        // Description
                        if (work.description) {
                            $('#view_description_wrapper').show();
                            $('#view_work_description').html(work.description); // ✅ render HTML
                        }


                        // Date & Time
                        let startDate = work.start_datetime ? new Date(work.start_datetime)
                            .toLocaleString('en-US', {
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            }) : '---';

                        let endDate = work.end_datetime ? new Date(work.end_datetime)
                            .toLocaleString('en-US', {
                                year: 'numeric',
                                month: 'short',
                                day: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit'
                            }) : '---';

                        $('#view_start_datetime').text(startDate);
                        $('#view_end_datetime').text(endDate);

                        // All Day
                        if (work.is_all_day) {
                            $('#view_is_all_day').html(
                                '<span class="badge bg-info"> Yes</span>'
                            );
                        } else {
                            $('#view_is_all_day').html(
                                '<span class="badge bg-secondary"> No</span>'
                            );
                        }

                        // Location
                        if (work.location) {
                            $('#view_location_wrapper').show();
                            $('#view_work_location').text(work.location);

                            let mapUrl;
                            if (work.latitude && work.longitude) {
                                mapUrl =
                                    `https://www.google.com/maps/search/?api=1&query=${work.latitude},${work.longitude}`;
                            } else {
                                mapUrl =
                                    `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(work.location)}`;
                            }
                            $('#view_location_link').attr('href', mapUrl).attr('target', '_blank');
                        }

                        // Category
                        if (work.category) {
                            $('#view_category_wrapper').show();
                            $('#view_work_category').text(work.category.name || work.category);
                        }

                        // Team
                        if (work.team) {
                            $('#view_team_wrapper').show();
                            $('#view_work_team').text(work.team.name || work.team);
                        }

                        // Status
                        let statusHtml = '';
                        if (work.is_completed) {
                            statusHtml =
                                '<span class="badge bg-success"><i class="fa fa-check"></i> Completed</span>';
                        } else if (work.is_rescheduled) {
                            statusHtml =
                                '<span class="badge bg-warning"><i class="fa fa-clock"></i> Rescheduled</span>';
                        } else {
                            statusHtml =
                                '<span class="badge bg-primary"><i class="fa fa-hourglass-half"></i> Pending</span>';
                        }
                        $('#view_work_status').html(statusHtml);

                        // Note
                        if (work.note) {
                            $('#view_note_wrapper').show();
                            $('#view_work_note').text(work.note);
                        }

                        // Google Calendar Sync
                        if (work.google_event_id) {
                            $('#view_google_sync_wrapper').show();
                        }

                        // Hide loading, show content
                        $('#workDetailsLoading').hide();
                        $('#workDetailsData').show();
                    } else {
                        toastr.error(response.message || 'Failed to load work details.');
                        $('#viewWorkModal').modal('hide');
                    }
                }).fail(function() {
                    toastr.error('Failed to load work details.');
                    $('#viewWorkModal').modal('hide');
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

    {{-- Work delete and complation system --}}
    <script>
        // Completion Status Change Confirmation
        function showCompletionChangeAlert(id, newStatus) {
            event.preventDefault();

            Swal.fire({
                title: 'Are you sure?',
                text: 'You want to update the completion status?',
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Yes',
                cancelButtonText: 'No',
            }).then((result) => {
                if (result.isConfirmed) {
                    completionStatusChange(id, newStatus);
                }
            });
        }

        // Handle AJAX Request
        function completionStatusChange(id, newStatus) {
            NProgress.start();

            let url = "{{ route('work.complation.status', ':id') }}";

            $.ajax({
                type: "POST",
                url: url.replace(':id', id),
                data: {
                    _token: "{{ csrf_token() }}",
                    is_completed: newStatus ? 1 : 0
                },
                success: function(resp) {
                    NProgress.done();
                    toastr.success(resp.message);
                    $('#datatable').DataTable().ajax.reload(null, false);
                },
                error: function(error) {
                    NProgress.done();
                    toastr.error(error.responseJSON?.message || 'Something went wrong!');
                }
            });
        }

        // delete Confirm
        function showDeleteConfirm(id) {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure you want to delete this work?',
                text: 'If you delete this, it will be gone forever.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteItem(id);
                }
            });
        }

        // Delete Button
        function deleteItem(id) {
            NProgress.start();
            let url = "{{ route('work.delete', ':id') }}";
            let csrfToken = '{{ csrf_token() }}';
            $.ajax({
                type: "DELETE",
                url: url.replace(':id', id),
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function(resp) {
                    NProgress.done();
                    toastr.success(resp.message);
                    $('#datatable').DataTable().ajax.reload();
                },
                error: function(error) {
                    NProgress.done();
                    toastr.error(error.message);
                }
            });
        }
    </script>
@endpush
