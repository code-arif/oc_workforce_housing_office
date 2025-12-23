{{-- @extends('backend.layouts.app') --}}
@extends('backend.app')

@section('title', 'Property Types')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Property Types</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Index</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Property Types</li>
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
                                    <h4 class="mb-0">Property Types List</h4>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#propertyTypeModal" id="addPropertyTypeBtn">Add Property Type</button>
                                </div>


                                <div class="table-responsive">
                                    <table class="table table-bordered " id="propertyTypeTable" width="100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Slug</th>
                                                <th>Description</th>
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

    @include('backend.layouts.properties.types.create')
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
            const propertyTypeTable = $('#propertyTypeTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("property-type.list") }}',
                    type: 'GET',
                    data: function(d) {
                        return d;
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'name', name: 'name' },
                    { data: 'slug', name: 'slug' },
                    { data: 'description', name: 'description' },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[0, 'desc']],
                pageLength: 10,
                dom: 'lrtip'
            });

            // Add Property Type
            $('#addPropertyTypeBtn').click(function() {
                $('#propertyTypeForm')[0].reset();
                $('#propertyTypeId').val('');
                $('#modalTitle').text('Add Property Type');
                $('#submitBtn').text('Add Property Type');
                $('#propertyTypeModal').modal('show');
            });

            // Submit Form
            $('#propertyTypeForm').on('submit', function(e) {
                e.preventDefault();

                const id = $('#propertyTypeId').val();
                const url = id ? `{{ route('property-type.update', '') }}/${id}` : '{{ route("property-type.store") }}';
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
                            showAlert('success', response.message);
                            $('#propertyTypeModal').modal('hide');
                            propertyTypeTable.ajax.reload();
                            $('#propertyTypeForm')[0].reset();
                        }
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors || {};
                        if (Object.keys(errors).length > 0) {
                            let errorMsg = 'Please fix the following errors:\n';
                            $.each(errors, function(key, value) {
                                errorMsg += '- ' + value[0] + '\n';
                            });
                            showAlert('danger', errorMsg);
                        } else {
                            showAlert('danger', xhr.responseJSON?.message || 'An error occurred');
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
        function editPropertyType(id) {
            $.ajax({
                url: `{{ route('property-type.edit', '') }}/${id}`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        $('#propertyTypeId').val(response.data.id);
                        $('#name').val(response.data.name);
                        $('#slug').val(response.data.slug);
                        $('#description').val(response.data.description);
                        $('#isActive').prop('checked', response.data.is_active);
                        $('#modalTitle').text('Edit Property Type');
                        $('#submitBtn').text('Update Property Type');
                        $('#propertyTypeModal').modal('show');
                    }
                },
                error: function(xhr) {
                    showAlert('danger', xhr.responseJSON?.message || 'Error loading property type');
                }
            });
        }

        // Delete Property Type
        function deletePropertyType(id) {
            if (confirm('Are you sure you want to delete this property type?')) {
                $.ajax({
                    url: `{{ route('property-type.delete', '') }}/${id}`,
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            showAlert('success', response.message);
                            $('#propertyTypeTable').DataTable().ajax.reload();
                        }
                    },
                    error: function(xhr) {
                        showAlert('danger', xhr.responseJSON?.message || 'Error deleting property type');
                    }
                });
            }
        }

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
    </script>
@endpush
