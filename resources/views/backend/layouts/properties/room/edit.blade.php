@extends('backend.app')

@section('title', 'Edit Room')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Edit Room</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Index</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('rooms.list') }}">Rooms</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit Room</li>
                        </ol>
                    </div>
                </div>

                <div id="alertContainer"></div>

                <div class="row">
                    <div class="col-12">
                        <div class="card box-shadow-0">
                            <div class="card-body">

                                <div class="card-header border-bottom mb-3 d-flex justify-content-between align-items-center">
                                    <h4 class="mb-0">Edit Room</h4>
                                </div>

                                <form id="roomForm" action="{{ route('rooms.update', $room->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row">

                                        <div class="col-12 col-lg-8 col-xl-8 mb-3">
                                            <div class="row">
                                                <!-- Room Number -->
                                                <div class="col-12 mb-3">
                                                    <label for="room_number" class="form-label">Room Number <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" id="room_number" name="room_number" class="form-control @error('room_number') is-invalid @enderror"
                                                        placeholder="Enter Room number" value="{{ old('room_number', $room->room_number) }}" required>
                                                    @error('room_number')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <!-- Room Name -->
                                                <div class="col-12 mb-3">
                                                    <label for="name" class="form-label">Room Name (optional)</label>
                                                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                                                        placeholder="Enter room name" value="{{ old('name', $room->name) }}">
                                                    @error('name')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <!-- Gender Designation -->
                                                <div class="col-12 mb-3">
                                                    <label for="gender_designation" class="form-label">Gender Designation (optional)</label>
                                                    <select id="gender_designation" name="gender_designation" class="form-control @error('gender_designation') is-invalid @enderror">
                                                        <option value="">-- Select --</option>
                                                        <option value="Male" {{ old('gender_designation', $room->gender_designation) == 'Male' ? 'selected' : '' }}>Male</option>
                                                        <option value="Female" {{ old('gender_designation', $room->gender_designation) == 'Female' ? 'selected' : '' }}>Female</option>
                                                        <option value="Mixed" {{ old('gender_designation', $room->gender_designation) == 'Mixed' ? 'selected' : '' }}>Mixed</option>
                                                    </select>
                                                    @error('gender_designation')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>

                                                <!-- Room Description -->
                                                <div class="col-12 mb-3">
                                                    <label for="description" class="form-label">Room Description (optional)</label>
                                                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
                                                        placeholder="Enter room description" rows="3">{{ old('description', $room->description) }}</textarea>
                                                    @error('description')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Beds Section -->
                                        <div class="col-12 col-lg-4 col-xl-4 mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="mb-0"><strong>Beds</strong></h6>
                                                <button type="button" class="btn btn-sm btn-success" id="addBedBtn">
                                                    <i class="bi bi-plus-circle"></i> Add Bed
                                                </button>
                                            </div>

                                            <div id="bed-container" class="border p-3" style="background-color: #f8f9fa;">
                                                <!-- Beds will be added here dynamically -->
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-footer mt-4">
                                        <a href="{{ route('rooms.list') }}" class="btn btn-light">Cancel</a>
                                        <button type="submit" class="btn btn-primary">Update Room</button>
                                    </div>
                                </form>

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
        .bed-item {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 12px;
            position: relative;
            transition: all 0.3s ease;
        }

        .bed-item:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .bed-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .bed-item-title {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .bed-remove-btn {
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
            font-size: 18px;
            padding: 0;
            transition: all 0.2s ease;
        }

        .bed-remove-btn:hover {
            color: #a71d2a;
            transform: scale(1.2);
        }

        .bed-fields {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .bed-fields .form-group {
            margin-bottom: 8px;
        }

        .bed-fields label {
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 4px;
            color: #555;
        }

        .bed-fields input {
            font-size: 13px;
            padding: 6px 8px;
        }

        .bed-item-empty {
            text-align: center;
            padding: 30px;
            color: #999;
            font-size: 14px;
        }

        .form-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            border-top: 1px solid #e0e0e0;
            padding-top: 20px;
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        .form-control.is-invalid:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
    </style>
@endpush

@push('scripts')
    <script>
        let bedCount = 0;

        $(document).ready(function() {
            // Load existing beds
            const existingBeds = @json($room->beds);
            loadBedsFromData(existingBeds);

            // Add bed input
            document.getElementById('addBedBtn').addEventListener('click', function() {
                addBedInput();
            });
        });

        function addBedInput(bedData = null) {
            const bedContainer = document.getElementById('bed-container');

            // Clear empty message if exists
            const emptyMsg = bedContainer.querySelector('.bed-item-empty');
            if (emptyMsg) {
                emptyMsg.remove();
            }

            bedCount++;
            const bedId = bedData?.id || '';
            const bedLabel = bedData?.bed_label || '';
            const bedNumber = bedData?.bed_number || '';

            const bedHTML = `
                <div class="bed-item" data-bed-id="${bedId}">
                    <div class="bed-item-header">
                        <span class="bed-item-title">Bed #${bedCount}</span>
                        <button type="button" class="bed-remove-btn" onclick="removeBed(this)">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                    <div class="bed-fields">
                       
                        <div class="form-group">
                            <label for="bed_number_${bedCount}">Bed Number</label>
                            <input type="text"
                                id="bed_number_${bedCount}"
                                name="beds[${bedCount}][bed_number]"
                                class="form-control form-control-sm"
                                placeholder="e.g., 1, 2, 3"
                                value="${bedNumber}">
                        </div>
                    </div>
                    ${bedId ? `<input type="hidden" name="beds[${bedCount}][id]" value="${bedId}">` : ''}
                </div>
            `;

            bedContainer.insertAdjacentHTML('beforeend', bedHTML);
        }

        function removeBed(btn) {
            btn.closest('.bed-item').remove();

            // Show empty message if no beds
            const bedContainer = document.getElementById('bed-container');
            if (bedContainer.children.length === 0) {
                bedContainer.innerHTML = '<div class="bed-item-empty">No beds added. Click "Add Bed" to start adding beds.</div>';
            }
        }

        function clearBeds() {
            const bedContainer = document.getElementById('bed-container');
            bedContainer.innerHTML = '<div class="bed-item-empty">No beds added. Click "Add Bed" to start adding beds.</div>';
            bedCount = 0;
        }

        function loadBedsFromData(beds) {
            clearBeds();
            bedCount = 0;

            if (beds && beds.length > 0) {
                beds.forEach(bed => {
                    addBedInput(bed);
                });
            } else {
                clearBeds();
            }
        }
    </script>
@endpush
