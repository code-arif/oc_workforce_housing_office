@extends('backend.app')

@section('title', 'Activity Logs')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app" style="margin-bottom: 50px">
            <div class="main-container container-fluid">

                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Activity Logs</h1>
                        <p class="text-muted mb-0">Monitor and audit all system activities and user interactions</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Activity Logs</li>
                        </ol>
                    </div>
                </div>
                <!-- PAGE-HEADER END -->

                <!-- FILTERS -->
                <div class="row">
                    <div class="col-12">
                        <div class="filter-card">
                            <div class="row align-items-end g-3">
                                <div class="col-md-2">
                                    <label class="form-label">User</label>
                                    <select class="form-select select3" id="userFilter" data-placeholder="All Users">
                                        <option value="">All Users</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Module</label>
                                    <select class="form-select select3" id="moduleFilter" data-placeholder="All Modules">
                                        <option value="">All Modules</option>
                                        @foreach ($modules as $module)
                                            <option value="{{ $module }}">{{ $module }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Action</label>
                                    <select class="form-select select3" id="actionFilter" data-placeholder="All Actions">
                                        <option value="">All Actions</option>
                                        @foreach ($actions as $action)
                                            <option value="{{ $action }}">{{ $action }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Date From</label>
                                    <input type="date" class="form-control" id="dateFrom">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Date To</label>
                                    <input type="date" class="form-control" id="dateTo">
                                </div>

                                <div class="col-md-2">
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-primary flex-fill" id="applyFilter">
                                            <i class="fe fe-filter me-1"></i> Filter
                                        </button>
                                        <button type="button" class="btn btn-secondary" id="resetFilter"
                                            title="Reset Filters">
                                            <i class="fe fe-refresh-cw"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ACTIVITY LOG TABLE -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                                <h3 class="card-title mb-0">System Activity History</h3>
                                <div class="card-options d-flex align-items-center gap-2 flex-wrap">

                                    <div class="btn-group d-none" id="bulkActionGroup">
                                        <button type="button" id="bulkDeleteBtn"
                                            class="btn btn-sm btn-danger d-inline-flex align-items-center">
                                            <i class="fe fe-trash-2 me-1"></i>
                                            <span>
                                                Bulk Delete (<span id="selectedCount">0</span>)
                                            </span>
                                        </button>
                                    </div>

                                    <div class="btn-group">
                                        <button type="button"
                                            class="btn btn-sm btn-outline-primary dropdown-toggle d-inline-flex align-items-center"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fe fe-download me-1"></i>
                                            <span>Export</span>
                                        </button>

                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="#" onclick="exportLogs('xlsx')">
                                                    Excel (.xlsx)
                                                </a>
                                            </li>

                                            <li>
                                                <a class="dropdown-item" href="#" onclick="exportLogs('csv')">
                                                    CSV (.csv)
                                                </a>
                                            </li>
                                        </ul>
                                    </div>

                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap border-bottom w-100" id="activityTable">
                                        <thead>
                                            <tr>
                                                <th class="text-center" style="width: 50px;">
                                                    <input type="checkbox" class="form-check-input" id="selectAllLogs">
                                                </th>
                                                <th>ID</th>
                                                <th>Timestamp</th>
                                                <th>User</th>
                                                <th>Module</th>
                                                <th>Action</th>
                                                <th>IP Address</th>
                                                <th>Details</th>
                                                <th>Redirect</th>
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

    <!-- LOG DETAILS MODAL -->
    <div class="modal fade" id="logDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Activity Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="fw-bold">Module:</label>
                            <p id="detailModule"></p>
                        </div>
                        <div class="col-md-3">
                            <label class="fw-bold">Action:</label>
                            <p id="detailAction"></p>
                        </div>
                        <div class="col-md-3">
                            <label class="fw-bold">Route:</label>
                            <p id="detailRoute" class="text-truncate"></p>
                        </div>
                        <div class="col-md-3 text-end">
                            <div id="detailActionLink"></div>
                        </div>
                    </div>

                    <div class="row" id="dataDetailsContainer">
                        <div class="col-md-6" id="oldDataColumn">
                            <div class="card bg-light shadow-none h-100 mb-0">
                                <div class="card-header bg-warning-transparent">
                                    <h6 class="mb-0 text-warning">Previous Data (Old)</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div id="detailOldView" class="table-responsive"
                                        style="max-height: 500px; overflow-y: auto;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6" id="newDataColumn">
                            <div class="card bg-light shadow-none h-100 mb-0">
                                <div class="card-header bg-success-transparent">
                                    <h6 class="mb-0 text-success">Updated Data (New)</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div id="detailNewView" class="table-responsive"
                                        style="max-height: 500px; overflow-y: auto;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="singleDataView" class="d-none">
                        <div class="card bg-light shadow-none mb-0">
                            <div class="card-header bg-primary-transparent">
                                <h6 class="mb-0 text-primary" id="singleDataTitle">Activity Data</h6>
                            </div>
                            <div class="card-body p-0">
                                <div id="detailSingleView" class="table-responsive"
                                    style="max-height: 500px; overflow-y: auto;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let activityTable;

        $(document).ready(function() {
            activityTable = $('#activityTable').DataTable({
                processing: true,
                serverSide: true,
                order: [
                    [0, 'desc']
                ],
                ajax: {
                    url: "{{ route('activity-logs.data') }}",
                    data: function(d) {
                        d.user_id = $('#userFilter').val();
                        d.module = $('#moduleFilter').val();
                        d.action = $('#actionFilter').val();
                        d.date_from = $('#dateFrom').val();
                        d.date_to = $('#dateTo').val();
                    }
                },
                columns: [{
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'user_name',
                        name: 'user_name'
                    },
                    {
                        data: 'module',
                        name: 'module'
                    },
                    {
                        data: 'action',
                        name: 'action'
                    },
                    {
                        data: 'ip_address',
                        name: 'ip_address'
                    },
                    {
                        data: 'details',
                        name: 'details',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action_link',
                        name: 'action_link',
                        orderable: false,
                        searchable: false
                    }
                ],
                drawCallback: function() {
                    updateBulkActionUI();
                }
            });

            $('#applyFilter').click(function() {
                activityTable.ajax.reload();
            });

            $('#resetFilter').click(function() {
                $('.form-select').val('').trigger('change');
                $('.form-control').val('');
                activityTable.ajax.reload();
            });

            // Select All functionality
            $('#selectAllLogs').click(function() {
                $('.log-checkbox').prop('checked', $(this).prop('checked'));
                updateBulkActionUI();
            });

            $(document).on('change', '.log-checkbox', function() {
                updateBulkActionUI();
            });

            function updateBulkActionUI() {
                const count = $('.log-checkbox:checked').length;
                $('#selectedCount').text(count);
                if (count > 0) {
                    $('#bulkActionGroup').removeClass('d-none');
                } else {
                    $('#bulkActionGroup').addClass('d-none');
                    $('#selectAllLogs').prop('checked', false);
                }
            }

            $('#bulkDeleteBtn').click(function() {
                const ids = $('.log-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();
                if (ids.length === 0) return;

                Swal.fire({
                    title: 'Are you sure?',
                    text: `You are about to delete ${ids.length} selected activity logs!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete them!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('activity-logs.mass-delete') }}",
                            type: 'POST',
                            data: {
                                ids: ids,
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.success) {
                                    toastr.success(response.message);
                                    activityTable.ajax.reload();
                                } else {
                                    toastr.error(response.message);
                                }
                            }
                        });
                    }
                });
            });

            // Initialize Select2 (select3)
            if ($('.select3').length) {
                $('.select3').select2({
                    placeholder: "Select an option",
                    allowClear: true,
                    width: '100%'
                });
            }
        });

        function deleteLog(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('activity-logs.destroy', ':id') }}".replace(':id',
                            id),
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message);
                                activityTable.ajax.reload();
                            } else {
                                toastr.error(response.message);
                            }
                        }
                    });
                }
            });
        }

        function viewLogDetails(id) {
            NProgress.start();
            $.ajax({
                url: "{{ route('activity-logs.show', ':id') }}".replace(':id', id),
                type: 'GET',
                success: function(response) {
                    NProgress.done();
                    if (response.success) {
                        const log = response.data.log;
                        $('#detailModule').text(log.module);
                        $('#detailAction').text(log.action);
                        $('#detailRoute').text(log.route);

                        if (log.action === 'Updated') {
                            $('#dataDetailsContainer').removeClass('d-none');
                            $('#singleDataView').addClass('d-none');
                            $('#detailOldView').html(renderDataTable(log.old_data));
                            $('#detailNewView').html(renderDataTable(log.new_data));
                        } else {
                            $('#dataDetailsContainer').addClass('d-none');
                            $('#singleDataView').removeClass('d-none');
                            $('#singleDataTitle').text(log.action + ' Data');
                            const dataToRender = log.new_data || log.old_data;
                            $('#detailSingleView').html(renderDataTable(dataToRender));
                        }

                        if (response.data.redirect_url) {
                            $('#detailActionLink').html(
                                `<a href="${response.data.redirect_url}" class="btn btn-success"><i class="fe fe-link"></i> Go to Entity</a>`
                                );
                        } else {
                            $('#detailActionLink').html('');
                        }

                        $('#logDetailsModal').modal('show');
                    }
                },
                error: function() {
                    NProgress.done();
                    toastr.error('Failed to load log details');
                }
            });
        }

        function renderDataTable(data) {
            if (!data || Object.keys(data).length === 0) {
                return '<div class="p-4 text-center text-muted">No data available</div>';
            }

            let html = '<table class="table table-sm table-striped mb-0">';
            html += '<thead class="bg-light"><tr><th class="ps-3">Field</th><th>Value</th></tr></thead>';
            html += '<tbody>';

            for (const key in data) {
                let value = data[key];
                if (value === null) {
                    value = '<span class="text-muted">null</span>';
                } else if (typeof value === 'object') {
                    value =
                        `<pre class="mb-0 bg-transparent p-0" style="font-size: 11px;">${JSON.stringify(value, null, 2)}</pre>`;
                } else if (typeof value === 'boolean') {
                    value = value ? '<span class="badge bg-success">True</span>' :
                        '<span class="badge bg-danger">False</span>';
                }

                html += `<tr>
                    <td class="ps-3 fw-semibold text-muted" style="width: 35%;">${key.replace(/_/g, ' ').toUpperCase()}</td>
                    <td class="text-wrap">${value}</td>
                </tr>`;
            }

            html += '</tbody></table>';
            return html;
        }

        function exportLogs(format) {
            const params = new URLSearchParams({
                format: format,
                user_id: $('#userFilter').val(),
                module: $('#moduleFilter').val(),
                action: $('#actionFilter').val(),
                date_from: $('#dateFrom').val(),
                date_to: $('#dateTo').val()
            });
            window.location.href = "{{ route('activity-logs.export') }}?" + params.toString();
        }
    </script>
@endpush

@push('styles')
    <style>
        pre {
            background: #f8f9fa;
            border-radius: 4px;
            padding: 10px;
            font-size: 12px;
            color: #333;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .bg-warning-transparent {
            background-color: rgba(255, 193, 7, 0.1) !important;
        }

        .bg-success-transparent {
            background-color: rgba(40, 167, 69, 0.1) !important;
        }

        .select2-container {
            width: 100% !important;
        }

        .filter-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #e9ecef;
        }

        .filter-card .form-label {
            font-weight: 600;
            font-size: 13px;
            color: #495057;
            margin-bottom: 8px;
        }

        #activityTable th:first-child,
        #activityTable td:first-child {
            width: 40px !important;
            min-width: 40px !important;
            max-width: 40px !important;
            text-align: center !important;
            vertical-align: middle !important;
            padding: 10px 10px !important;
        }

        #activityTable th:first-child::before,
        #activityTable th:first-child::after {
            display: none !important;
            content: none !important;
        }

        .log-checkbox,
        #selectAllLogs {
            cursor: pointer;
            width: 16px;
            height: 16px;
            margin: 0 auto !important;
        }
    </style>
@endpush
