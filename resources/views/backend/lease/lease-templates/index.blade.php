@extends('backend.app')

@section('content')
<div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2>Lease Templates</h2>
                            <a href="{{ route('lease-templates.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Upload New Template
                            </a>
                        </div>

                        @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endif

                        @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        @if($templates->count() > 0)
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover" id="templatesTable">
                                        <thead>
                                            <tr>
                                                <th width="5%">ID</th>
                                                <th width="25%">Template Name</th>
                                                <th width="15%">Original File</th>
                                                <th width="10%">File Type</th>
                                                <th width="10%">Placeholders</th>
                                                <th width="10%">Signatures</th>
                                                <th width="10%">Status</th>
                                                <th width="10%">Created</th>
                                                <th width="15%" class="text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($templates as $template)
                                            <tr>
                                                <td>{{ $template->id }}</td>
                                                <td>
                                                    <strong>{{ $template->name }}</strong>
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ Str::limit($template->original_filename, 20) }}</small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $template->file_type === 'pdf' ? 'danger' : 'primary' }}">
                                                        {{ strtoupper($template->file_type) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <i class="fas fa-tag"></i> {{ $template->placeholder_count }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div>
                                                        @if($template->hasAdminSignature())
                                                        <span class="badge bg-success" title="Has Admin Signature">
                                                            <i class="fas fa-user-tie"></i>
                                                        </span>
                                                        @endif
                                                        @if($template->hasTenantSignature())
                                                        <span class="badge bg-info" title="Has Tenant Signature">
                                                            <i class="fas fa-user"></i>
                                                        </span>
                                                        @endif
                                                        @if(!$template->hasAdminSignature() && !$template->hasTenantSignature())
                                                        <span class="text-muted">-</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($template->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                    @else
                                                    <span class="badge bg-secondary">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <small>{{ $template->created_at->format('M d, Y') }}</small>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('lease-templates.edit', $template->id) }}" 
                                                        class="btn btn-sm btn-warning" 
                                                        title="Edit Template">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        
                                                        <button type="button" 
                                                                class="btn btn-sm btn-info preview-template" 
                                                                data-id="{{ $template->id }}"
                                                                title="Preview">
                                                            <i class="fas fa-eye"></i>
                                                        </button>

                                                        <button type="button" 
                                                                class="btn btn-sm btn-{{ $template->is_active ? 'secondary' : 'success' }} toggle-status" 
                                                                data-id="{{ $template->id }}"
                                                                data-status="{{ $template->is_active ? 0 : 1 }}"
                                                                title="{{ $template->is_active ? 'Deactivate' : 'Activate' }}">
                                                            <i class="fas fa-{{ $template->is_active ? 'toggle-off' : 'toggle-on' }}"></i>
                                                        </button>
                                                        
                                                        <button type="button" 
                                                                class="btn btn-sm btn-danger delete-template" 
                                                                data-id="{{ $template->id }}"
                                                                data-name="{{ $template->name }}"
                                                                title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="card">
                            <div class="card-body text-center py-5">
                                <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                                <h4>No Templates Found</h4>
                                <p class="text-muted">Get started by uploading your first lease template.</p>
                                <a href="{{ route('lease-templates.create') }}" class="btn btn-primary">
                                    <i class="fas fa-upload"></i> Upload Template
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Template Statistics -->
                @if($templates->count() > 0)
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Templates</h5>
                                <h2>{{ $templates->count() }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Active Templates</h5>
                                <h2>{{ $templates->where('is_active', true)->count() }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">PDF Templates</h5>
                                <h2>{{ $templates->where('file_type', 'pdf')->count() }}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body">
                                <h5 class="card-title">DOCX Templates</h5>
                                <h2>{{ $templates->where('file_type', 'docx')->count() }}</h2>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Template Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="previewContent" style="max-height: 600px; overflow-y: auto !important;">
                <!-- Preview content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the template <strong id="deleteTemplateName"></strong>?</p>
                <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> This action cannot be undone!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Template
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable if available
    if ($.fn.DataTable) {
        $('#templatesTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 10,
            responsive: true
        });
    }

    // Preview Template
    $('.preview-template').on('click', function() {
        const templateId = $(this).data('id');
        
        // Show loading
        $('#previewContent').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x"></i><p class="mt-3">Loading preview...</p></div>');
        $('#previewModal').modal('show');

        // Fetch template content via AJAX
        $.ajax({
            url: `/admin/lease-templates/0/${templateId}/preview`,
            method: 'GET',
            success: function(response) {
                $('#previewContent').html(`
                    <div style="background: white; padding: 40px; max-width: 850px; margin: 0 auto; box-shadow: 0 0 15px rgba(0,0,0,0.1); font-family: 'Times New Roman', serif;">
                        ${response.content || response}
                    </div>
                `);
            },
            error: function() {
                $('#previewContent').html('<div class="alert alert-danger">Failed to load preview</div>');
            }
        });
    });

    // Toggle Status
    $('.toggle-status').on('click', function() {
        const templateId = $(this).data('id');
        const newStatus = $(this).data('status');
        const button = $(this);

        if (confirm('Are you sure you want to ' + (newStatus ? 'activate' : 'deactivate') + ' this template?')) {
            $.ajax({
                url: `/admin/lease-templates/0/${templateId}/toggle-status`,
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    is_active: newStatus
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                },
                error: function() {
                    alert('Failed to update status');
                }
            });
        }
    });

    // Delete Template
    $('.delete-template').on('click', function() {
        const templateId = $(this).data('id');
        const templateName = $(this).data('name');
        
        $('#deleteTemplateName').text(templateName);
        $('#deleteForm').attr('action', `/admin/lease-templates/0/${templateId}`);
        $('#deleteModal').modal('show');
    });

    // Auto-hide alerts
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});
</script>
@endpush

@push('styles')
<style>
    .table tbody tr {
        cursor: pointer;
        transition: background-color 0.2s;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .btn-group .btn {
        margin: 0 2px;
    }

    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
    }

    .badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }

    #previewContent {
        background: white;
        padding: 30px;
        border: 1px solid #ddd;
    }

    .card.bg-primary,
    .card.bg-success,
    .card.bg-info,
    .card.bg-warning {
        border: none;
        margin-bottom: 20px;
    }

    .card.bg-primary .card-body,
    .card.bg-success .card-body,
    .card.bg-info .card-body,
    .card.bg-warning .card-body {
        padding: 1.5rem;
    }

    .card.bg-primary .card-title,
    .card.bg-success .card-title,
    .card.bg-info .card-title,
    .card.bg-warning .card-title {
        font-size: 0.875rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        opacity: 0.9;
    }

    .card h2 {
        font-size: 2rem;
        font-weight: bold;
        margin: 0;
    }
</style>
@endpush