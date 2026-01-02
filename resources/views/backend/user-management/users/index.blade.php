@extends('backend.app')

@section('title', 'Users Management')

@section('content')
<div class="app-content main-content mt-0">
    <div class="side-app">
        <div class="main-container container-fluid">
            <!-- PAGE HEADER -->
            <div class="page-header">
                <div class="page-title">
                    <h4>Users Management</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Users</li>
                        </ol>
                    </nav>
                </div>
                <div class="page-btn">
                    @can('user-management.users.create')
                    <a href="#" data-bs-toggle="modal" data-bs-target="#userModal" class="btn btn-primary btn-sm" id="addUserBtn">
                        <i class="fa fa-plus"></i> Add New User
                    </a>
                    @endcan
                </div>
            </div>
            <!-- /PAGE HEADER -->

            <!-- TABLE -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            @if ($message = Session::get('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ $message }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            @if ($message = Session::get('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ $message }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <div class="">
                                <table class="table table-striped table-hover" id="userTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Roles</th>
                                            <th>Actions</th>
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
            <!-- /TABLE -->
        </div>
    </div>
</div>
@include('backend.user-management.users.create')
@endsection
@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/css/iziToast.min.css">
@endpush
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/js/iziToast.min.css"></script>
    <script src="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/js/iziToast.min.js"></script>
    <script>
        $(document).ready(function() {
            const userTable = $('#userTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('user-management.users.get.data') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false },
                    {data: 'name', name: 'name'},
                    {data: 'email', name: 'email'},
                    {data: 'phone', name: 'phone'},
                    {data: 'roles', name: 'roles', orderable: false, searchable: false},
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[0, 'asc']],
                pageLength: 10,
                responsive: true
            });

            // Add User
            $('#addUserBtn').click(function() {
                $('#userForm')[0].reset();
                $('#userId').val('');
                $('#modalTitle').text('Create User');
                $('#submitBtn').text('Save');
                $('#userModal').modal('show');
            });

            // Submit Form
            $('#userForm').on('submit', function(e) {
                e.preventDefault();

                const id = $('#userId').val();
                const url = id ? `{{ route('user-management.users.update', '') }}/${id}` :
                    '{{ route('user-management.users.store') }}';
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
                            $('#userModal').modal('hide');
                            userTable.ajax.reload();
                            $('#userForm')[0].reset();
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
        })
        
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
        function editUser(id) {
            $.ajax({
                url: `{{ route('user-management.users.edit', '') }}/${id}`,
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {                    
                    if (response.success) {
                        $('#password').removeAttr('required');
                        $('#password_confirmation').removeAttr('required');
                        $('#userId').val(response.data.id);
                        $('#name').val(response.data.name);
                        $('#email').val(response.data.email);
                        $('#phone').val(response.data.phone);
                        $('input[name="roles[]"][value="' + response.data.roles.map(role => role.name).join('"], input[name="roles[]"][value="') + '"]').prop('checked', true);
                        $('#modalTitle').text('Edit User');
                        $('#submitBtn').text('Update');
                        $('#userModal').modal('show');
                        // toastr.success(resp.message);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Error loading user');
                    showAlert('danger', xhr.responseJSON?.message || 'Error loading user');
                }
            });
        }

        // delete Confirm
        function deleteUser(id) {
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
            let url = `{{ route('user-management.users.destroy', '') }}/${id}`;
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
                    $('#userTable').DataTable().ajax.reload();
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
                                $('#userTable').DataTable().ajax.reload();
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