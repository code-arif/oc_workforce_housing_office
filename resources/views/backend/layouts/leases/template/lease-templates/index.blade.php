@extends('backend.app')
@section('title', 'Lease Templates')
@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- PAGE HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Lease Templates</h1>
                        <p class="text-muted mb-0">Manage pre-formatted lease document templates</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        @can('lease.template.create')
                            <a href="{{ route('lease-templates.create') }}"
                                class="btn btn-primary d-inline-flex align-items-center gap-2">
                                <i class="fas fa-plus"></i> New Template
                            </a>
                        @endcan
                    </div>
                </div>
                <!-- PAGE HEADER END -->

                <!-- Stats Row -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 col-sm-6">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="stat-icon bg-primary">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div class="stat-content">
                                    <h3 class="stat-number">{{ $templates->count() }}</h3>
                                    <div class="stat-label">Total Templates</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 col-sm-6">
                        <div class="card stat-card">
                            <div class="card-body">
                                <div class="stat-icon bg-success">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="stat-content">
                                    <h3 class="stat-number">{{ $templates->where('is_active', true)->count() }}</h3>
                                    <div class="stat-label">Active Templates</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Templates Table Card -->
                <div class="card">
                    <div class="card-header border-bottom d-flex align-items-center justify-content-between">
                        <h3 class="card-title mb-0">All Templates</h3>
                        {{-- Search is handled by DataTable --}}
                    </div>
                    <div class="card-body p-0">
                        @if ($templates->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="templatesTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4" style="width: 40%">Template</th>
                                            <th style="width: 10%">Type</th>
                                            <th style="width: 10%">Status</th>
                                            <th style="width: 12%">Placeholders</th>
                                            <th style="width: 12%">Signatures</th>
                                            <th style="width: 10%">Created</th>
                                            <th class="text-center" style="width: 6%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($templates as $template)
                                            <tr>
                                                <!-- Name + description -->
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center gap-3">
                                                        <div class="template-icon-wrap">
                                                            <i
                                                                class="fas fa-file-{{ $template->file_type === 'pdf' ? 'pdf' : 'word' }} fa-lg
                                                                {{ $template->file_type === 'pdf' ? 'text-danger' : 'text-primary' }}"></i>
                                                        </div>
                                                        <div class="overflow-hidden">
                                                            <div class="fw-semibold text-truncate" style="max-width:320px"
                                                                title="{{ $template->name }}">
                                                                {{ $template->name }}
                                                            </div>
                                                            @if ($template->description)
                                                                <small class="text-muted text-truncate d-block"
                                                                    style="max-width:320px">
                                                                    {{ Str::limit($template->description, 80) }}
                                                                </small>
                                                            @else
                                                                <small class="text-muted fst-italic">No description</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>

                                                <!-- File type -->
                                                <td>
                                                    <span
                                                        class="badge p-3 badge-type {{ $template->file_type === 'pdf' ? 'badge-pdf' : 'badge-docx' }}">
                                                        {{ strtoupper($template->file_type) }}
                                                    </span>
                                                </td>

                                                <!-- Status -->
                                                <td>
                                                    <span
                                                        class="badge p-3 {{ $template->is_active ? 'badge-active' : 'badge-inactive' }}">
                                                        <i class="fas fa-circle me-1"
                                                            style="font-size:7px;vertical-align:middle"></i>
                                                        {{ $template->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>

                                                <!-- Placeholders -->
                                                <td>
                                                    <span class="d-inline-flex align-items-center gap-1 text-muted">
                                                        <i class="fas fa-tags text-primary" style="font-size:12px"></i>
                                                        {{ $template->placeholder_count }}
                                                    </span>
                                                </td>

                                                <!-- Signatures -->
                                                <td>
                                                    <span class="d-inline-flex align-items-center gap-1 text-muted">
                                                        <i class="fas fa-signature text-warning" style="font-size:12px"></i>
                                                        {{ $template->signature_count }}
                                                    </span>
                                                </td>

                                                <!-- Created date -->
                                                <td>
                                                    <small class="text-muted">
                                                        {{ $template->created_at->format('M d, Y') }}
                                                    </small>
                                                </td>

                                                <!-- Actions dropdown -->
                                                <td class="text-center">
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-light action-btn" type="button"
                                                            data-bs-toggle="dropdown" aria-expanded="false" title="Actions">
                                                            <i class="fas fa-ellipsis-v"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">

                                                            @can('lease.template.edit')
                                                                <li>
                                                                    <a class="dropdown-item"
                                                                        href="{{ route('lease-templates.edit', $template->id) }}">
                                                                        <i class="fas fa-edit text-primary"></i> Edit Template
                                                                    </a>
                                                                </li>
                                                            @endcan

                                                            <li>
                                                                <a class="dropdown-item"
                                                                    href="{{ route('lease-templates.preview', $template->id) }}">
                                                                    <i class="fas fa-eye text-info"></i> Preview
                                                                </a>
                                                            </li>

                                                            <li>
                                                                <a class="dropdown-item duplicate-template" href="#"
                                                                    data-id="{{ $template->id }}">
                                                                    <i class="fas fa-copy text-warning"></i> Duplicate
                                                                </a>
                                                            </li>

                                                            <li>
                                                                <hr class="dropdown-divider my-1">
                                                            </li>

                                                            <li>
                                                                <a class="dropdown-item toggle-status" href="#"
                                                                    data-id="{{ $template->id }}"
                                                                    data-status="{{ $template->is_active ? 0 : 1 }}">
                                                                    <i
                                                                        class="fas fa-{{ $template->is_active ? 'ban' : 'check' }}
                                                                        text-{{ $template->is_active ? 'danger' : 'success' }}"></i>
                                                                    {{ $template->is_active ? 'Deactivate' : 'Activate' }}
                                                                </a>
                                                            </li>

                                                            @can('lease.template.delete')
                                                                <li>
                                                                    <hr class="dropdown-divider my-1">
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item delete-template" href="#"
                                                                        data-id="{{ $template->id }}">
                                                                        <i class="fas fa-trash text-danger"></i> Delete
                                                                    </a>
                                                                </li>
                                                            @endcan

                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <!-- Empty state -->
                            <div class="text-center py-5">
                                <div class="empty-icon-wrap mb-3">
                                    <i class="fas fa-file-alt fa-2x text-muted"></i>
                                </div>
                                <h5 class="text-muted mb-1">No Templates Yet</h5>
                                <p class="text-muted small mb-3">Get started by uploading your first lease template</p>
                                @can('lease.template.create')
                                    <a href="{{ route('lease-templates.create') }}"
                                        class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1">
                                        <i class="fas fa-plus"></i> Upload Your First Template
                                    </a>
                                @endcan
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Delete Confirmation Form (kept exactly as before) -->
    <form id="deleteForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

@endsection

@push('styles')
    <style>
        /* ── Stat Cards (unchanged from original) ── */
        .stat-card .card-body {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px 24px;
        }

        .stat-icon {
            width: 54px;
            height: 54px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 18px;
        }

        .stat-icon.bg-primary {
            background: rgba(102, 126, 234, 0.15) !important;
            color: #667eea;
        }

        .stat-icon.bg-success {
            background: rgba(17, 153, 142, 0.15) !important;
            color: #11998e;
        }

        .stat-content {
            flex: 1;
        }

        .stat-number {
            font-size: 24px;
            font-weight: 700;
            color: #2c3e50;
            line-height: 1.1;
            margin-bottom: 2px;
        }

        .stat-label {
            font-size: 12px;
            color: #6c757d;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        /* ── Table ── */
        #templatesTable thead th {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: #6c757d;
            border-bottom: 2px solid #e9ecef;
            padding: 12px 14px;
            white-space: nowrap;
        }

        #templatesTable tbody tr {
            transition: background .15s;
        }

        #templatesTable tbody tr:hover {
            background: #f8f9fa;
        }

        #templatesTable tbody td {
            padding: 12px 14px;
            border-bottom: 1px solid #f1f3f5;
            vertical-align: middle;
        }

        #templatesTable tbody tr:last-child td {
            border-bottom: none;
        }

        /* ── Template icon wrap ── */
        .template-icon-wrap {
            width: 36px;
            height: 36px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-radius: 8px;
        }

        /* ── Badges ── */
        .badge-type {
            font-size: 10px;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 4px;
            letter-spacing: .3px;
        }

        .badge-pdf {
            background: #fee2e2;
            color: #dc2626;
        }

        .badge-docx {
            background: #dbeafe;
            color: #2563eb;
        }

        .badge-active {
            background: #d1fae5;
            color: #059669;
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 20px;
            font-weight: 500;
        }

        .badge-inactive {
            background: #f3f4f6;
            color: #6b7280;
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 20px;
            font-weight: 500;
        }

        /* ── Action button ── */
        .action-btn {
            width: 30px;
            height: 30px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            color: #6c757d;
            border: 1px solid #e9ecef;
            background: #fff;
        }

        .action-btn:hover {
            background: #f8f9fa;
            color: #343a40;
            border-color: #dee2e6;
        }

        /* ── Dropdown ── */
        .dropdown-menu {
            border: none;
            box-shadow: 0 4px 16px rgba(0, 0, 0, .12);
            border-radius: 8px;
            min-width: 170px;
            padding: 4px;
        }

        .dropdown-item {
            padding: 8px 12px;
            font-size: 13px;
            border-radius: 6px;
        }

        .dropdown-item i {
            width: 16px;
            margin-right: 8px;
        }

        .dropdown-item:hover {
            background: #f8f9fa;
        }

        .dropdown-divider {
            margin: 0;
        }

        /* ── DataTable overrides ── */
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 6px 12px;
            font-size: 13px;
            margin-left: 6px;
        }

        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 4px 28px 4px 10px;
            /* top right bottom left */
            font-size: 13px;
            margin: 0 4px;
        }

        div.dataTables_wrapper div.dataTables_length select {
            width: 50% !important;
        }

        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_filter label,
        .dataTables_wrapper .dataTables_length label {
            font-size: 13px;
            color: #6c757d;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 4px 10px;
            font-size: 13px;
            border-radius: 6px !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: #4285f4 !important;
            color: #fff !important;
            border-color: #4285f4 !important;
        }

        div.dataTables_wrapper div.dataTables_filter {
            text-align: right;
        }

        /* top bar spacing */
        .dt-top-bar {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f3f5;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {

            // ── Init DataTable ──────────────────────────────────────────
            const table = $('#templatesTable').DataTable({
                pageLength: 25,
                order: [
                    [5, 'desc']
                ], // sort by Created date desc
                columnDefs: [{
                        orderable: false,
                        targets: [6]
                    } // Actions column not sortable
                ],
                language: {
                    search: '',
                    searchPlaceholder: 'Search templates…',
                    lengthMenu: 'Show _MENU_ entries',
                    info: 'Showing _START_–_END_ of _TOTAL_ templates',
                    infoEmpty: 'No templates found',
                    infoFiltered: '(filtered from _MAX_ total)',
                    paginate: {
                        previous: '<i class="fas fa-chevron-left"></i>',
                        next: '<i class="fas fa-chevron-right"></i>'
                    }
                },
                dom: '<"dt-top-bar"lf>rt<"px-3 py-2 d-flex justify-content-between align-items-center"ip>',
            });

            // ── Toggle Status ───────────────────────────────────────────
            // Using event delegation so it works after DataTable re-renders
            $(document).on('click', '.toggle-status', function(e) {
                e.preventDefault();
                const templateId = $(this).data('id');
                const newStatus = $(this).data('status');
                const statusText = newStatus ? 'activate' : 'deactivate';

                Swal.fire({
                    title: `${statusText.charAt(0).toUpperCase() + statusText.slice(1)} this template?`,
                    text: `The template will be marked as ${newStatus ? 'active' : 'inactive'}.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, proceed!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/admin/lease-templates/${templateId}/toggle-status`,
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                is_active: newStatus
                            },
                            success: function() {
                                // Show toast then reload to reflect change
                                Swal.fire({
                                    title: 'Done!',
                                    text: `Template ${statusText}d successfully.`,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => location.reload());
                            },
                            error: function(xhr) {
                                Swal.fire('Error', xhr.responseJSON?.message ||
                                    'Could not update status.', 'error');
                            }
                        });
                    }
                });
            });

            // ── Duplicate ───────────────────────────────────────────────
            $(document).on('click', '.duplicate-template', function(e) {
                e.preventDefault();
                const templateId = $(this).data('id');

                Swal.fire({
                    title: 'Duplicate this template?',
                    text: 'A copy will be created and opened for editing.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, duplicate!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = `/admin/lease-templates/${templateId}/duplicate`;
                    }
                });
            });

            // ── Delete ──────────────────────────────────────────────────
            $(document).on('click', '.delete-template', function(e) {
                e.preventDefault();
                const templateId = $(this).data('id');

                Swal.fire({
                    title: 'Delete this template?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = $('#deleteForm');
                        form.attr('action', `/admin/lease-templates/${templateId}`);
                        form.submit();
                    }
                });
            });

        });
    </script>
@endpush
