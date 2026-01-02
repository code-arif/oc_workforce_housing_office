@extends('backend.app')

@section('title', 'Roles Management')

@section('content')
<div class="app-content main-content mt-0">
    <div class="side-app">
        <div class="main-container container-fluid">
            <!-- PAGE HEADER -->
            <div class="page-header">
                <div class="page-title">
                    <h4>Roles Management</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Roles</li>
                        </ol>
                    </nav>
                </div>
                <div class="page-btn">
                    @can('user-management.roles.create')
                    <a href="{{ route('user-management.roles.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Add New Role
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
                                <table class="table table-striped table-hover" id="roleTable">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Role Name</th>
                                            <th>Display Name</th>
                                            <th>Description</th>
                                            <th>Permissions</th>
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
@endsection
@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/css/iziToast.min.css">
@endpush
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/js/iziToast.min.css"></script>
    <script src="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/js/iziToast.min.js"></script>
    <script>
        $(document).ready(function() {
            const roleTable = $('#roleTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('user-management.roles.get.data') }}",
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false },
                    {data: 'name', name: 'name'},
                    {data: 'display_name', name: 'display_name'},
                    {data: 'description', name: 'description'},
                    {data: 'permissions', name: 'permissions', orderable: false, searchable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[0, 'asc']],
                pageLength: 10,
                responsive: true
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

        // delete Confirm
        function deleteRole(id) {
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
            let url = `{{ route('user-management.roles.destroy', '') }}/${id}`;
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
                    $('#roleTable').DataTable().ajax.reload();
                },
                error: function(error) {
                    NProgress.done();
                    showToast('danger', error.responseJSON?.message || 'Error deleting  item.');
                }
            });
        }

    </script>
@endpush