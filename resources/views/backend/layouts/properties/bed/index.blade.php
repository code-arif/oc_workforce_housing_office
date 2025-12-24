{{-- @extends('backend.layouts.app') --}}
@extends('backend.app')

@section('title', 'Beds List')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Beds List</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Index</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Beds List</li>
                        </ol>
                    </div>
                </div>

                <div id="alertContainer">
                    @if ($message = Session::get('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ $message }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if ($message = Session::get('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ $message }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card box-shadow-0">
                            <div class="card-body">

                                <div class="card-header border-bottom mb-3 d-flex justify-content-between align-items-center">
                                    <h4 class="mb-0">Room List</h4>
                                    <div>
                                        <button class="btn btn-danger btn-sm me-2" id="bulkDeleteBtn" style="display: none;">
                                            <i class="bi bi-trash"></i> Delete Selected (<span id="selectedCount">0</span>)
                                        </button>
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#bedModal" id="addBedBtn">Add Bed</button>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered" id="bedTable" width="100%">
                                        <thead>
                                            <tr>
                                                <th style="width: 50px;">
                                                    <input type="checkbox" id="selectAllBeds" class="form-check-input" title="Select all">
                                                </th>
                                                <th>#</th>
                                                <th>Bed Number</th>
                                                <th>Room</th>
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
        @include('backend.layouts.properties.bed.create')
@endsection

@push('styles')
    <style>
        .select2-container {
            width: 100% !important;
        }
        .form-check-input {
            margin-left: 0.75rem !important;
            margin-top: .3rem !important;
            position: inherit;
        }
    </style>
@endpush

@push('scripts')
    <script>
        let selectedBeds = [];

        $(document).ready(function() {
            // Initialize DataTable
            const bedTable = $('#bedTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("beds.list") }}',
                    type: 'GET'
                },
                columns: [
                    {
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            return '<input type="checkbox" class="bed-checkbox form-check-input" data-id="' + data + '">';
                        }
                    },
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'bed_number', name: 'bed_number' },
                    { data: 'room', name: 'room' },
                    { data: 'description', name: 'description' },
                    { data: 'status', name: 'status', orderable: false, searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[1, 'desc']],
                pageLength: 10,
                dom: 'lrtip',
                drawCallback: function() {
                    updateSelectAllCheckbox();
                }
            });

            // Handle select all checkbox
            $('#selectAllBeds').on('change', function() {
                let isChecked = this.checked;
                $('.bed-checkbox').prop('checked', isChecked);
                updateSelectedBeds();
            });

            // Handle individual checkbox changes
            $(document).on('change', '.bed-checkbox', function() {
                updateSelectedBeds();
                updateSelectAllCheckbox();
            });

            function updateSelectAllCheckbox() {
                let totalCheckboxes = $('.bed-checkbox').length;
                let checkedCheckboxes = $('.bed-checkbox:checked').length;
                $('#selectAllBeds').prop('checked', totalCheckboxes > 0 && totalCheckboxes === checkedCheckboxes);
            }

            function updateSelectedBeds() {
                selectedBeds = [];
                $('.bed-checkbox:checked').each(function() {
                    selectedBeds.push($(this).data('id'));
                });
                
                let count = selectedBeds.length;
                $('#selectedCount').text(count);
                
                if (count > 0) {
                    $('#bulkDeleteBtn').show();
                } else {
                    $('#bulkDeleteBtn').hide();
                }
            }

            // Handle bulk delete
            $('#bulkDeleteBtn').on('click', function() {
                if (selectedBeds.length === 0) {
                    toastr.warning('Please select at least one bed to delete');
                    return;
                }

                Swal.fire({
                    title: 'Are you sure?',
                    text: `You are about to delete ${selectedBeds.length} bed(s). This cannot be undone!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete them!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route("beds.bulk-delete") }}',
                            type: 'POST',
                            data: {
                                ids: selectedBeds,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                toastr.success(`Successfully deleted ${response.count} bed(s)`, 'Success');
                                bedTable.ajax.reload();
                                selectedBeds = [];
                                $('#selectAllBeds').prop('checked', false);
                                $('#bulkDeleteBtn').hide();
                            },
                            error: function(error) {
                                toastr.error('An error occurred while deleting beds', 'Error');
                                console.log(error);
                            }
                        });
                    }
                });
            });
            
            // Add Property Type
            $('#addBedBtn').click(function() {
                $('#bedForm')[0].reset();
                $('#bedId').val('');
                $('#modalTitle').text('Add Bed');
                $('#submitBtn').text('Save');
                $('#bedModal').modal('show');
            });

            // Submit Form
            $('#bedForm').on('submit', function(e) {
                e.preventDefault();

                const id = $('#bedId').val();
                const url = id ? `{{ route('beds.update', '') }}/${id}` : '{{ route("beds.store") }}';
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
                            $('#bedModal').modal('hide');
                            bedTable.ajax.reload();
                            $('#bedForm')[0].reset();
                        }else{
                            toastr.error(response.message);
                        }

                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors || {};
                        console.log(errors);
                        
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

            // Hide success/error alerts after 5 seconds
            setTimeout(() => {
                $('.alert').fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 5000);
        });

        function editbed(id) {
            $.ajax({
                url: `{{ route('beds.edit', '') }}/${id}`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        $('#bedId').val(response.data.id);
                        $('#room_id').val(response.data.room_id).trigger('change');
                        // $('#bed_label').val(response.data.bed_label);
                        $('#bed_number').val(response.data.bed_number);
                        $('#description').val(response.data.description);
                        $('#isActive').prop('checked', response.data.is_active);
                        $('#modalTitle').text('Edit Bed');
                        $('#submitBtn').text('Update Bed');
                        $('#bedModal').modal('show');
                        // toastr.success(response.message);
                    }

                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Error loading Bed');
                    // showAlert('danger', xhr.responseJSON?.message || 'Error loading bed');
                }
            });
        }

        // delete Confirm
        function showDeleteConfirm(id) {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure you want to delete this bed?',
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
            let url = `{{ route('beds.delete', '') }}/${id}`;
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
                    $('#bedTable').DataTable().ajax.reload();
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
                text: 'If you update this, it will be Changed.',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, update it!',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ route('beds.toggle.status', '') }}/${id}`,
                        type: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#bedTable').DataTable().ajax.reload();
                                toastr.success(response.message);
                            }
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.message || 'Error toggling status');
                            // showAlert('danger', xhr.responseJSON?.message || 'Error toggling status');
                        }
                    });
                }
            });
            
        }

        $(document).ready(function() {
            $('.select3').select2({
                // minimumResultsForSearch: -1,
                width: '100%',
                dropdownParent: $('#bedModal')                
            });
        })
    </script>
@endpush