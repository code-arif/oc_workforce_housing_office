{{-- Trash Section - Deleted Properties --}}
<div class="row">
    <div class="col-lg-12">
        <div class="card box-shadow-0">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h4 class="card-title">
                    <i class="fe fe-trash me-2"></i> Deleted Properties
                </h4>
                <small class="text-muted">Properties can be restored or permanently deleted</small>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap mb-0" id="trashTable" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Type</th>
                                <th>Deleted On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        window.initTrashSection = function() {
            if (!$.fn.DataTable) {
                console.error('DataTables not available');
                return;
            }

            // Initialize DataTable for Trash
            const trashTable = $('#trashTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('property.trash.data') }}",
                columns: [
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'address',
                        name: 'address'
                    },
                    {
                        data: 'type',
                        name: 'type',
                        orderable: false
                    },
                    {
                        data: 'deleted_at',
                        name: 'deleted_at'
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [4, 'desc']
                ],
                pageLength: 10,
                responsive: true,
                language: {
                    emptyTable: "No deleted properties found",
                    infoEmpty: "No entries to show"
                },
                drawCallback: function() {
                    // Reinitialize tooltips after table draw
                    if (typeof $ !== 'undefined' && $.fn.tooltip) {
                        $('[data-bs-toggle="tooltip"]').tooltip();
                    }
                }
            });

            // Restore Property
            window.restoreProperty = function(id) {
                Swal.fire({
                    title: 'Restore Property?',
                    text: 'This property will be restored and visible again.',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, restore it!',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/admin/property/' + id + '/restore',
                            type: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (typeof window.showToast === 'function') {
                                    window.showToast('success', response.message || 'Property restored successfully!');
                                } else {
                                    iziToast.success({message: response.message || 'Property restored successfully!'});
                                }
                                trashTable.draw();
                            },
                            error: function(xhr) {
                                const msg = xhr.responseJSON?.message || 'Error restoring property';
                                if (typeof window.showToast === 'function') {
                                    window.showToast('error', msg);
                                } else {
                                    iziToast.error({message: msg});
                                }
                            }
                        });
                    }
                });
            };

            // Permanently Delete Property
            window.permanentlyDeleteProperty = function(id) {
                Swal.fire({
                    title: 'Permanently Delete?',
                    text: 'This action cannot be undone. The property will be completely removed.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete permanently!',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/admin/property/' + id + '/force-delete',
                            type: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (typeof window.showToast === 'function') {
                                    window.showToast('success', response.message || 'Property permanently deleted!');
                                } else {
                                    iziToast.success({message: response.message || 'Property permanently deleted!'});
                                }
                                trashTable.draw();
                            },
                            error: function(xhr) {
                                const msg = xhr.responseJSON?.message || 'Error deleting property';
                                if (typeof window.showToast === 'function') {
                                    window.showToast('error', msg);
                                } else {
                                    iziToast.error({message: msg});
                                }
                            }
                        });
                    }
                });
            };

            console.log('Trash section initialized');
        };

        // Auto-initialize
        if (typeof window.initTrashSection === 'function') {
            window.initTrashSection();
        }
    })();
</script>
