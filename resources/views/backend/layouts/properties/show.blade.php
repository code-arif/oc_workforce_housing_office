@extends('backend.app')

@section('title', 'Property Details - ' . $property->name)

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <!-- Page Header -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Property Details</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('property.list') }}">Properties</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $property->name }}</li>
                        </ol>
                    </div>
                </div>

                <!-- Back Button & Actions -->
                <div class="row mb-3">
                    <div class="col-12">
                        <a href="{{ route('property.list') }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Back to Properties
                        </a>
                        <button class="btn btn-warning btn-sm" onclick="editProperty({{ $property->id }})">
                            <i class="bi bi-pencil"></i> Edit
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="deleteProperty({{ $property->id }})">
                            <i class="bi bi-trash"></i> Delete
                        </button>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="row">
                    <!-- Left: Property Details -->
                    <div class="col-lg-8">
                        <!-- Property Basic Info Card -->
                        <div class="card box-shadow-0 mb-3">
                            <div class="card-header border-bottom">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-house"></i> Basic Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Property Name -->
                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label text-muted">Property Name</label>
                                        <p class="fw-bold text-dark">{{ $property->name }}</p>
                                    </div>

                                    <!-- Property Type -->
                                    <div class="col-lg-6 mb-3">
                                        <label class="form-label text-muted">Property Type</label>
                                        <p>
                                            @if($property->propertyType)
                                                <span class="badge bg-primary">{{ $property->propertyType->name }}</span>
                                            @else
                                                <span class="text-muted">---</span>
                                            @endif
                                        </p>
                                    </div>

                                    <!-- Address -->
                                    <div class="col-lg-12 mb-3">
                                        <label class="form-label text-muted">Address</label>
                                        <p class="text-dark">{{ $property->address ?? '---' }}</p>
                                    </div>

                                    <!-- Description -->
                                    <div class="col-lg-12 mb-3">
                                        <label class="form-label text-muted">Description</label>
                                        <p class="text-dark">{{ $property->description ?? '---' }}</p>
                                    </div>

                                    <!-- Status -->
                                    <div class="col-lg-6">
                                        <label class="form-label text-muted">Status</label>
                                        <p>
                                            @if($property->is_active)
                                                <span class="badge bg-success">Available</span>
                                            @else
                                                <span class="badge bg-danger">Unavailable</span>
                                            @endif
                                        </p>
                                    </div>

                                    <!-- Created At -->
                                    <div class="col-lg-6">
                                        <label class="form-label text-muted">Created At</label>
                                        <p class="text-dark">{{ $property->created_at->format('d M Y, h:i A') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Property Image Card -->
                        @if($property->image_path)
                            <div class="card box-shadow-0 mb-3">
                                <div class="card-header border-bottom">
                                    <h5 class="card-title mb-0">
                                        <i class="bi bi-image"></i> Property Image
                                    </h5>
                                </div>
                                <div class="card-body text-center">
                                    <img src="{{ asset('storage/' . $property->image_path) }}" 
                                         alt="{{ $property->name }}" 
                                         class="img-fluid rounded" 
                                         style="max-height: 400px; object-fit: cover;">
                                </div>
                            </div>
                        @endif

                        <!-- Units & Rooms & Beds Card -->
                        <div class="card box-shadow-0">
                            <div class="card-header border-bottom">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-building"></i> Units, Rooms & Beds
                                </h5>
                            </div>
                            <div class="card-body">
                                @if(count($propertyUnits) > 0)
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Unit</th>
                                                    <th>Room</th>
                                                    <th>Bed</th>
                                                    <th>Tentants</th>
                                                    <th>Bed Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($propertyUnits as $index => $item)
                                                    <tr class="align-middle" onclick="alert('Tentant details are coming soon!!')">
                                                        <td>
                                                            <span class="badge bg-info">{{ $index + 1 }}</span>
                                                        </td>
                                                        <td>
                                                            <strong>{{ $item['unit']->name ?? '---' }}</strong>
                                                            @if($item['unit']->description)
                                                                <br><small class="text-muted">{{ $item['unit']->description }}</small>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($item['room'])
                                                                <strong>{{ $item['room']->room_number }}</strong>
                                                                @if($item['room']->name)
                                                                    <br><small class="text-muted">{{ $item['room']->name }}</small>
                                                                @endif
                                                            @else
                                                                <span class="text-muted">---</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($item['bed'])
                                                                <strong>{{ $item['bed']->bed_number }}</strong>
                                                                @if($item['bed']->bed_label)
                                                                    <br><small class="text-muted">{{ $item['bed']->bed_label }}</small>
                                                                @endif
                                                            @else
                                                                <span class="text-muted">---</span>
                                                            @endif
                                                        </td>
                                                        <td>

                                                            {{-- @if($item['room'])
                                                                @if($item['room']->is_active)
                                                                    <span class="badge bg-success">Available</span>
                                                                @else
                                                                    <span class="badge bg-danger">Unavailable</span>
                                                                @endif
                                                            @else
                                                                ---
                                                            @endif --}}
                                                        </td>
                                                        <td>
                                                            @if($item['bed'])
                                                                @if($item['bed']->is_active)
                                                                <span class="badge bg-danger">Occupied</span>
                                                                @else
                                                                <span class="badge bg-success">Available</span>
                                                                @endif
                                                            @else
                                                                ---
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center text-muted py-4">
                                                            <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                                            <p class="mt-2">No units assigned to this property</p>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center text-muted py-5">
                                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                        <p class="mt-2">No units assigned to this property</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Right: Summary & Stats -->
                    <div class="col-lg-4">
                        <!-- Quick Stats Card -->
                        <div class="card box-shadow-0 mb-3">
                            <div class="card-header border-bottom">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-graph-up"></i> Summary
                                </h5>
                            </div>
                            <div class="card-body">
                                <!-- Total Units -->
                                <div class="mb-4 pb-3 border-bottom">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <p class="text-muted mb-1">Total Units</p>
                                            <h4 class="fw-bold">{{ $property->units->unique()->count() }}</h4>
                                        </div>
                                        <div style="font-size: 2.5rem; color: #007bff;">
                                            <i class="bi bi-building"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Total Rooms -->
                                <div class="mb-4 pb-3 border-bottom">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <p class="text-muted mb-1">Total Rooms</p>
                                            <h4 class="fw-bold">
                                                {{ collect($propertyUnits)->map(fn($item) => $item['room']?->id)->unique()->count() }}
                                            </h4>
                                        </div>
                                        <div style="font-size: 2.5rem; color: #28a745;">
                                            <i class="bi bi-door-closed"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Total Beds -->
                                <div class="mb-4">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <p class="text-muted mb-1">Total Beds</p>
                                            <h4 class="fw-bold">{{ $totalBeds }}</h4>
                                        </div>
                                        <div style="font-size: 2.5rem; color: #ffc107;">
                                            <i class="bi bi-box2"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Unit List Card -->
                        <div class="card box-shadow-0">
                            <div class="card-header border-bottom">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-list-ul"></i> Units Breakdown
                                </h5>
                            </div>
                            <div class="card-body">
                                @php
                                    $unitBreakdown = collect($propertyUnits)->groupBy('unit.id')->map(function($items) {
                                        return [
                                            'unit' => $items[0]['unit'],
                                            'count' => $items->count()
                                        ];
                                    });
                                @endphp

                                @forelse($unitBreakdown as $unitId => $data)
                                    <div class="mb-3 pb-3 border-bottom">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <p class="fw-bold text-dark mb-1">{{ $data['unit']->name }}</p>
                                                <small class="text-muted">{{ $data['count'] }} bed(s)</small>
                                            </div>
                                            <span class="badge bg-info">{{ $data['count'] }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted text-center py-3">No units assigned</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- Metadata Card -->
                        <div class="card box-shadow-0 mt-3">
                            <div class="card-header border-bottom">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-info-circle"></i> Metadata
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3 pb-3 border-bottom">
                                    <p class="text-muted mb-1 small">Created</p>
                                    <p class="fw-bold text-dark">{{ $property->created_at->format('d M Y') }}</p>
                                </div>
                                <div class="mb-3 pb-3 border-bottom">
                                    <p class="text-muted mb-1 small">Last Updated</p>
                                    <p class="fw-bold text-dark">{{ $property->updated_at->format('d M Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-muted mb-1 small">Property ID</p>
                                    <p class="fw-bold text-dark">#{{ $property->id }}</p>
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
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            background-color: #f8f9fa;
            border-radius: 8px 8px 0 0;
            padding: 1.5rem;
        }

        .card-title {
            color: #333;
            font-weight: 600;
        }

        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }

        .badge {
            padding: 0.5rem 0.75rem;
            font-weight: 500;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .btn {
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #5a6268;
        }

        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #000;
        }

        .btn-warning:hover {
            background-color: #e0a800;
            border-color: #d39e00;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bb2d3b;
        }

        .page-header {
            margin-bottom: 1.5rem;
        }

        .breadcrumb {
            margin-bottom: 0;
        }

        .img-fluid {
            max-width: 100%;
            height: auto;
        }
    </style>
@endpush

@push('scripts')
    <script>
        function editProperty(id) {
            // TODO: Implement edit functionality
            // window.location.href = '/property/' + id + '/edit';
            alert('Edit functionality coming soon');
        }

        function deleteProperty(id) {
            if (confirm('Are you sure you want to delete this property?')) {
                // TODO: Implement delete functionality
                alert('Delete functionality coming soon');
            }
        }
    </script>
@endpush
