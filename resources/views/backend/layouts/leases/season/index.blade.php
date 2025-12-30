{{-- @extends('backend.layouts.app') --}}
@extends('backend.app')

@section('title', 'Season List')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Season List</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Index</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Season List</li>
                        </ol>
                    </div>
                </div>

                <div id="alertContainer"></div>

                <div class="row">
                    <div class="col-12">
                        <div class="card box-shadow-0">
                            <div class="card-body">

                                <div
                                    class="card-header border-bottom mb-3 d-flex justify-content-between align-items-center">
                                    <h4 class="mb-0">Season List</h4>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#seasonModal" id="addSeasonBtn">Add Season</button>
                                </div>


                                <div class="table-responsive">
                                    <table class="table table-bordered " id="seasonTable" width="100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Duration</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @include('backend.layouts.leases.season.create')
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/css/iziToast.min.css">

    <style>
        .datepicker {
            background-color: #fff;
            border: 1px solid #e9ebfa;
            border-radius: 5px;
            box-shadow: none;
            display: inline-block;
            font-family: Roboto, sans-serif;
            font-size: inherit;
            margin: 1px 0 0;
            padding: 0px;
            width: auto !important;
            z-index: 5 !important;
        }
        .form-group label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }

        .btn-action {
            padding: 4px 8px;
            font-size: 12px;
        }

        .modal-header {
            border-bottom: 1px solid #e0e0e0;
        }

        .modal-body {
            padding: 24px;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .alert {
            border-radius: 6px;
            margin-bottom: 15px;
        }
    </style>
@endpush


@push('scripts')
    <script src="{{asset('backend/plugins/bootstrap-datepicker/js/datepicker.js')}}"></script>
    <script src="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/js/iziToast.min.css"></script>
    <script src="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/js/iziToast.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.datepicker2').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true,
                width: 300
            });
            // Initialize DataTable
            const seasonTable = $('#seasonTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('seasons.list') }}',
                    type: 'GET',
                    data: function(d) {
                        return d;
                    }
                },
                columns: [{data: 'DT_RowIndex',name: 'DT_RowIndex',orderable: false,searchable: false},
                    {data: 'name',name: 'name'},
                    {data: 'date',name: 'date'},
                    {data: 'status',name: 'status',orderable: false,searchable: false},
                    {data: 'actions',name: 'actions',orderable: false,searchable: false}
                ],
                order: [
                    [0, 'desc']
                ],
                pageLength: 10,
            });

            // Add Property Type
            $('#addSeasonBtn').click(function() {
                $('#seasonForm')[0].reset();
                $('#seasonId').val('');
                $('#modalTitle').text('Add Unit');
                $('#submitBtn').text('Save ');
                $('#seasonModal').modal('show');
            });

            // Submit Form
            $('#seasonForm').on('submit', function(e) {
                e.preventDefault();

                const id = $('#seasonId').val();
                const url = id ? `{{ route('seasons.update', '') }}/${id}` :
                    '{{ route('seasons.store') }}';
                const method = id ? 'POST' : 'POST';

                $.ajax({
                    url: url,
                    type: method,
                    data: $(this).serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            showToast('success', response.message);
                            $('#seasonModal').modal('hide');
                            seasonTable.ajax.reload();
                            $('#seasonForm')[0].reset();
                        }
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors || {};
                        if (Object.keys(errors).length > 0) {
                            let errorMsg = 'Please fix the following errors:\n';
                            $.each(errors, function(key, value) {
                                errorMsg += '- ' + value[0] + '\n';
                            });
                            // toastr.error(errorMsg);
                            showToast('danger', errorMsg);
                        } else {
                            // toastr.error(xhr.responseJSON?.message || 'An error occurred');
                            showToast('danger', xhr.responseJSON?.message || 'An error occurred');
                        }
                    }
                });
            });

           
        });

        window.showToast = function(type, message) {
            if (typeof iziToast !== 'undefined') {
                iziToast[type]({
                    title: type === 'success' ? 'Success' : 'Error',
                    message: message,
                    position: 'topRight',
                    timeout: type === 'success' ? 3000 : 5000
                });
            } else if (typeof toastr !== 'undefined') {
                toastr[type](message);
            } else {
                alert(message);
            }
        };

        // Edit Property Type
        function editSeason(id) {
            $.ajax({
                url: `{{ route('seasons.edit', '') }}/${id}`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        $('#seasonId').val(response.data.id);
                        $('#name').val(response.data.name);
                        $('#blanket_start_date').val(response.data.blanket_start_date);
                        $('#blanket_end_date').val(response.data.blanket_end_date);
                        $('#isActive').prop('checked', response.data.is_active);
                        $('#modalTitle').text('Edit Season');
                        $('#submitBtn').text('Update');
                        $('#seasonModal').modal('show');
                        // toastr.success(resp.message);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Error loading season');
                    showAlert('danger', xhr.responseJSON?.message || 'Error loading season');
                }
            });
        }

        // delete Confirm
        function showDeleteConfirm(id) {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure you want to delete ?',
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
            let url = `{{ route('seasons.delete', '') }}/${id}`;
            let csrfToken = '{{ csrf_token() }}';
            $.ajax({
                type: "DELETE",
                url: url.replace(':id', id),
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function(resp) {
                    NProgress.done();
                    showToast('success', resp.message || 'Deleted successfully!');
                    $('#seasonTable').DataTable().ajax.reload();
                },
                error: function(error) {
                    NProgress.done();
                    showToast('danger', error.responseJSON?.message || 'Error deleting season');
                }
            });
        }

        function toggleSeasonStatus(id) {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure you want to update status?',
                text: 'If you update this, it will be updated.',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, update it!',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ route('seasons.toggle.status', '') }}/${id}`,
                        type: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#seasonTable').DataTable().ajax.reload();
                                // toastr.success(response.message);
                                showToast('success', response.message);
                            }
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.message || 'Error toggling status');
                            // showAlert('danger', xhr.responseJSON?.message || 'Error toggling status');
                            showToast('error', xhr.responseJSON?.message || 'Error toggling status');
                        }
                    });
                }
            });

        }
    </script>
@endpush
