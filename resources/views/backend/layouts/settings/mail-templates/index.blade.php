@extends('backend.app')

@section('title', 'Mail Templates')

@section('content')
<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            {{-- PAGE-HEADER --}}
            <div class="page-header">
                <div>
                    <h1 class="page-title">Mail Templates</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Settings</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Mail Templates</li>
                    </ol>
                </div>
            </div>
            {{-- PAGE-HEADER --}}

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">All Mail Templates</h3>
                            <a href="{{ route('setting.mail-templates.create') }}" class="btn btn-primary">
                                <i class="fa fa-plus me-2"></i>Create Template
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="mail-templates-table" class="table table-bordered text-nowrap w-100">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Subject</th>
                                            <th>Description</th>
                                            <th>Status</th>
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

        </div>
        <!-- CONTAINER CLOSED -->

    </div>
</div>
<!--app-content close-->
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#mail-templates-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('setting.mail-templates.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'name', name: 'name' },
                { data: 'subject', name: 'subject' },
                { data: 'description', name: 'description' },
                { data: 'status', name: 'status', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ]
        });

        // Status toggle
        $(document).on('change', '.status-toggle', function() {
            var id = $(this).data('id');
            var checkbox = $(this);
            
            $.ajax({
                url: "{{ url('setting/mail-templates') }}/" + id + "/toggle-status",
                type: 'PATCH',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                        checkbox.prop('checked', !checkbox.prop('checked'));
                    }
                },
                error: function() {
                    toastr.error('Failed to update status');
                    checkbox.prop('checked', !checkbox.prop('checked'));
                }
            });
        });

        // Delete template
        $(document).on('click', '.delete-btn', function() {
            var id = $(this).data('id');
            
            if (confirm('Are you sure you want to delete this mail template?')) {
                $.ajax({
                    url: "{{ url('setting/mail-templates') }}/" + id,
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            table.ajax.reload();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('Failed to delete mail template');
                    }
                });
            }
        });
    });
</script>
@endpush
