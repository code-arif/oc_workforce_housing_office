{{-- Properties Section --}}
<div class="row">
    <div class="col-lg-12">
        <div class="card box-shadow-0">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h4 class="card-title">Properties</h4>
                @can('property.create')
                <button class="btn btn-primary btn-sm" id="addPropertyBtn">
                    <i class="fe fe-plus me-1"></i> Add Property
                </button>
                @endcan
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap mb-0" id="propertiesTable" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Rent</th>
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

<!-- Add/Edit Property Modal -->
<div class="modal fade" id="propertyModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="propertyForm">
                @csrf
                <input type="hidden" name="id" id="propertyId">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="propertyModalLabel">Add Property</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label for="prop_name" class="form-label">Property Name</label>
                        <input type="text" class="form-control" name="name" id="prop_name" placeholder="Enter property name">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="prop_address" class="form-label">Address</label>
                        <input type="text" class="form-control" name="address" id="prop_address" placeholder="Enter address">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="prop_type" class="form-label">Property Type</label>
                        <select class="form-control" name="property_type_id" id="prop_type">
                            <option value="">Select Property Type</option>
                            @foreach(App\Models\PropertyType::where('is_active', true)->get() as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="prop_description" class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="prop_description" rows="3" placeholder="Enter description"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none me-2" id="propertySpinner"></span>
                        <span id="submitBtnText">Save Property</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function() {
        window.initPropertiesSection = function() {
            // console.log('Properties section initialized');

            if (!$.fn.DataTable) {
                console.error('DataTables not available');
                return;
            }

            let propertyModal = null;
            let isEditMode = false;
            let editingId = null;

            // Initialize Modal
            const modalElement = document.getElementById('propertyModal');
            if (modalElement && typeof bootstrap !== 'undefined') {
                propertyModal = new bootstrap.Modal(modalElement);
                modalElement.addEventListener('hidden.bs.modal', resetForm);
            }

            // Initialize DataTable
            const table = $('#propertiesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('property.get.data') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false },
                    { data: 'name', name: 'name' },
                    { data: 'description', name: 'description', orderable: false },
                    { data: 'rent', name: 'rent', orderable: false },
                    { data: 'status', name: 'is_active', orderable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[0, 'asc']],
                pageLength: 10,
                responsive: true,
                language: {
                    emptyTable: "No properties found",
                    infoEmpty: "No entries to show"
                }
            });

            // Add Property Button
            document.getElementById('addPropertyBtn')?.addEventListener('click', () => {
                isEditMode = false;
                editingId = null;
                document.getElementById('propertyModalLabel').textContent = 'Add Property';
                document.getElementById('submitBtnText').textContent = 'Save Property';
                if (propertyModal) propertyModal.show();
            });

            // Form Submit
            const form = document.getElementById('propertyForm');
            if (form) {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const submitBtn = this.querySelector('button[type="submit"]');
                    const spinner = document.getElementById('propertySpinner');
                    const btnText = document.getElementById('submitBtnText');

                    submitBtn.disabled = true;
                    spinner.classList.remove('d-none');
                    btnText.textContent = isEditMode ? 'Updating...' : 'Saving...';

                    clearFormErrors(this);

                    try {
                        const formData = new FormData(this);
                        const url = isEditMode 
                            ? "{{ route('property.update', '') }}/" + editingId
                            : "{{ route('property.store') }}";

                        const response = await axios.post(url, formData);

                        if (response.data.success) {
                            window.showToast('success', response.data.message || 'Saved successfully!');
                            table.draw();
                            if (propertyModal) propertyModal.hide();
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
                        btnText.textContent = isEditMode ? 'Update' : 'Save Property';
                    }

                    return false;
                });
            }

            // Helper Functions
            function resetForm() {
                const form = document.getElementById('propertyForm');
                if (form) {
                    form.reset();
                    document.getElementById('propertyId').value = '';
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
            window.editProperty = function(id) {
                $.ajax({
                    url: `{{ route('property.edit', '') }}/${id}`,
                    type: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            isEditMode = true;
                            editingId = id;
                            document.getElementById('propertyId').value = response.data.id;
                            document.getElementById('prop_name').value = response.data.name;
                            document.getElementById('prop_address').value = response.data.address;
                            document.getElementById('prop_type').value = response.data.property_type_id;
                            document.getElementById('propertyModalLabel').textContent = 'Edit Property';
                            document.getElementById('submitBtnText').textContent = 'Update';
                            if (propertyModal) propertyModal.show();
                        }
                    },
                    error: function(xhr) {
                        window.showToast('error', xhr.responseJSON?.message || 'Error loading property');
                    }
                });
            };

            // Delete Confirm
            window.propertyDeleteConfirm = function(id) {
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
                        window.deleteProperty(id);
                    }
                });
            };

            // Delete Unit
            window.deleteProperty = function(id) {
                $.ajax({
                    type: "DELETE",
                    url: `{{ route('property.delete', '') }}/${id}`,
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
            window.togglePropertyStatus = function(id) {
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

            // console.log('Properties DataTable initialized');
        };

        // Auto-initialize
        if (typeof window.initPropertiesSection === 'function') {
            window.initPropertiesSection();
        }
    })();
</script>

