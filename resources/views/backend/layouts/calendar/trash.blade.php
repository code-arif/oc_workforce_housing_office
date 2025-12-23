@extends('backend.app')

@section('title', 'Trash')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                {{-- Page Header --}}
                <div class="page-header">
                    <div class="card shadow-sm mb-2 border-0">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <h1 class="page-title mb-0">
                                    <i class="fas fa-trash me-2"></i>Trash
                                </h1>
                                <p class="text-muted mb-0">Deleted works are kept here for 30 days</p>
                            </div>
                            <div>
                                <a href="{{ route('calendar.index') }}" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Calendar
                                </a>
                                <button class="btn btn-sm btn-danger" id="emptyTrashBtn">
                                    <i class="fas fa-trash-alt me-2"></i>Empty Trash
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Trash Items --}}
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="trashTable">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Calendar</th>
                                        <th>Date & Time</th>
                                        <th>Deleted At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="trashTableBody">
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            <i class="fas fa-spinner fa-spin me-2"></i>Loading trash...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <style>
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
            cursor: pointer;
        }

        .action-btn {
            padding: 5px 12px;
            font-size: 12px;
            border-radius: 4px;
            margin: 0 2px;
        }

        .calendar-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            color: white;
        }

        .empty-trash {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-trash i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .toast-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            min-width: 300px;
            background: white;
            padding: 16px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 9999;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .toast-notification.success {
            border-left: 4px solid #34a853;
        }

        .toast-notification.error {
            border-left: 4px solid #ea4335;
        }

        .toast-notification.info {
            border-left: 4px solid #4285f4;
        }
    </style>

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadTrash();

            // Load trash items
            async function loadTrash() {
                try {
                    const response = await fetch('{{ route('trash.index') }}');
                    const trashedWorks = await response.json();

                    const tbody = document.getElementById('trashTableBody');

                    if (trashedWorks.length === 0) {
                        tbody.innerHTML = `
                    <tr>
                        <td colspan="5">
                            <div class="empty-trash">
                                <i class="fas fa-trash"></i>
                                <h4>Trash is empty</h4>
                                <p>Deleted items will appear here</p>
                            </div>
                        </td>
                    </tr>
                `;
                        return;
                    }

                    tbody.innerHTML = trashedWorks.map(work => {
                        const startDate = new Date(work.start_datetime);
                        const endDate = new Date(work.end_datetime);
                        const deletedAt = new Date(work.deleted_at);

                        let dateTimeDisplay = '';
                        if (work.is_all_day) {
                            dateTimeDisplay = startDate.toLocaleDateString('en-US', {
                                weekday: 'short',
                                month: 'short',
                                day: 'numeric',
                                year: 'numeric'
                            }) + ' (All Day)';
                        } else {
                            dateTimeDisplay = startDate.toLocaleDateString('en-US', {
                                weekday: 'short',
                                month: 'short',
                                day: 'numeric',
                                year: 'numeric'
                            }) + '<br>' + startDate.toLocaleTimeString('en-US', {
                                hour: '2-digit',
                                minute: '2-digit'
                            }) + ' - ' + endDate.toLocaleTimeString('en-US', {
                                hour: '2-digit',
                                minute: '2-digit'
                            });
                        }

                        const calendarColor = work.calendar ? work.calendar.color : '#3b82f6';
                        const calendarName = work.calendar ? work.calendar.name : 'Default';

                        return `
                    <tr>
                        <td>
                            <strong>${work.title}</strong>
                            ${work.description ? '<br><small class="text-muted">' + work.description + '</small>' : ''}
                        </td>
                        <td>
                            <span class="calendar-badge" style="background-color: ${calendarColor};">
                                ${calendarName}
                            </span>
                        </td>
                        <td>${dateTimeDisplay}</td>
                        <td>
                            <small class="text-muted">
                                ${deletedAt.toLocaleDateString('en-US', {
                                    month: 'short',
                                    day: 'numeric',
                                    year: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                })}
                            </small>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-success action-btn" onclick="restoreWork(${work.id})">
                                <i class="fas fa-undo me-1"></i>Restore
                            </button>
                            <button class="btn btn-sm btn-danger action-btn" onclick="permanentDelete(${work.id})">
                                <i class="fas fa-trash me-1"></i>Delete Forever
                            </button>
                        </td>
                    </tr>
                `;
                    }).join('');
                } catch (error) {
                    console.error('Error loading trash:', error);
                    showToast('Failed to load trash', 'error');
                }
            }

            // Restore work
            window.restoreWork = async function(workId) {
                const result = await Swal.fire({
                    title: 'Restore Work?',
                    text: 'This work will be moved back to your calendar',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, restore it!'
                });

                if (!result.isConfirmed) return;

                try {
                    const response = await fetch(`/trash/${workId}/restore`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        showToast(data.message, 'success');
                        loadTrash();
                    } else {
                        showToast(data.message, 'error');
                    }
                } catch (error) {
                    showToast('Failed to restore work', 'error');
                }
            };

            // Permanent delete
            window.permanentDelete = async function(workId) {
                const result = await Swal.fire({
                    title: 'Delete Permanently?',
                    html: '<strong style="color: #dc3545;">This action cannot be undone!</strong><br>The work will be deleted from Google Calendar as well.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete forever!',
                    cancelButtonText: 'Cancel'
                });

                if (!result.isConfirmed) return;

                try {
                    const response = await fetch(`/trash/${workId}/force-delete`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        showToast(data.message, 'success');
                        loadTrash();
                    } else {
                        showToast(data.message, 'error');
                    }
                } catch (error) {
                    showToast('Failed to delete work', 'error');
                }
            };

            // Empty trash
            document.getElementById('emptyTrashBtn').addEventListener('click', async function() {
                const result = await Swal.fire({
                    title: 'Empty Trash?',
                    html: '<strong style="color: #dc3545;">This will permanently delete ALL items in trash!</strong><br>This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, empty trash!',
                    cancelButtonText: 'Cancel'
                });

                if (!result.isConfirmed) return;

                try {
                    const response = await fetch('{{ route('trash.empty') }}', {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        showToast(data.message, 'success');
                        loadTrash();
                    } else {
                        showToast(data.message, 'error');
                    }
                } catch (error) {
                    showToast('Failed to empty trash', 'error');
                }
            });

            function showToast(message, type = 'info') {
                const toast = document.createElement('div');
                toast.className = `toast-notification ${type}`;
                toast.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        `;
                document.body.appendChild(toast);
                setTimeout(() => {
                    toast.style.opacity = '0';
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            }

            @if (session('success'))
                showToast('{{ session('success') }}', 'success');
            @endif

            @if (session('error'))
                showToast('{{ session('error') }}', 'error');
            @endif
        });
    </script>
@endpush
