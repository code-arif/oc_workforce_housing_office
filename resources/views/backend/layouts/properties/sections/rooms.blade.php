{{-- Rooms Section --}}
<div class="row">
    <div class="col-lg-12">
        <div class="card box-shadow-0">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h4 class="card-title">Rooms</h4>
                <button class="btn btn-primary btn-sm" id="addRoomBtn">
                    <i class="fe fe-plus me-1"></i> Add Room
                </button>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap mb-0" id="roomsTable" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Unit</th>
                                <th>Beds</th>
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

<!-- Add/Edit Room Modal -->
<div class="modal fade" id="roomModal" tabindex="-1" style="z-index: 1060;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="roomForm">
                @csrf
                <input type="hidden" name="id" id="roomId">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="roomModalLabel">Add Room</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group mb-3">
                                <label for="property_id" class="form-label">Property <span class="text-danger">*</span></label>
                                <select class="form-control" name="property_id" id="property_id" required>
                                    <option value="">Select Property</option>
                                    @foreach(App\Models\Property::where('is_active', true)->get() as $property)
                                        <option value="{{ $property->id }}">{{ $property->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="unit_id" class="form-label">Unit <span class="text-danger">*</span></label>
                                <select class="form-control" name="unit_id" id="unit_id" required>
                                    <option value="">Select Unit</option>
                                    {{-- dynamically unit show --}}
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="room_number" class="form-label">Room Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="room_number" id="room_number" placeholder="Enter room number" required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="name" class="form-label">Room Name (Optional)</label>
                                <input type="text" class="form-control" name="name" id="name" placeholder="Enter room name">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="gender_designation" class="form-label">Gender Designation (Optional)</label>
                                <select class="form-control" name="gender_designation" id="gender_designation">
                                    <option value="">-- Select --</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0"><strong>Beds</strong></h6>
                                <button type="button" class="btn btn-sm btn-success" id="addBedBtn">
                                    <i class="fe fe-plus me-1"></i> Add Bed
                                </button>
                            </div>
                            <div id="bedContainer" class="border p-3" style="background-color: #f8f9fa; max-height: 400px; overflow-y: auto;">
                                <div class="bed-item-empty">No beds added. Click "Add Bed" to start.</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none me-2" id="roomSpinner"></span>
                        <span id="submitBtnText">Save Room</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .bed-item {
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 12px;
        margin-bottom: 10px;
        position: relative;
    }

    .bed-item-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .bed-item-title {
        font-weight: 600;
        color: #333;
        font-size: 13px;
    }

    .bed-remove-btn {
        background: none;
        border: none;
        color: #dc3545;
        cursor: pointer;
        font-size: 16px;
        padding: 0;
    }

    .bed-remove-btn:hover {
        color: #a71d2a;
    }

    .bed-fields input {
        font-size: 12px;
        padding: 6px 8px;
    }

    .bed-item-empty {
        text-align: center;
        padding: 20px;
        color: #999;
        font-size: 12px;
    }
</style>

<script>
    (function() {
        window.initRoomsSection = function() {
            console.log('Rooms section initialized');

            let roomModal = null;
            let isEditMode = false;
            let editingId = null;
            let bedCount = 0;

            // Initialize Modal
            const modalElement = document.getElementById('roomModal');
            if (modalElement && typeof bootstrap !== 'undefined') {
                roomModal = new bootstrap.Modal(modalElement);
                modalElement.addEventListener('hidden.bs.modal', resetForm);
            }

            // Initialize DataTable
            if (!$.fn.DataTable) {
                console.error('DataTables not available');
                return;
            }

            const table = $('#roomsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('rooms.list') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false },
                    { data: 'room_number', name: 'room_number' },
                    { data: 'unit', name: 'unit', orderable: false },
                    { data: 'beds_count', name: 'beds_count', orderable: false },
                    { data: 'status', name: 'status', orderable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[0, 'asc']],
                pageLength: 10,
                responsive: true
            });

            // Add Button
            document.getElementById('addRoomBtn')?.addEventListener('click', () => {
                isEditMode = false;
                editingId = null;
                bedCount = 0;
                document.getElementById('roomModalLabel').textContent = 'Add Room';
                document.getElementById('submitBtnText').textContent = 'Save Room';
                clearBeds();
                if (roomModal) roomModal.show();
            });

            // Add Bed Button
            document.getElementById('addBedBtn')?.addEventListener('click', () => {
                addBedInput();
            });

            // Form Submit
            const form = document.getElementById('roomForm');
            if (form) {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const submitBtn = this.querySelector('button[type="submit"]');
                    const spinner = document.getElementById('roomSpinner');
                    const btnText = document.getElementById('submitBtnText');

                    submitBtn.disabled = true;
                    spinner.classList.remove('d-none');
                    btnText.textContent = isEditMode ? 'Updating...' : 'Saving...';

                    clearFormErrors(this);

                    try {
                        const formData = new FormData(this);
                        const url = isEditMode 
                            ? "{{ route('rooms.update', '') }}/" + editingId
                            : "{{ route('rooms.store') }}";

                        const response = await axios.post(url, formData);

                        if (response.data.success) {
                            window.showToast('success', response.data.message || 'Saved successfully!');
                            table.draw();
                            if (roomModal) roomModal.hide();
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
                        btnText.textContent = isEditMode ? 'Update' : 'Save Room';
                    }

                    return false;
                });
            }

            // Helper Functions
            function resetForm() {
                const form = document.getElementById('roomForm');
                if (form) {
                    form.reset();
                    document.getElementById('roomId').value = '';
                    clearFormErrors(form);
                    isEditMode = false;
                    editingId = null;
                    bedCount = 0;
                    clearBeds();
                }
            }

            function clearFormErrors(form) {
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => {
                    el.textContent = '';
                });
            }

            function addBedInput(bedData = null) {
                const bedContainer = document.getElementById('bedContainer');
                
                const emptyMsg = bedContainer.querySelector('.bed-item-empty');
                if (emptyMsg) {
                    emptyMsg.remove();
                }

                bedCount++;
                const bedId = bedData?.id || '';
                const bedNumber = bedData?.bed_number || '';

                const bedHTML = `
                    <div class="bed-item" data-bed-id="${bedId}">
                        <div class="bed-item-header">
                            <span class="bed-item-title">Bed #${bedCount}</span>
                            <button type="button" class="bed-remove-btn" onclick="window.removeBedRoom(this)" title="Remove">
                                <i class="fe fe-trash-2"></i>
                            </button>
                        </div>
                        <div class="bed-fields">
                            <input type="text"
                                name="beds[${bedCount}][bed_number]"
                                class="form-control form-control-sm"
                                placeholder="e.g., 1, 2, 3"
                                value="${bedNumber}">
                        </div>
                        ${bedId ? `<input type="hidden" name="beds[${bedCount}][id]" value="${bedId}">` : ''}
                    </div>
                `;

                bedContainer.insertAdjacentHTML('beforeend', bedHTML);
            }

            function clearBeds() {
                const bedContainer = document.getElementById('bedContainer');
                bedContainer.innerHTML = '<div class="bed-item-empty">No beds added. Click "Add Bed" to start.</div>';
                bedCount = 0;
            }

            // Edit Room
            window.editRoom = function(id) {
                $.ajax({
                    url: `{{ route('rooms.edit', '') }}/${id}`,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            isEditMode = true;
                            editingId = id;
                            bedCount = 0;

                            const propertyId = response.data.unit.property_id;
                            const unitId = response.data.unit_id;

                            // 1️⃣ Set property
                            document.getElementById('property_id').value = propertyId;

                            // 2️⃣ Load units and auto-select unit
                            if (propertyId) {
                                getUnits(propertyId, unitId);
                            }

                            document.getElementById('roomId').value = response.data.id;
                            document.getElementById('room_number').value = response.data.room_number;
                            document.getElementById('name').value = response.data.name || '';
                            document.getElementById('gender_designation').value = response.data.gender_designation || '';

                            document.getElementById('roomModalLabel').textContent = 'Edit Room';
                            document.getElementById('submitBtnText').textContent = 'Update';

                            clearBeds();
                            if (response.data.beds?.length) {
                                response.data.beds.forEach(bed => addBedInput(bed));
                            }

                            if (roomModal) roomModal.show();
                        }
                    }
                });
            };


            // Delete Confirm
            window.deleteRoom = function(id) {
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
                            url: `{{ route('rooms.delete', '') }}/${id}`,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(resp) {
                                window.showToast('success', resp.message || 'Deleted successfully!');
                                table.draw();
                            },
                            error: function(error) {
                                window.showToast('error', error.responseJSON?.message || 'Error deleting room');
                            }
                        });
                    }
                });
            };


            // Toggle Room Status
            window.toggleRoomStatus = function(id) {
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
                            url: `{{ route('rooms.toggle.status', '') }}/${id}`,
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

            document.getElementById('property_id')?.addEventListener('change', () => {
                getUnits(document.getElementById('property_id').value);
            });


            window.getUnits = function(propertyId, selectedUnitId = null) {
                let unitSelect = document.getElementById('unit_id');
                unitSelect.innerHTML = '<option value="">Select Unit</option>';

                $.ajax({
                    url: `{{ route('rooms.get.units', '') }}/${propertyId}`,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            response.data.forEach(unit => {
                                let option = document.createElement('option');
                                option.value = unit.id;
                                option.textContent = unit.name;

                                if (selectedUnitId && unit.id == selectedUnitId) {
                                    option.selected = true;
                                }

                                unitSelect.appendChild(option);
                            });
                        }
                    },
                    error: function(xhr) {
                        window.showToast('error', xhr.responseJSON?.message || 'Error loading units');
                    }
                });
            };


            console.log('Rooms DataTable initialized');
        };

        // Remove bed function
        window.removeBedRoom = function(btn) {
            btn.closest('.bed-item').remove();
            const bedContainer = document.getElementById('bedContainer');
            if (bedContainer.children.length === 0) {
                bedContainer.innerHTML = '<div class="bed-item-empty">No beds added. Click "Add Bed" to start.</div>';
            }
        };

        // Auto-initialize
        if (typeof window.initRoomsSection === 'function') {
            window.initRoomsSection();
        }
    })();
</script>
