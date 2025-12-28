{{-- @extends('backend.layouts.app') --}}
@extends('backend.app')

@section('title', 'Unit List')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Unit List</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Index</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Unit List</li>
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
                                    <h4 class="mb-0">Unit List</h4>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#unitModal" id="addUnitBtn">Add Unit</button>
                                </div>


                                <div class="table-responsive">
                                    <table class="table table-bordered " id="unitTable" width="100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Property</th>
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

    @include('backend.layouts.properties.unit.create')
@endsection

@push('styles')
    <style>
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
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            const unitTable = $('#unitTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('units.list') }}',
                    type: 'GET',
                    data: function(d) {
                        return d;
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'property',
                        name: 'property'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [0, 'desc']
                ],
                pageLength: 10,
            });

            // Add Property Type
            $('#addUnitBtn').click(function() {
                $('#unitForm')[0].reset();
                $('#unitId').val('');
                $('#modalTitle').text('Add Unit');
                $('#submitBtn').text('Save ');
                $('#unitModal').modal('show');
            });

            // Submit Form
            $('#unitForm').on('submit', function(e) {
                e.preventDefault();

                const id = $('#unitId').val();
                const url = id ? `{{ route('units.update', '') }}/${id}` :
                    '{{ route('units.store') }}';
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
                            toastr.success(response.message);
                            // showAlert('success', response.message);
                            $('#unitModal').modal('hide');
                            unitTable.ajax.reload();
                            $('#unitForm')[0].reset();
                        }
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors || {};
                        if (Object.keys(errors).length > 0) {
                            let errorMsg = 'Please fix the following errors:\n';
                            $.each(errors, function(key, value) {
                                errorMsg += '- ' + value[0] + '\n';
                            });
                            toastr.error(errorMsg);
                            // showAlert('danger', errorMsg);
                        } else {
                            toastr.error(xhr.responseJSON?.message || 'An error occurred');
                            // showAlert('danger', xhr.responseJSON?.message || 'An error occurred');
                        }
                    }
                });
            });

            // Show Alert
            function showAlert(type, message) {
                const alertHtml = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>`;

                $('#alertContainer').html(alertHtml);

                setTimeout(() => {
                    $('.alert').fadeOut('slow', function() {
                        $(this).remove();
                    });
                }, 5000);
            }
        });

        // Edit Property Type
        function editUnit(id) {
            $.ajax({
                url: `{{ route('units.edit', '') }}/${id}`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        $('#unitId').val(response.data.id);
                        $('#name').val(response.data.name);
                        $('#property_id').val(response.data.property_id).trigger('change');
                        $('#gender_designation').val(response.data.gender_designation).trigger('change');
                        $('#isActive').prop('checked', response.data.is_active);
                        $('#modalTitle').text('Edit Unit');
                        $('#submitBtn').text('Update');
                        $('#unitModal').modal('show');
                        // toastr.success(resp.message);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Error loading Unit');
                    showAlert('danger', xhr.responseJSON?.message || 'Error loading Unit');
                }
            });
        }

        // delete Confirm
        function showDeleteConfirm(id) {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure you want to delete this team?',
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
            let url = `{{ route('units.delete', '') }}/${id}`;
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
                    $('#unitTable').DataTable().ajax.reload();
                },
                error: function(error) {
                    NProgress.done();
                    toastr.error(error.message);
                }
            });
        }

        function toggleStatus(id) {
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
                        url: `{{ route('units.toggle.status', '') }}/${id}`,
                        type: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#unitTable').DataTable().ajax.reload();
                                toastr.success(response.message);
                            }
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.message || 'Error toggling status');
                            showAlert('danger', xhr.responseJSON?.message || 'Error toggling status');
                        }
                    });
                }
            });

        }
    </script>
@endpush
