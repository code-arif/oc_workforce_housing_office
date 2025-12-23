{{-- @extends('backend.layouts.app') --}}
@extends('backend.app')

@section('title', 'Empoyee')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">

            <div class="main-container container-fluid">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Employee Lists</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Index</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Employee</li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    {{-- venue table section --}}
                    <div class="col-12 col-md-12 col-sm-12">
                        <div class="card box-shadow-0">
                            <div class="card-body">

                                <div class="card-header border-bottom mb-3">
                                    <div class="card-options ms-auto">
                                        {{-- <a href="{{ route('employee.list') }}" class="btn btn-outline-success btn-sm" style="margin-right: 10px">
                                             <i class="fa fa-arrow-left"></i> Back to list</a> --}}
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#userModal" id="addUserBtn">Add Employee</button>
                                    </div>
                                </div>

                                <div class="table-responsive custom-scroll">
                                    <table class="table text-nowrap mb-0 table-bordered" id="datatable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Avatar</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th>Password</th>
                                                <th>Address</th>
                                                <th>Team</th>
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

    <!-- Loader Overlay -->
    <div id="ajax-loader"
        style="display:none; position:fixed;
        width:100%; height:100%; background:rgba(255,255,255,0.7);
        z-index:9999; text-align:center;">
        <img src="{{ asset('default/loader.gif') }}" alt="Loading..." style="width:100px; height:100px;">
        {{-- <div>Loading...</div> --}}
    </div>

    <!-- User Create Modal -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="userModalLabel">Add User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="userForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" id="userID">
                    <div class="modal-body">

                        <div class="row">
                            <!-- Name -->
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="name" name="name" class="form-control"
                                    placeholder="Enter full name" required>
                                <span class="text-danger error-text name_error"></span>
                            </div>

                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" class="form-control"
                                    placeholder="Enter email">
                                <span class="text-danger error-text email_error"></span>
                            </div>

                            <!-- Phone -->
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone</label>
                                <input type="text" id="phone" name="phone" class="form-control"
                                    placeholder="Enter phone number">
                                <span class="text-danger error-text phone_error"></span>
                            </div>

                            <!-- Password -->
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="text" id="password" name="password" class="form-control"
                                    placeholder="Enter password" required>
                                <span class="text-danger error-text password_error"></span>
                            </div>

                            <!-- Address -->
                            <div class="col-md-6 mb-3">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" id="address" name="address" class="form-control"
                                    placeholder="Enter address">
                            </div>

                            {{-- Select team --}}
                            <div class="col-md-6 mb-3">
                                <label for="team_id" class="form-label">Select Team</label>
                                <select name="team_id" id="team_id" class="form-control form-select team-select">
                                    <option value="">-- Select Team --</option>
                                    @foreach ($teams as $team)
                                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">You can assign the employee to a team now or later.</small>
                                <span class="text-danger error-text team_id_error"></span>
                            </div>

                            <!-- Avatar -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Image</label>
                                <input type="file" name="avatar" id="avatar" class="form-control dropify"
                                    accept="image">
                                <span class="text-danger error-text image_path_error"></span>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="saveUserBtn">Save User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


