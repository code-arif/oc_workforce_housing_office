{{-- @extends('backend.layouts.app') --}}
@extends('backend.app')

@section('title', 'Rooms List')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Rooms List</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Index</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Rooms List</li>
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

                                <div
                                    class="card-header border-bottom mb-3 d-flex justify-content-between align-items-center">
                                    <h4 class="mb-0">Room List</h4>
                                    <a href="{{ route('rooms.create') }}" class="btn btn-primary btn-sm">
                                        <i class="bi bi-plus-circle"></i> Add Room
                                    </a>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered" id="roomTable" width="100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Room Number</th>
                                                <th>Description</th>
                                                <th>Gender</th>
                                                <th>Beds</th>
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
        .table-responsive {
            overflow-x: visible;
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

        .alert {
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .beds-badge {
            cursor: pointer;
            position: relative;
            /* padding: 4px 8px;
                border-radius: 4px; */
            transition: background-color 0.2s ease;
        }

        .beds-badge:hover {
            background-color: #e7f3ff;
        }

        .beds-preview {
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 12px;
            min-width: 250px;
            height: 200px;
            overflow-y: scroll;
            z-index: 1000;
            margin-bottom: 8px;
            opacity: 1;
            visibility: visible;
            transition: all 0.3s ease;
            pointer-events: auto;
        }

        .beds-preview::after {
            content: '';
            position: absolute;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 6px solid transparent;
            border-top-color: #ddd;
        }

        .beds-preview::before {
            content: '';
            position: absolute;
            top: calc(100% - 1px);
            left: 50%;
            transform: translateX(-50%);
            border: 5px solid transparent;
            border-top-color: #fff;
        }

        .beds-preview-header {
            font-weight: 600;
            font-size: 13px;
            color: #333;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
        }

        .bed-preview-item {
            padding: 8px 0;
            font-size: 12px;
            color: #555;
            border-bottom: 1px solid #f5f5f5;
        }

        .bed-preview-item:last-child {
            border-bottom: none;
        }

        .bed-preview-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2px;
        }

        .bed-preview-label strong {
            color: #333;
            font-weight: 600;
            min-width: 70px;
        }

        .bed-preview-badge {
            background: #e7f3ff;
            color: #ffffff;
            padding: 7px 6px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            const roomTable = $('#roomTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('rooms.list') }}',
                    type: 'GET'
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'room_number',
                        name: 'room_number'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'gender',
                        name: 'gender'
                    },
                    {
                        data: 'beds_count',
                        name: 'beds_count',
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
                dom: 'lrtip',
                drawCallback: function() {
                    // Reinitialize bed preview after table draw
                    initBedPreviews();
                }
            });

            // Hide success/error alerts after 5 seconds
            setTimeout(() => {
                $('.alert').fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 5000);
        });

        // Initialize bed preview functionality
        function initBedPreviews() {
            $(document).off('click', '.beds-badge');
            $(document).off('click');

            $(document).on('click', '.beds-badge', function(e) {
                e.stopPropagation();

                // Remove any existing previews
                $('.beds-preview').remove();

                const bedsData = JSON.parse($(this).attr('data-beds'));
                const preview = generateBedsPreview(bedsData);
                $(this).append(preview);
            });

            // Close preview when clicking outside
            $(document).on('click', function() {
                $('.beds-preview').remove();
            });
        }

        // Generate beds preview HTML
        function generateBedsPreview(beds) {
            let html = '<div class="beds-preview">';
            html += '<div class="beds-preview-header">Beds Details</div>';
            let blabel = '';
            let bnumber = '';
            if (beds && beds.length > 0) {
                beds.forEach((bed, index) => {
                    if (bed.number !== '---') {
                        bnumber =`<div style="color: #666;"><strong>Bed No:</strong> ${bed.number}</div>`;
                    }

                    html += `
                        <div class="bed-preview-item">
                            <div class="bed-preview-label">
                                <div style="color: #666;"><strong>Room: </strong>${bed.room }</div>
                                ${bnumber}
                                <span class="bed-preview-badge ${bed.is_active == 1 ? 'bg-danger' : 'bg-success'}">${bed.is_active == 1 ? 'Occupied' : 'Available'}</span>
                            </div>
                    `;



                    html += '</div>';
                });
            } else {
                html += '<div style="padding: 10px; text-align: center; color: #999;">No beds available</div>';
            }

            html += '</div>';
            return html;
        }

        // Edit Room - Navigate to edit page
        function editRoom(id) {
            window.location.href = `{{ route('rooms.edit', '') }}/${id}`;
        }

        // delete Confirm
        function showDeleteConfirm(id) {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure you want to delete this room?',
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
            let url = `{{ route('rooms.delete', '') }}/${id}`;
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
                    $('#roomTable').DataTable().ajax.reload();
                },
                error: function(error) {
                    NProgress.done();
                    toastr.error(error.message);
                }
            });
        }

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
    </script>
@endpush
