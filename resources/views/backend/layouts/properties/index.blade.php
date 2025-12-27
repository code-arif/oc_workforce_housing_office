{{-- @extends('backend.layouts.app') --}}
@extends('backend.app')

@section('title', 'Properties list ')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Properties list </h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Index</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Properties list </li>
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
                                    <h4 class="mb-0">Property List</h4>
                                    <a href="{{ route('property.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-circle"></i> Add Property
                                    </a>
                                </div>


                                <div class="table-responsive">
                                    <table class="table table-bordered " id="propertyTable" width="100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Address</th>
                                                <th>Rent</th>
                                                <th>Available Units</th>
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

        .select2-container {
            width: 100% !important;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            const propertyTable = $('#propertyTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("property.list") }}',
                    type: 'GET',
                    data: function(d) {
                        return d;
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'description', name: 'description' },
                    { data: 'rent', name: 'rent' },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[0, 'desc']], 
                pageLength: 10,
                dom: 'lrtip'
            });

            // Add Property Type
            $('#addpropertyBtn').click(function() {
                $('#propertyForm')[0].reset();
                $('#propertyId').val('');
                $('#modalTitle').text('Add Property ');
                $('#submitBtn').text('Save');
                $('#propertyModal').modal('show');
            });

            // Submit Form - REMOVED (using new create page)

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
        function editproperty(id) {
            $.ajax({
                url: `{{ route('property.edit', '') }}/${id}`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        $('#propertyId').val(response.data.id);
                        $('#name').val(response.data.name);
                        $('#address').val(response.data.address);
                        $('#isActive').prop('checked', response.data.is_active);
                        $('#modalTitle').text('Edit Property');
                        $('#submitBtn').text('Update Property ');
                        $('#propertyModal').modal('show');
                        // toastr.success(resp.message);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Error loading property ');
                    showAlert('danger', xhr.responseJSON?.message || 'Error loading property ');
                }
            });
        }

        // delete Confirm
        function showDeleteConfirm(id) {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure you want to delete this property?',
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
            let url = `{{ route('property.delete', '') }}/${id}`;
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
                    $('#propertyTable').DataTable().ajax.reload();
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
                        url: `{{ route('property.toggle.status', '') }}/${id}`,
                        type: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#propertyTable').DataTable().ajax.reload();
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

        $(document).ready(function() {
            // Modal select2 removed - using new create page
        })
    </script>
@endpush
