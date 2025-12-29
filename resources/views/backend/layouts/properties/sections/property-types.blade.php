{{-- Property Types Section --}}
<div class="row">
    <div class="col-lg-12">
        <div class="card box-shadow-0">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h4 class="card-title">Property Types</h4>
                <button class="btn btn-primary btn-sm" id="addPropertyTypeBtn">
                    <i class="fe fe-plus me-1"></i> Add Property Type
                </button>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap mb-0" id="propertyTypeTable" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Description</th>
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

<!-- Add/Edit Property Type Modal -->
<div class="modal fade" id="propertyTypeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="propertyTypeForm">
                @csrf
                <input type="hidden" name="id" id="propertyTypeId">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="propertyTypeModalLabel">Add Property Type</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" id="name" placeholder="Enter name">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="description" rows="3" placeholder="Enter description"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none me-2" id="propertyTypeSpinner"></span>
                        <span id="submitBtnText">Save</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function() {
        window.initPropertyTypesSection = function() {
            console.log('Property Types section initialized');

            let propertyTypeModal = null;
            let isEditMode = false;
            let editingId = null;

            // Initialize Modal
            const modalElement = document.getElementById('propertyTypeModal');
            if (modalElement && typeof bootstrap !== 'undefined') {
                propertyTypeModal = new bootstrap.Modal(modalElement);
                modalElement.addEventListener('hidden.bs.modal', resetForm);
            }

            // Initialize DataTable
            if (!$.fn.DataTable) {
                console.error('DataTables not available');
                return;
            }

            const table = $('#propertyTypeTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('property-type.list') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false },
                    { data: 'name', name: 'name' },
                    { data: 'slug', name: 'slug' },
                    { data: 'description', name: 'description' },
                    { data: 'status', name: 'is_active', orderable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[0, 'asc']],
                pageLength: 10,
                responsive: true
            });

            // Add Button
            document.getElementById('addPropertyTypeBtn')?.addEventListener('click', () => {
                isEditMode = false;
                editingId = null;
                document.getElementById('propertyTypeModalLabel').textContent = 'Add Property Type';
                document.getElementById('submitBtnText').textContent = 'Save';
                if (propertyTypeModal) propertyTypeModal.show();
            });

            // Form Submit
            const form = document.getElementById('propertyTypeForm');
            if (form) {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const submitBtn = this.querySelector('button[type="submit"]');
                    const spinner = document.getElementById('propertyTypeSpinner');
                    const btnText = document.getElementById('submitBtnText');

                    submitBtn.disabled = true;
                    spinner.classList.remove('d-none');
                    btnText.textContent = isEditMode ? 'Updating...' : 'Saving...';

                    clearFormErrors(this);

                    try {
                        const formData = new FormData(this);
                        const url = isEditMode 
                            ? "{{ route('property-type.update', '') }}/" + editingId
                            : "{{ route('property-type.store') }}";
                        const method = isEditMode ? 'post' : 'post';

                        const response = await axios.post(url, formData);

                        if (response.data.success) {
                            window.showToast('success', response.data.message || 'Saved successfully!');
                            table.draw();
                            if (propertyTypeModal) propertyTypeModal.hide();
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
                const form = document.getElementById('propertyTypeForm');
                if (form) {
                    form.reset();
                    document.getElementById('propertyTypeId').value = '';
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

            // Edit Property Type
            window.editPropertyType = function(id) {
                $.ajax({
                    url: `{{ route('property-type.edit', '') }}/${id}`,
                    type: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            isEditMode = true;
                            editingId = id;
                            document.getElementById('propertyTypeId').value = response.data.id;
                            document.getElementById('name').value = response.data.name;
                            document.getElementById('description').value = response.data.description;
                            document.getElementById('propertyTypeModalLabel').textContent = 'Edit Property Type';
                            document.getElementById('submitBtnText').textContent = 'Update';
                            if (propertyTypeModal) propertyTypeModal.show();
                        }
                    },
                    error: function(xhr) {
                        window.showToast('error', xhr.responseJSON?.message || 'Error loading property type');
                    }
                });
            };

            // Delete Confirm
            window.deletePropertyType = function(id) {
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
                        $.ajax({
                            type: "DELETE",
                            url: `{{ route('property-type.delete', '') }}/${id}`,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(resp) {
                                window.showToast('success', resp.message || 'Deleted successfully!');
                                table.draw();
                            },
                            error: function(error) {
                                window.showToast('error', error.responseJSON?.message || 'Error deleting property type');
                            }
                        });
                    }
                });
            };

            // Toggle Status
            window.togglePropertyTypeStatus = function(id) {
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
                            url: `{{ route('property-type.toggle.status', '') }}/${id}`,
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

            console.log('Property Types DataTable initialized');
        };

        // Auto-initialize
        if (typeof window.initPropertyTypesSection === 'function') {
            window.initPropertyTypesSection();
        }
    })();
</script>
