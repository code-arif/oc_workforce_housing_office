@extends('backend.app')

@section('content')
<div class="app-content main-content mt-0">
    <div class="side-app">
        <div class="main-container container-fluid">
            <!-- PAGE HEADER -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Lease Templates</h1>
                    <p class="text-muted">Manage pre-formatted lease document templates</p>
                </div>
                <div class="ms-auto pageheader-btn">
                    <a href="{{ route('lease-templates.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Upload New Template
                    </a>
                </div>
            </div>
            <!-- PAGE HEADER END -->

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="stat-icon bg-primary">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number">{{ $templates->count() }}</div>
                                <div class="stat-label">Total Templates</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="stat-icon bg-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number">{{ $templates->where('is_active', true)->count() }}</div>
                                <div class="stat-label">Active Templates</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="stat-icon bg-warning">
                                <i class="fas fa-file-pdf"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number">{{ $templates->where('file_type', 'pdf')->count() }}</div>
                                <div class="stat-label">PDF Templates</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="stat-icon bg-info">
                                <i class="fas fa-file-word"></i>
                            </div>
                            <div class="stat-content">
                                <div class="stat-number">{{ $templates->where('file_type', 'docx')->count() }}</div>
                                <div class="stat-label">DOCX Templates</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Templates Grid -->
            <div class="row">
                @forelse($templates as $template)
                <div class="col-xl-4 col-lg-6 col-md-6">
                    <div class="card template-card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="template-header-info">
                                    <h5 class="template-title mb-1">{{ $template->name }}</h5>
                                    <div class="template-meta">
                                        <span class="badge badge-{{ $template->file_type == 'pdf' ? 'danger' : 'primary' }}">
                                            {{ strtoupper($template->file_type) }}
                                        </span>
                                        <span class="badge badge-{{ $template->is_active ? 'success' : 'secondary' }}">
                                            {{ $template->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('lease-templates.edit', $template->id) }}">
                                                <i class="fas fa-edit text-primary"></i> Edit Template
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item preview-template" href="#" data-id="{{ $template->id }}">
                                                <i class="fas fa-eye text-info"></i> Preview
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item duplicate-template" href="#" data-id="{{ $template->id }}">
                                                <i class="fas fa-copy text-warning"></i> Duplicate
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item toggle-status" href="#" 
                                               data-id="{{ $template->id }}" 
                                               data-status="{{ !$template->is_active }}">
                                                <i class="fas fa-{{ $template->is_active ? 'ban' : 'check' }} text-{{ $template->is_active ? 'danger' : 'success' }}"></i>
                                                {{ $template->is_active ? 'Deactivate' : 'Activate' }}
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item delete-template" href="#" data-id="{{ $template->id }}">
                                                <i class="fas fa-trash text-danger"></i> Delete
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @if($template->description)
                            <p class="template-description">{{ Str::limit($template->description, 100) }}</p>
                            @else
                            <p class="template-description text-muted">No description provided</p>
                            @endif

                            <div class="template-stats mt-3">
                                <div class="stat-item">
                                    <i class="fas fa-tags text-primary"></i>
                                    <span>{{ $template->placeholder_count }} Placeholders</span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-signature text-warning"></i>
                                    <span>{{ $template->signature_count }} Signatures</span>
                                </div>
                            </div>

                            <div class="template-info mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-file"></i> {{ $template->original_filename }}
                                </small>
                                <small class="text-muted d-block mt-1">
                                    <i class="fas fa-calendar"></i> {{ $template->created_at->format('M d, Y') }}
                                </small>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('lease-templates.edit', $template->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <button class="btn btn-sm btn-outline-success use-template" data-id="{{ $template->id }}">
                                    <i class="fas fa-file-contract"></i> Use Template
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <h3 class="empty-title">No Templates Yet</h3>
                                <p class="empty-text">Get started by uploading your first lease template</p>
                                <a href="{{ route('lease-templates.create') }}" class="btn btn-primary btn-lg mt-3">
                                    <i class="fas fa-plus"></i> Upload Your First Template
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye"></i> Template Preview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="previewContent" class="preview-content"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Form -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@endsection

@push('styles')
<style>
    /* Stat Cards */
    .stat-card {
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border-radius: 12px;
        overflow: hidden;
    }

    .stat-card .card-body {
        display: flex;
        align-items: center;
        gap: 20px;
        padding: 25px;
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
    }

    .stat-icon.bg-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .stat-icon.bg-success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .stat-icon.bg-warning {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .stat-icon.bg-info {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .stat-content {
        flex: 1;
    }

    .stat-number {
        font-size: 28px;
        font-weight: 700;
        color: #2c3e50;
        line-height: 1;
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 13px;
        color: #6c757d;
        font-weight: 500;
        text-transform: uppercase;
    }

    /* Template Cards */
    .template-card {
        border: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border-radius: 12px;
        transition: all 0.3s ease;
        margin-bottom: 25px;
    }

    .template-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.12);
    }

    .template-card .card-header {
        background: white;
        border-bottom: 2px solid #f1f3f5;
        padding: 20px;
    }

    .template-title {
        font-size: 16px;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
    }

    .template-meta {
        display: flex;
        gap: 8px;
        margin-top: 8px;
    }

    .template-meta .badge {
        font-size: 11px;
        padding: 4px 8px;
        font-weight: 500;
    }

    .template-description {
        font-size: 14px;
        color: #6c757d;
        margin-bottom: 0;
        line-height: 1.6;
    }

    .template-stats {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 15px 0;
        border-top: 1px solid #f1f3f5;
    }

    .template-stats .stat-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #6c757d;
    }

    .template-stats .stat-item i {
        font-size: 14px;
    }

    .template-info {
        padding-top: 15px;
        border-top: 1px solid #f1f3f5;
    }

    .template-info small {
        display: block;
        font-size: 12px;
    }

    .template-info i {
        width: 16px;
    }

    .card-footer {
        background: #f9fafb;
        border-top: 1px solid #f1f3f5;
        padding: 15px 20px;
    }

    /* Empty State */
    .empty-state {
        padding: 40px 20px;
    }

    .empty-icon {
        width: 100px;
        height: 100px;
        margin: 0 auto 30px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .empty-icon i {
        font-size: 48px;
        color: #667eea;
    }

    .empty-title {
        font-size: 24px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 10px;
    }

    .empty-text {
        font-size: 16px;
        color: #6c757d;
        margin-bottom: 0;
    }

    /* Preview Content */
    .preview-content {
        background: white;
        padding: 60px 80px;
        font-family: 'Times New Roman', serif;
        font-size: 14px;
        line-height: 1.6;
    }

    /* Badges */
    .badge-danger {
        background: #fee2e2;
        color: #dc2626;
    }

    .badge-primary {
        background: #dbeafe;
        color: #2563eb;
    }

    .badge-success {
        background: #d1fae5;
        color: #059669;
    }

    .badge-secondary {
        background: #f3f4f6;
        color: #6b7280;
    }

    .badge-warning {
        background: #fef3c7;
        color: #f59e0b;
    }

    /* Dropdown */
    .dropdown-menu {
        border: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        border-radius: 8px;
    }

    .dropdown-item {
        padding: 10px 16px;
        font-size: 14px;
    }

    .dropdown-item i {
        width: 18px;
        margin-right: 8px;
    }

    .dropdown-item:hover {
        background: #f9fafb;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Preview template
    $('.preview-template').on('click', function(e) {
        e.preventDefault();
        const templateId = $(this).data('id');

        $.ajax({
            url: `/backend/lease-templates/${templateId}/preview`,
            method: 'GET',
            success: function(response) {
                $('#previewContent').html(response.content);
                $('#previewModal').modal('show');
            },
            error: function() {
                alert('Error loading preview');
            }
        });
    });

    // Toggle status
    $('.toggle-status').on('click', function(e) {
        e.preventDefault();
        const templateId = $(this).data('id');
        const newStatus = $(this).data('status');
        const statusText = newStatus ? 'activate' : 'deactivate';

        if (confirm(`Are you sure you want to ${statusText} this template?`)) {
            $.ajax({
                url: `/backend/lease-templates/${templateId}/toggle-status`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    is_active: newStatus
                },
                success: function() {
                    location.reload();
                },
                error: function() {
                    alert('Error updating template status');
                }
            });
        }
    });

    // Duplicate template
    $('.duplicate-template').on('click', function(e) {
        e.preventDefault();
        const templateId = $(this).data('id');

        if (confirm('Create a copy of this template?')) {
            window.location.href = `/backend/lease-templates/${templateId}/duplicate`;
        }
    });

    // Delete template
    $('.delete-template').on('click', function(e) {
        e.preventDefault();
        const templateId = $(this).data('id');

        if (confirm('Are you sure you want to delete this template? This action cannot be undone.')) {
            const form = $('#deleteForm');
            form.attr('action', `/backend/lease-templates/${templateId}`);
            form.submit();
        }
    });

    // Use template
    $('.use-template').on('click', function() {
        const templateId = $(this).data('id');
        // Redirect to lease creation with template
        window.location.href = `/backend/leases/create?template_id=${templateId}`;
    });
});
</script>
@endpush
