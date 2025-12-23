@extends('backend.app')

@section('title', 'Reschedule List')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">

            <div class="main-container container-fluid">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Reschedule List</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Reschedule</a></li>
                            <li class="breadcrumb-item active" aria-current="page">List</li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    {{-- reschedule table section --}}
                    <div class="col-12 col-md-12 col-sm-12">
                        <div class="card box-shadow-0">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table text-nowrap mb-0 table-bordered" id="datatable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Title</th>
                                                <th>Team</th>
                                                <th>Note</th>
                                                <th>Suggested Start Time</th>
                                                <th>Suggested End Time</th>
                                                <th>Suggested Date</th>
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
    <!-- CONTAINER CLOSED -->


    {{-- Add/Edit reschedule Modal --}}
    @include('backend.layouts.reschedule.reschedule')
@endsection


@push('scripts')
    <!-- Timepicker JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-timepicker/1.13.18/jquery.timepicker.min.js"></script>

    <script>
        //document ready function
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                }
            });

            // Handle All Day checkbox toggle
            $('#is_all_day').on('change', function() {
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

            let dTable = $('#datatable').DataTable({
                order: [],
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                processing: true,
                serverSide: true,
                language: {
                    processing: `<div class="text-center">
                        <img src="{{ asset('default/loader.gif') }}" alt="Loader" style="width: 50px;">
                    </div>`
                },
                ajax: {
                    url: "{{ route('reschedule.work.list') }}",
                    type: "GET",
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
                        data: 'team'
                    },
                    {
                        data: 'note'
                    },
                    {
                        data: 'suggested_start_time'
                    },
                    {
                        data: 'suggested_end_time'
                    },
                    {
                        data: 'suggested_date'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // Handle form submission
            $('#rescheduleForm').on('submit', function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                let id = $('#rescheduleID').val();
                let url = "{{ route('reschedule.work.update', ':id') }}".replace(':id', id);

                formData.append('_method', 'POST');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        $('span.error-text').text('');
                        $('#rescheduleSubmitBtn').prop('disabled', true).html('Processing...');
                    },
                    success: function(response) {
                        if (!response.status) {
                            $.each(response.errors, function(prefix, val) {
                                $('span.' + prefix + '_error').text(val[0]);
                            });
                        } else {
                            $('#rescheduleModal').modal('hide');
                            $('#rescheduleForm')[0].reset();
                            toastr.success(response.message);
                            $('#datatable').DataTable().ajax.reload();
                        }
                        $('#rescheduleSubmitBtn').prop('disabled', false).html('Save changes');
                    },
                    error: function(xhr) {
                        $('#rescheduleSubmitBtn').prop('disabled', false).html('Save changes');
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


            // Edit reschedule - Load existing data
            $(document).on('click', '.rescheduleBtn', function() {
                var id = $(this).data('id');
                var url = "{{ route('reschedule.work.edit', ':id') }}".replace(':id', id);

                $.get(url, function(response) {
                    if (response.success) {
                        $('#rescheduleModalLabel').text('Edit Reschedule');
                        $('#rescheduleID').val(response.data.id);

                        // Work details (read-only) - Display from work table
                        $('#work_title').text(response.data.work.title ?? '---');
                        $('#work_description').text(response.data.work.description ?? '---');
                        $('#reschedule_location').text(response.data.work.location ?? '---');

                        // Display work time and date from work.start_datetime and work.end_datetime
                        if (response.data.work.is_all_day == 1) {
                            $('#time').text('All Day');
                        } else if (response.data.work.start_datetime && response.data.work
                            .end_datetime) {
                            let workStartTime = response.data.work.start_datetime.split(' ')[1]
                                .substring(0, 5);
                            let workEndTime = response.data.work.end_datetime.split(' ')[1]
                                .substring(0, 5);

                            // Convert to 12-hour format
                            let formatTime = function(time) {
                                let [hours, minutes] = time.split(':');
                                let hour = parseInt(hours);
                                let ampm = hour >= 12 ? 'PM' : 'AM';
                                hour = hour % 12 || 12;
                                return hour + ':' + minutes + ' ' + ampm;
                            };

                            $('#time').text(formatTime(workStartTime) + ' - ' + formatTime(
                                workEndTime));
                        } else {
                            $('#time').text('---');
                        }

                        // Display work date
                        if (response.data.work.start_datetime) {
                            let workDate = response.data.work.start_datetime.split(' ')[0];
                            // Format date as dd MMM yyyy
                            let dateObj = new Date(workDate);
                            let formattedDate = dateObj.toLocaleDateString('en-GB', {
                                day: '2-digit',
                                month: 'short',
                                year: 'numeric'
                            });
                            $('#work_date').text(formattedDate);
                        } else {
                            $('#work_date').text('---');
                        }

                        // Location link
                        if (response.data.work.latitude && response.data.work.longitude) {
                            let mapUrl =
                                `https://www.google.com/maps/search/?api=1&query=${response.data.work.latitude},${response.data.work.longitude}`;
                            $('#reschedule_location_link')
                                .attr('href', mapUrl)
                                .attr('target', '_blank')
                                .attr('title', 'View on Google Maps');
                        } else {
                            $('#reschedule_location_link').removeAttr('href');
                        }

                        // Editable reschedule fields - From reschedule_requests table
                        $('#is_all_day').prop('checked', response.data.is_all_day == 1);

                        if (response.data.is_all_day == 1) {
                            $('#start_time_wrapper').hide();
                            $('#end_time_wrapper').hide();
                            $('#start_time').val('').prop('required', false);
                            $('#end_time').val('').prop('required', false);
                        } else {
                            $('#start_time_wrapper').show();
                            $('#end_time_wrapper').show();
                            $('#start_time').prop('required', true);
                            $('#end_time').prop('required', true);

                            // Extract time from reschedule start_datetime and end_datetime
                            if (response.data.start_datetime) {
                                let startTime = response.data.start_datetime.split(' ')[1]
                                    .substring(0, 5);
                                $('#start_time').val(startTime);
                            }

                            if (response.data.end_datetime) {
                                let endTime = response.data.end_datetime.split(' ')[1].substring(0,
                                    5);
                                $('#end_time').val(endTime);
                            }
                        }

                        // Extract date from reschedule start_datetime
                        if (response.data.start_datetime) {
                            let workDate = response.data.start_datetime.split(' ')[0];
                            $('#work_date_input').val(workDate);
                        }

                        $('#rescheduleModal').modal('show');
                    } else {
                        alert(response.message || 'Failed to load reschedule data');
                    }
                });
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
    </style>
@endpush
