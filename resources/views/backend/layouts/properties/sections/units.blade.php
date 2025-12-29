{{-- Units Section --}}
<div class="row">
    <div class="col-lg-12">
        <div class="card box-shadow-0">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h4 class="card-title">Units</h4>
                <button class="btn btn-primary btn-sm" id="addUnitBtn">
                    <i class="fe fe-plus me-1"></i> Add Unit
                </button>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap mb-0" id="unitsTable" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Property</th>
                                <th>Status</th>
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

<!-- Add/Edit Unit Modal -->
<div class="modal fade" id="unitModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="unitForm">
                @csrf
                <input type="hidden" name="id" id="unitId">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="unitModalLabel">Add Unit</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="unit_name" class="form-label">Unit Name</label>
                        <input type="text" class="form-control" name="name" id="unit_name" placeholder="Enter unit name">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="property_id" class="form-label">Property</label>
                        <select class="form-control" name="property_id" id="property_id">
                            <option value="">Select Property</option>
                            @foreach(App\Models\Property::where('is_active', true)->get() as $property)
                                <option value="{{ $property->id }}">{{ $property->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none me-2" id="unitSpinner"></span>
                        <span id="submitBtnText">Save</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function() {
        window.initUnitsSection = function() {
            console.log('Units section initialized');

            let unitModal = null;
            let isEditMode = false;
            let editingId = null;

            // Initialize Modal
            const modalElement = document.getElementById('unitModal');
            if (modalElement && typeof bootstrap !== 'undefined') {
                unitModal = new bootstrap.Modal(modalElement);
                modalElement.addEventListener('hidden.bs.modal', resetForm);
            }

            // Initialize DataTable
            if (!$.fn.DataTable) {
                console.error('DataTables not available');
                return;
            }

            const table = $('#unitsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('units.list') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false },
                    { data: 'name', name: 'name' },
                    { data: 'property', name: 'property', orderable: false },
                    { data: 'status', name: 'is_active', orderable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[0, 'asc']],
                pageLength: 10,
                responsive: true
            });

            // Add Button
            document.getElementById('addUnitBtn')?.addEventListener('click', () => {
                isEditMode = false;
                editingId = null;
                document.getElementById('unitModalLabel').textContent = 'Add Unit';
                document.getElementById('submitBtnText').textContent = 'Save';
                if (unitModal) unitModal.show();
            });

            // Form Submit
            const form = document.getElementById('unitForm');
            if (form) {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const submitBtn = this.querySelector('button[type="submit"]');
                    const spinner = document.getElementById('unitSpinner');
                    const btnText = document.getElementById('submitBtnText');

                    submitBtn.disabled = true;
                    spinner.classList.remove('d-none');
                    btnText.textContent = isEditMode ? 'Updating...' : 'Saving...';

                    clearFormErrors(this);

                    try {
                        const formData = new FormData(this);
                        const url = isEditMode 
                            ? "{{ route('units.update', '') }}/" + editingId
                            : "{{ route('units.store') }}";

                        const response = await axios.post(url, formData);

                        if (response.data.success) {
                            window.showToast('success', response.data.message || 'Saved successfully!');
                            table.draw();
                            if (unitModal) unitModal.hide();
                            resetForm();
                        } else {
                            window.showToast('error', response.data.message || 'Failed to save!');
                        }
                    } catch (error) {
                        if (error.response?.status === 422 && error.response?.data?.errors) {
                            const errors = error.response.data.errors;
                            Object.keys(errors).forEach(field => {
                                const input = this.querySelector(`[name="${field}"]`);
                                if (input) {
                                    input.classList.add('is-invalid');
                                    const feedback = input.parentElement.querySelector('.invalid-feedback');
                                    if (feedback) {
                                        feedback.textContent = errors[field][0];
                                    }
                                }
                            });
                            window.showToast('error', Object.values(errors).flat()[0]);
                        } else {
                            window.showToast('error', error.response?.data?.message || 'Something went wrong!');
                        }
                    } finally {
                        submitBtn.disabled = false;
                        spinner.classList.add('d-none');
                        btnText.textContent = isEditMode ? 'Update' : 'Save';
                    }

                    return false;
                });
            }

            // Helper Functions
            function resetForm() {
                const form = document.getElementById('unitForm');
                if (form) {
                    form.reset();
                    document.getElementById('unitId').value = '';
                    clearFormErrors(form);
                    isEditMode = false;
                    editingId = null;
                }
            }

            function clearFormErrors(form) {
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => {
                    el.textContent = '';
                });
            }

            // Edit Unit
            window.editUnit = function(id) {
                $.ajax({
                    url: `{{ route('units.edit', '') }}/${id}`,
                    type: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            isEditMode = true;
                            editingId = id;
                            document.getElementById('unitId').value = response.data.id;
                            document.getElementById('unit_name').value = response.data.name;
                            document.getElementById('property_id').value = response.data.property_id;
                            document.getElementById('unitModalLabel').textContent = 'Edit Unit';
                            document.getElementById('submitBtnText').textContent = 'Update';
                            if (unitModal) unitModal.show();
                        }
                    },
                    error: function(xhr) {
                        window.showToast('error', xhr.responseJSON?.message || 'Error loading unit');
                    }
                });
            };

            // Delete Confirm
            window.showDeleteConfirm = function(id) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'If you delete this, it will be gone forever.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!',
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.deleteUnit(id);
                    }
                });
            };

            // Delete Unit
            window.deleteUnit = function(id) {
                $.ajax({
                    type: "DELETE",
                    url: `{{ route('units.delete', '') }}/${id}`,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(resp) {
                        window.showToast('success', resp.message || 'Deleted successfully!');
                        table.draw();
                    },
                    error: function(error) {
                        window.showToast('error', error.responseJSON?.message || 'Error deleting unit');
                    }
                });
            };

            // Toggle Unit Status
            window.toggleUnitStatus = function(id) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Status will be updated.',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, update it!',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ route('units.toggle.status', '') }}/${id}`,
                            type: 'GET',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    window.showToast('success', response.message);
                                    table.draw();
                                }
                            },
                            error: function(xhr) {
                                window.showToast('error', xhr.responseJSON?.message || 'Error toggling status');
                            }
                        });
                    }
                });
            };

            console.log('Units DataTable initialized');
        };

        // Auto-initialize
        if (typeof window.initUnitsSection === 'function') {
            window.initUnitsSection();
        }
    })();
</script>
