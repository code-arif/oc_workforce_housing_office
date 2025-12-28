@extends('backend.app')

@section('title', 'Create Property')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <!-- Page Header -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Create Property</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('property.list') }}">Properties</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Create</li>
                        </ol>
                    </div>
                </div>

                <!-- Back Button -->
                <div class="row mb-3">
                    <div class="col-12">
                        <a href="{{ route('property.list') }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-left"></i> Back to Properties
                        </a>
                    </div>
                </div>

                <!-- Alerts -->
                <div id="alertContainer">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-start">
                                <div class="flex-grow-1">
                                    <h5 class="alert-heading mb-2">
                                        <i class="bi bi-exclamation-triangle"></i> Validation Error!
                                    </h5>
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li class="mb-1">{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Main Card -->
                <div class="row">
                    <div class="col-12">
                        <div class="card box-shadow-0">
                            <!-- Tabs Navigation -->
                            <div class="card-header border-bottom">
                                <h5 class="card-title mb-0">Property Create</h5>
                            </div>
                            <div class="card-body">
                                <div class="card-body">
                                    <form action="{{ route('property.store') }}" method="POST" id="propertyInfoForm" enctype="multipart/form-data">
                                        @csrf
                                        <div class="row">
                                            <!-- Property Type -->
                                            <div class="col-lg-6 mb-3">
                                                <label for="type_id" class="form-label">Property Type <span
                                                        class="text-danger">*</span></label>
                                                <select name="type_id" id="type_id" class="form-control select3" required>
                                                    <option value="">-- Select Property Type --</option>
                                                    @foreach ($propertyTypes as $type)
                                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="invalid-feedback" id="type_id_error" style="display: block;">
                                                </div>
                                            </div>

                                            <!-- Property Name -->
                                            <div class="col-lg-6 mb-3">
                                                <label for="name" class="form-label">Property Name <span
                                                        class="text-danger">*</span></label>
                                                <input type="text" name="name" id="name" class="form-control"
                                                    placeholder="Enter property name" required>
                                                <div class="invalid-feedback" id="name_error" style="display: block;"></div>
                                            </div>

                                            <!-- Property Address -->
                                            <div class="col-lg-12 mb-3">
                                                <label for="address" class="form-label">Property Address</label>
                                                <input type="text" name="address" id="address" class="form-control"
                                                    placeholder="Enter property address">
                                                <div class="invalid-feedback" id="address_error" style="display: block;">
                                                </div>
                                            </div>

                                            <!-- Description -->
                                            <div class="col-lg-12 mb-3">
                                                <label for="description" class="form-label">Description</label>
                                                <textarea name="description" id="description" class="form-control" placeholder="Enter property description"
                                                    rows="4"></textarea>
                                                <div class="invalid-feedback" id="description_error"
                                                    style="display: block;"></div>
                                            </div>

                                            <!-- Image Upload -->
                                            <div class="col-lg-12 mb-3">
                                                <label for="image_path" class="form-label">Property Image (Optional)</label>
                                                <input type="file" name="image_path" id="image_path" class="form-control"
                                                    accept="image/*">
                                                <small class="text-muted">Accepted: jpg, jpeg, png, gif (Max: 5MB)</small>
                                                <div class="invalid-feedback" id="image_path_error" style="display: block;">
                                                </div>
                                            </div>

                                            <!-- Image Preview -->
                                            <div class="col-lg-12 mb-3" id="imagePreviewContainer" style="display: none;">
                                                <img id="imagePreview" src="" alt="Preview"
                                                    style="max-width: 300px; max-height: 300px; border-radius: 8px; border: 1px solid #ddd; padding: 10px;">
                                            </div>
                                        </div>

                                        <div class="form-footer mt-4">
                                            <a href="{{ route('property.list') }}" class="btn btn-light">Cancel</a>
                                            <button type="submit" class="btn btn-primary">Save 
                                            </button>
                                        </div>
                                    </form>
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
        .select2-container {
            width: 100% !important;
        }

        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .nav-tabs {
            border-bottom: 2px solid #b9b9b9;
        }

        .nav-link {
            color: #666;
            border: none;
            border-bottom: 3px solid transparent;
            padding: 1rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: #007bff;
            border-bottom-color: #e5e7eb;
        }

        .nav-link.active {
            color: #007bff;
            border-bottom-color: var(--primary-bg-color) !important;
            background-color: var(--primary-bg-color) !important;
        }

        .card-header-tabs {
            line-height: 0rem !important;
            margin: 0rem 1rem !important;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.75rem;
        }

        .form-control,
        .select2-container--default .select2-selection--single {
            border-radius: 6px;
            border: 1px solid #ddd;
            /* padding: 0.75rem; */
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: none !important;
        }

        .invalid-feedback.show {
            display: block !important;
        }

        .form-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            border-top: 1px solid #e0e0e0;
            padding-top: 2rem;
        }

        .units-list-container {
            max-height: 600px;
            overflow-y: auto;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
            background-color: #f9fafc;
        }

        .unit-item {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .unit-item:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .unit-item-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 0.75rem;
        }

        .unit-item-title {
            /* font-weight: 600; */
            color: #333;
            font-size: 1rem;
        }

        .unit-item-remove {
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
            padding: 0;
            font-size: 1.2rem;
            transition: all 0.2s ease;
        }

        .unit-item-remove:hover {
            color: #a71d2a;
            transform: scale(1.2);
        }

        .room-badge {
            display: inline-block;
            background-color: #e7f3ff;
            color: #0066cc;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            margin: 0.25rem 0.25rem 0.25rem 0;
            font-weight: 500;
        }

        .bed-badge {
            display: inline-block;
            background-color: #f0f3ff;
            color: #5555ff;
            padding: 0.3rem 0.6rem;
            border-radius: 4px;
            font-size: 0.75rem;
            margin: 0.25rem 0.25rem 0.25rem 0;
        }

        .section-label {
            font-weight: 600;
            color: #666;
            font-size: 0.85rem;
            text-transform: uppercase;
            margin-top: 0.75rem;
            margin-bottom: 0.5rem;
        }

        .text-danger {
            color: #dc3545;
        }

        .select2-container--default .select2-selection--single {
            height: 38px;
            display: flex;
            align-items: center;
        }

        .select2-container--default .select2-selection--multiple {
            min-height: 38px;
        }

        #imagePreview {
            object-fit: cover;
        }

        .btn {
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }

        .btn-success:hover {
            background-color: #218838;
            border-color: #218838;
        }

        .tab-pane {
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        let selectedUnits = [];

        $(document).ready(function() {
            // Initialize Select2
            $('.select3').select2({
                width: '100%',
                allowClear: true,
                placeholder: 'Select an option'
            });

            // Image preview
            $('#image_path').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#imagePreview').attr('src', e.target.result);
                        $('#imagePreviewContainer').show();
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
@endpush
