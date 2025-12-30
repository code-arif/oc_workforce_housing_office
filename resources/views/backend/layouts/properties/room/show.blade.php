@extends('backend.app')

@section('title', 'Room Details - ' . $room->room_number)

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <!-- Page Header -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Room Details</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('rooms.list') }}">Rooms</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $room->room_number }}</li>
                        </ol>
                    </div>
                </div>

                <!-- Back Button -->
                <div class="row mb-3">
                    <div class="col-12">
                        <a href="history.back()" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Back to Rooms
                        </a>
                        
                    </div>
                </div>

                <!-- Main Content Row -->
                <div class="row">
                    <!-- Room Information Card -->
                    <div class="col-lg-8">
                        <div class="card box-shadow-0">
                            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-0">Room Information</h4>
                                </div>
                                
                            </div>
                            <div class="card-body">
                                <!-- Room Number -->
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <div class="info-item">
                                            <label class="info-label">Room Number</label>
                                            <div class="info-value">
                                                <span class="badge bg-primary fs-6">{{ $room->room_number }}</span>
                                            </div>
                                            
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-item">
                                            <label class="info-label">Unit</label>
                                            <div class="info-value">
                                                <div class="info-value">
                                                    <span class="badge bg-light fs-6">{{ $room->unit->name }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="info-item">
                                            <label class="info-label">Status</label>
                                            <div class="info-value">
                                                <span class="badge {{ $room->is_active ? 'bg-success' : 'bg-danger' }} fs-6">
                                                    {{ $room->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Room Name -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="info-item">
                                            <label class="info-label">Room Name</label>
                                            <div class="info-value">
                                                {{ $room->name ?? 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Description -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="info-item">
                                            <label class="info-label">Description</label>
                                            <div class="info-value">
                                                <p class="text-muted">
                                                    {{ $room->description ?? 'No description provided' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Gender Designation -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-item">
                                            <label class="info-label">Gender Designation</label>
                                            <div class="info-value">
                                                @if($room->gender_designation)
                                                    <span class="badge bg-info fs-6">{{ $room->gender_designation }}</span>
                                                @else
                                                    <span class="text-muted">Not specified</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Beds Information Card -->
                        <div class="card box-shadow-0 mt-4">
                            <div class="card-header border-bottom">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="card-title mb-0">Beds Information</h4>
                                    <span class="badge bg-info fs-6">{{ $room->beds->count() }} Total</span>
                                </div>
                            </div>
                            <div class="card-body">
                                @if($room->beds->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Bed Number</th>
                                                    <th>Status</th>
                                                    <th>Created At</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($room->beds as $index => $bed)
                                                    <tr class="align-middle" onclick="alert('Tenant detail are coming soon!!')">
                                                        <td>
                                                            <span class="badge bg-light text-dark">{{ $index + 1 }}</span>
                                                        </td>
                                                        <td>
                                                            <span class="fw-bold">{{ $bed->bed_number ?? 'N/A' }}</span><br>
                                                            <span class="text-muted fs-9">{{ $bed->bed_label ?? 'N/A' }}</span>
                                                        </td>
                                                        <td>
                                                            <span class="badge {{ $bed->is_active ? 'bg-danger' : 'bg-success' }}">
                                                                {{ $bed->is_active ? 'Occupied' : 'Available' }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <small class="text-muted">
                                                                {{ $bed->created_at->format('M d, Y') }}
                                                            </small>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="alert alert-info" role="alert">
                                        <i class="bi bi-info-circle"></i> No beds added to this room yet.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <!-- Quick Stats Card -->
                        <div class="card box-shadow-0">
                            <div class="card-header border-bottom">
                                <h5 class="card-title mb-0">Quick Stats</h5>
                            </div>
                            <div class="card-body">
                                <div class="stat-item mb-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Total Beds</span>
                                        <h3 class="mb-0 text-primary fw-bold">{{ $room->beds->count() }}</h3>
                                    </div>
                                </div>
                                <hr>
                                <div class="stat-item mb-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Available Beds</span>
                                        <h3 class="mb-0 text-success fw-bold">{{ $room->beds->where('is_active', 0)->count() }}</h3>
                                    </div>
                                </div>
                                <hr>
                                <div class="stat-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted">Occupied Beds</span>
                                        <h3 class="mb-0 text-danger fw-bold">{{ $room->beds->where('is_active', 1)->count() }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Details Card -->
                        <div class="card box-shadow-0 mt-4">
                            <div class="card-header border-bottom">
                                <h5 class="card-title mb-0">Additional Info</h5>
                            </div>
                            <div class="card-body">
                                <div class="detail-item mb-3">
                                    <label class="detail-label">Created Date</label>
                                    <p class="mb-0">{{ $room->created_at->format('M d, Y H:i A') }}</p>
                                </div>
                                <hr>
                                <div class="detail-item">
                                    <label class="detail-label">Last Updated</label>
                                    <p class="mb-0">{{ $room->updated_at->format('M d, Y H:i A') }}</p>
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
        .info-item {
            margin-bottom: 1.5rem;
        }

        .info-label {
            display: block;
            font-weight: 600;
            color: #666;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .info-value {
            font-size: 1rem;
            color: #333;
            line-height: 1.6;
        }

        .stat-item {
            padding: 0.5rem 0;
        }

        .detail-label {
            display: block;
            font-weight: 600;
            color: #666;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .card {
            border: none;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: #f8f9fa;
            border-radius: 8px 8px 0 0;
        }

        .table thead {
            background-color: #f8f9fa;
        }

        .table tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }

        .table tbody tr:hover {
            background-color: #f9fafc;
        }

        .page-header {
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1a1a1a;
        }

        .badge {
            padding: 0.5rem 0.75rem;
            font-weight: 500;
        }

        hr {
            margin: 1rem 0;
            border: none;
            border-top: 1px solid #e5e7eb;
        }
    </style>
@endpush

@push('scripts')
    <script>
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
                    deleteRoom(id);
                }
            });
        }

        function deleteRoom(id) {
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
                    setTimeout(() => {
                        window.location.href = '{{ route('rooms.list') }}';
                    }, 1000);
                },
                error: function(error) {
                    NProgress.done();
                    toastr.error(error.responseJSON?.message || 'Error deleting room');
                }
            });
        }
    </script>
@endpush
