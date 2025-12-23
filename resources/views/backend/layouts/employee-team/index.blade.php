{{-- @extends('backend.layouts.app') --}}
@extends('backend.app')

@section('title', 'Assinging Employee')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">User Who Assinging Into Team</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Index</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Emplyee</li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card box-shadow-0">
                            <div class="card-body">

                                <div
                                    class="card-header border-bottom mb-3 d-flex justify-content-between align-items-center">
                                    <h4 class="mb-0">Emloyee List</h4>
                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#teamModal" id="addTeamBtn">Assing Employee</button>
                                </div>


                                <div class="table-responsive">
                                    <table class="table text-nowrap mb-0 table-bordered" id="datatable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Team Name</th>
                                                <th>Description</th>
                                                <th>Unique ID</th>
                                                <th>Users</th>
                                                <th>Action</th>
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


    <!-- User Create Modal -->
    <div class="modal fade" id="teamModal" tabindex="-1" aria-labelledby="teamModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="teamModalLabel">Add Team</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="teamForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" id="teamID">
                    <div class="modal-body">

                        <div class="row">
                            <!-- Name -->
                            <div class="col-12 mb-3">
                                <label for="name" class="form-label">Team Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="name" name="name" class="form-control"
                                    placeholder="Enter full name" required>
                            </div>


                            <!-- Desciption -->
                            <div class="col-12 mb-3">
                                <label for="password" class="form-label">Desciption </label>
                                <textarea name="description" id="description" class="form-control" placeholder="Enter team details"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="saveTeamBtn">Save Team</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


@push('scripts')
    {{-- datatable and form submission --}}
    <script>
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                }
            });

            if (!$.fn.DataTable.isDataTable('#datatable')) {
                let dTable = $('#datatable').DataTable({
                    order: [],
                    lengthMenu: [
                        [10, 25, 50, 100, -1],
                        [10, 25, 50, 100, "All"]
                    ],
                    processing: true,
                    responsive: true,
                    serverSide: true,

                    language: {
                        processing: `<div class="text-center">
                        <img src="{{ asset('default/loader.gif') }}" alt="Loader" style="width: 50px;">
                        </div>`
                    },

                    scroller: {
                        loadingIndicator: false
                    },
                    pagingType: "full_numbers",
                    dom: "<'row justify-content-between table-topbar'<'col-md-4 col-sm-3'l><'col-md-5 col-sm-5 px-0'f>>tipr",

                    ajax: {
                        url: "{{ route('team.list') }}",
                        type: "GET",
                        data: function(d) {
                            d.role = $('#roleFilter').val();
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
                            data: 'description',
                            name: 'description'
                        },
                        {
                            data: 'unique_id',
                            name: 'unique_id'
                        },
                        {
                            data: 'action',
                            name: 'action'
                        },
                    ],

                });
            }

            // Open modal for new User
            $('#addTeamBtn').click(function() {
                $('#teamModalLabel').text('Create Team');
                $('#teamForm')[0].reset();
                $('#teamID').val('');
                $('.error-text').text('');

                $('#saveTeamBtn').prop('disabled', false).html('Save Team');
                $('#teamModal').modal('show');
            });


            // Handle User form submission (Create + Update)
            $('#teamForm').on('submit', function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                let id = $('#teamID').val();

                let url = id ?
                    "{{ route('team.update', ':id') }}".replace(':id', id) :
                    "{{ route('team.store') }}";

                if (id) {
                    formData.append('_method',
                        'POST');
                }

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        $('span.error-text').text('');
                        $('#teamSubmitBtn').prop('disabled', true).html('Processing...');
                    },
                    success: function(response) {
                        if (response.status == 0) {
                            $.each(response.errors, function(prefix, val) {
                                $('span.' + prefix + '_error').text(val[0]);
                            });
                        } else {
                            // Success
                            $('#teamModal').modal('hide');
                            $('#teamForm')[0].reset();

                            toastr.success(response.message);
                            $('#datatable').DataTable().ajax.reload();
                        }
                        $('#teamSubmitBtn').prop('disabled', false).html('Save Team');
                    },
                    error: function(xhr) {
                        $('#saveTeamBtn').prop('disabled', false).html('Save Tram');
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

            // Edit Team - Load existing data
            $(document).on('click', '.editTeam', function() {
                var id = $(this).data('id');
                var url = "{{ route('team.edit', ':id') }}".replace(':id', id);

                $.get(url, function(response) {
                    if (response.success) {
                        $('#teamModalLabel').text('Edit Team');
                        $('#teamID').val(response.data.id);

                        // Fill form fields
                        $('#name').val(response.data.name);
                        $('#desciption').val(response.data.desciption);

                        // Show modal
                        $('#teamModal').modal('show');
                    } else {
                        toastr.error('Failed to load user data!');
                    }
                }).fail(function() {
                    toastr.error('Something went wrong while loading team data.');
                });
            });
        });

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
            let url = "{{ route('team.delete', ':id') }}";
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
