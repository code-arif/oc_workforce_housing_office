@extends('backend.app')

@section('title', 'Create Mail Template')

@section('content')
<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            {{-- PAGE-HEADER --}}
            <div class="page-header">
                <div>
                    <h1 class="page-title">Create Mail Template</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="javascript:void(0);">Settings</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('setting.mail-templates.index') }}">Mail Templates</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create</li>
                    </ol>
                </div>
            </div>
            {{-- PAGE-HEADER --}}

            <div class="row">
                <div class="col-lg-12 col-xl-12 col-md-12 col-sm-12">
                    <div class="card box-shadow-0">
                        <div class="card-header">
                            <h4 class="card-title mb-1">New Mail Template</h4>
                            <p class="mb-2 text-muted">Create a new email template with dynamic placeholders</p>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('setting.mail-templates.store') }}">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="name" class="form-label">Template Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                                id="name" name="name" value="{{ old('name') }}" 
                                                placeholder="e.g., Welcome Email, Invoice Notification">
                                            @error('name')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-3">
                                            <label for="subject" class="form-label">Email Subject <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('subject') is-invalid @enderror"
                                                id="subject" name="subject" value="{{ old('subject') }}" 
                                                placeholder="e.g., Welcome to">
                                            @error('subject')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                        id="description" name="description" rows="2" 
                                        placeholder="Brief description of when this template is used">{{ old('description') }}</textarea>
                                    @error('description')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="variables" class="form-label">Available Placeholders</label>
                                    <input type="text" class="form-control @error('variables') is-invalid @enderror"
                                        id="variables" name="variables" value="{{ old('variables') }}" 
                                        placeholder="TENANT_NAME, PROPERTY_NAME, AMOUNT (comma-separated)">
                                    <small class="text-muted">Enter placeholder names separated by commas. Use them in body as: <code>@{{PLACEHOLDER_NAME}}</code></small>
                                    @error('variables')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="body" class="form-label">Email Body <span class="text-danger">*</span></label>
                                    <textarea class="form-control summernote @error('body') is-invalid @enderror"
                                        id="body" name="body" rows="15">{{ old('body') }}</textarea>
                                    @error('body')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-save me-2"></i>Create Template
                                    </button>
                                    <a href="{{ route('setting.mail-templates.index') }}" class="btn btn-secondary">
                                        <i class="fa fa-arrow-left me-2"></i>Back
                                    </a>
                                </div>
                            </form>
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
        // Initialize Summernote editor
        $('.summernote').summernote({
            height: 300,
            placeholder: 'Write your email template content here...',
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'italic', 'clear']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    });
</script>
@endpush