{{-- place to push page-specific scripts --}}
@push('scripts')
    {{-- copy functionality --}}
    <script>
        // global event listener for copy button
        $(document).on('click', '.copyBtn', function() {
            let value = $(this).data('copy');
            navigator.clipboard.writeText(value).then(() => {
                // Optional: small success message
                toastr.success("Copied: " + value);
            }).catch(err => {
                toastr.error("Failed to copy!");
            });
        });
    </script>


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
                        url: "{{ route('employee.list') }}",
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
                            data: 'avatar',
                            name: 'avatar'
                        },
                        {
                            data: 'name',
                            name: 'name'
                        },
                        {
                            data: 'email',
                            name: 'email',
                            render: function(data) {
                                if (data && data !== '---') {
                                    return `<div class="d-flex align-items-center justify-content-between">
                                    <span>${data}</span>
                                    <button class="btn btn-sm btn-light copyBtn" data-copy="${data}">
                                    <i class="fe fe-copy"></i>
                                    </button>
                                </div>`;
                                }
                                return '---';
                            }
                        },
                        {
                            data: 'phone',
                            name: 'phone',
                            render: function(data) {
                                if (data && data !== '---') {
                                    return `<div class="d-flex align-items-center justify-content-between">
                            <span>${data}</span>
                            <button class="btn btn-sm btn-light copyBtn" data-copy="${data}">
                                <i class="fe fe-copy"></i>
                            </button>
                        </div>`;
                                }
                                return '---';
                            }
                        },
                        {
                            data: 'password',
                            name: 'password',
                            render: function(data) {
                                if (data && data !== '---') {
                                    return `<div class="d-flex align-items-center justify-content-between">
                            <span>${data}</span>
                            <button class="btn btn-sm btn-light copyBtn" data-copy="${data}">
                                <i class="fe fe-copy"></i>
                            </button>
                        </div>`;
                                }
                                return '---';
                            }
                        },

                        {
                            data: 'address',
                            name: 'address'
                        },
                        {
                            data: 'team',
                            name: 'team'
                        },

                        {
                            data: 'action',
                            name: 'action'
                        },
                    ],

                });
            }

            // Open modal for new User
            $('#addUserBtn').click(function() {
                $('#userModalLabel').text('Create User');
                $('#userForm')[0].reset();
                $('#userID').val('');
                $('.error-text').text('');

                // reset dropify
                let imageInput = $('#avatar').data('dropify');
                if (imageInput) {
                    imageInput.resetPreview();
                    imageInput.clearElement();
                }

                $('#userSubmitBtn').prop('disabled', false).html('Save User');
                $('#userModal').modal('show');
            });


            // Handle User form submission (Create + Update)
            $('#userForm').on('submit', function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                let id = $('#userID').val();

                let url = id ?
                    "{{ route('employee.update', ':id') }}".replace(':id', id) :
                    "{{ route('employee.store') }}";

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
                        $('#userSubmitBtn').prop('disabled', true).html('Processing...');
                    },
                    success: function(response) {
                        if (response.status == 0) {
                            // Validation errors
                            $.each(response.errors, function(prefix, val) {
                                $('span.' + prefix + '_error').text(val[0]);
                            });
                        } else {
                            // Success
                            $('#userModal').modal('hide');
                            $('#userForm')[0].reset();

                            // reset dropify
                            let imageInput = $('#avatar').data('dropify');
                            if (imageInput) {
                                imageInput.resetPreview();
                                imageInput.clearElement();
                            }

                            toastr.success(response.message);
                            $('#datatable').DataTable().ajax.reload();
                        }
                        $('#userSubmitBtn').prop('disabled', false).html('Save User');
                    },
                    error: function(xhr) {
                        $('#userSubmitBtn').prop('disabled', false).html('Save User');
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

            // Edit User
            $(document).on('click', '.editUser', function() {
                var id = $(this).data('id');
                var url = "{{ route('employee.edit', ':id') }}".replace(':id', id);

                $.get(url, function(response) {
                    if (response.status) {
                        $('#userModalLabel').text('Edit User');
                        $('#userID').val(response.data.id);

                        // Fill form fields
                        $('#name').val(response.data.name);
                        $('#email').val(response.data.email);
                        $('#phone').val(response.data.phone);
                        $('#password').val(response.data.password);
                        $('#address').val(response.data.address || '');

                        // Handle Dropify image
                        let drEvent = $('#avatar').dropify();
                        let drInstance = drEvent.data('dropify');
                        drInstance.resetPreview();
                        drInstance.clearElement();

                        if (response.data.avatar) {
                            let baseUrl = "{{ asset('') }}";
                            drInstance.settings.defaultFile = baseUrl + response.data.avatar;
                        }
                        drInstance.destroy();
                        drInstance.init();

                        // Pre-select team if exists
                        if (response.data.teams.length > 0) {
                            $('#team_id').val(response.data.teams[0].id);
                        } else {
                            $('#team_id').val('');
                        }

                        // Show modal
                        $('#userModal').modal('show');
                    } else {
                        toastr.error(response.message || 'Failed to load user data!');
                    }
                }).fail(function() {
                    toastr.error('Something went wrong while loading user data.');
                });
            });

        });

        // delete Confirm
        function showDeleteConfirm(id) {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure you want to delete this empoyee?',
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
            let url = "{{ route('employee.delete', ':id') }}";
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

@push('styles')
    {{-- style for employee avatar image --}}
    <style>
        .avatar-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 50%;
            display: block;
        }
    </style>

    {{-- style for datatable horaizontal scrollbar --}}
    <style>
        /* Make horizontal scrollbar thicker */
        .custom-scroll {
            overflow-x: auto;
        }

        /* For Chrome, Safari, Edge */
        .custom-scroll::-webkit-scrollbar {
            height: 20px;
            /* thickness */
        }

        .custom-scroll::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .custom-scroll::-webkit-scrollbar-thumb {
            background-color: #888;
            border-radius: 10px;
            border: 3px solid #f1f1f1;
        }

        .custom-scroll::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* For Firefox */
        .custom-scroll {
            scrollbar-width: thin;
            /* can use auto or thin */
            scrollbar-color: #888 #f1f1f1;
        }
    </style>
@endpush
