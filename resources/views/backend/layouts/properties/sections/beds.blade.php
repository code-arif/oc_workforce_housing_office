{{-- Beds Section --}}
<div class="row">
    <div class="col-lg-12">
        <div class="card box-shadow-0">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h4 class="card-title">Beds</h4>
                <button class="btn btn-primary btn-sm d-inline-flex align-items-center gap-1" id="addBedBtn">
                    <i class="fe fe-plus me-1"></i> Add Bed
                </button>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap mb-0" id="bedsTable" width="100%">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Bed</th>
                                <th>Description</th>
                                {{-- <th>Room</th> --}}
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

<!-- Add/Edit Bed Modal -->
<div class="modal fade" id="bedModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="bedForm">
                @csrf
                <input type="hidden" name="id" id="bedId">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="bedModalLabel">Add Bed</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="form-group mb-3">
                        <label for="property_id" class="form-label">Property</label>
                        <select class="form-control" id="property_id">
                            <option value="">Select Property</option>
                            @foreach(App\Models\Property::where('is_active', true)->get() as $property)
                                <option value="{{ $property->id }}">{{ $property->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="unit_id" class="form-label">Unit</label>
                        <select class="form-control" id="unit_id">
                            <option value="">Select a Property First</option>

                        </select>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="room_id" class="form-label">Room <span class="text-danger">*</span></label>
                        <select class="form-control" name="room_id" id="room_id">
                            <option value="">Select a Unit First</option>

                        </select>
                        <div class="existingBeds" ></div>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="bed_number" class="form-label">Bed Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="bed_number" id="bed_number" placeholder="Enter bed number">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Amenities</label>
                        <div id="amenitiesContainer">
                            <!-- Amenities checkboxes will be loaded here -->
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none me-2" id="bedSpinner"></span>
                        <span id="submitBtnText">Save</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function() {
        window.initBedsSection = function() {
            // console.log('Beds section initialized');

            let bedModal = null;
            let isEditMode = false;
            let editingId = null;

            // Initialize Modal
            const modalElement = document.getElementById('bedModal');
            if (modalElement && typeof bootstrap !== 'undefined') {
                bedModal = new bootstrap.Modal(modalElement);
                modalElement.addEventListener('hidden.bs.modal', resetForm);
            }

            // Initialize DataTable
            if (!$.fn.DataTable) {
                console.error('DataTables not available');
                return;
            }

            const table = $('#bedsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('beds.list') }}",
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false },
                    { data: 'bed_label', name: 'bed_label' },
                    { data: 'description', name: 'description', orderable: false },
                    // { data: 'room', name: 'room', orderable: false },
                    { data: 'status', name: 'status', orderable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false }
                ],
                order: [[0, 'asc']],
                pageLength: 10,
            });

            // Add Button
            document.getElementById('addBedBtn')?.addEventListener('click', () => {
                isEditMode = false;
                editingId = null;
                document.getElementById('bedModalLabel').textContent = 'Add Bed';
                document.getElementById('submitBtnText').textContent = 'Save';
                loadAmenities();
                if (bedModal) bedModal.show();
            });

            // Form Submit
            const form = document.getElementById('bedForm');
            if (form) {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    const submitBtn = this.querySelector('button[type="submit"]');
                    const spinner = document.getElementById('bedSpinner');
                    const btnText = document.getElementById('submitBtnText');

                    submitBtn.disabled = true;
                    spinner.classList.remove('d-none');
                    btnText.textContent = isEditMode ? 'Updating...' : 'Saving...';

                    clearFormErrors(this);

                    try {
                        const formData = new FormData(this);
                        const url = isEditMode
                            ? "{{ route('beds.update', '') }}/" + editingId
                            : "{{ route('beds.store') }}";

                        const response = await axios.post(url, formData);

                        if (response.data.success) {
                            window.showToast('success', response.data.message || 'Saved successfully!');
                            table.draw();
                            if (bedModal) bedModal.hide();
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
                const form = document.getElementById('bedForm');
                if (form) {
                    form.reset();
                    document.getElementById('bedId').value = '';
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

            function loadAmenities(selectedAmenities = []) {
                const container = document.getElementById('amenitiesContainer');
                container.innerHTML = '<p>Loading amenities...</p>';

                $.ajax({
                    url: `{{ route('beds.get.amenities') }}`,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            container.innerHTML = '';
                            response.data.forEach(amenity => {
                                const isChecked = selectedAmenities.includes(amenity.id) ? 'checked' : '';
                                const checkboxHtml = `
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="amenities[]" value="${amenity.id}" id="amenity_${amenity.id}" ${isChecked}>
                                        <label class="form-check-label" for="amenity_${amenity.id}">
                                            ${amenity.name}
                                        </label>
                                    </div>
                                `;
                                container.insertAdjacentHTML('beforeend', checkboxHtml);
                            });
                        } else {
                            container.innerHTML = '<p class="text-danger">Failed to load amenities.</p>';
                        }
                    },
                    error: function(xhr) {
                        container.innerHTML = '<p class="text-danger">Error loading amenities.</p>';
                        window.showToast('error', xhr.responseJSON?.message || 'Error loading amenities');
                    }
                });
            }

            window.editBed = function(id) {
                $.ajax({
                    url: `{{ route('beds.edit', '') }}/${id}`,
                    type: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            isEditMode = true;
                            editingId = id;
                            propertyId = response.data.room.unit.property_id;
                            unitId = response.data.room.unit_id;
                            roomId = response.data.room_id;

                            // 1️⃣ Set property
                            document.getElementById('property_id').value = propertyId;

                            // 2️⃣ Load units and auto-select unit
                            if (propertyId) {
                                getUnits(propertyId, unitId);
                            }

                            // 3️⃣ Load rooms and auto-select room
                            if (unitId) {
                                getRooms(unitId, roomId);
                            }

                            document.getElementById('bedId').value = response.data.id;
                            document.getElementById('room_id').value = response.data.room_id;
                            document.getElementById('bed_number').value = response.data.bed_number || '';
                            document.getElementById('bedModalLabel').textContent = 'Edit Bed';
                            document.getElementById('submitBtnText').textContent = 'Update';
                            loadAmenities(response.data.amenities.map(a => a.id));
                            if (bedModal) bedModal.show();
                        }
                    },
                    error: function(xhr) {
                        window.showToast('error', xhr.responseJSON?.message || 'Error loading bed');
                    }
                });
            };

            // Delete Confirm
            window.deleteBed = function(id) {
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
                            url: `{{ route('beds.delete', '') }}/${id}`,
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(resp) {
                                window.showToast('success', resp.message || 'Deleted successfully!');
                                table.draw();
                            },
                            error: function(error) {
                                window.showToast('error', error.responseJSON?.message || 'Error deleting bed');
                            }
                        });
                    }
                });
            };

            // Toggle Bed Status
            window.toggleBedStatus = function(id) {
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
                            url: `{{ route('beds.toggle.status', '') }}/${id}`,
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

            document.getElementById('unit_id')?.addEventListener('change', () => {
                getRooms(document.getElementById('unit_id').value);
            });

            document.getElementById('room_id')?.addEventListener('change', () => {
                getBeds(document.getElementById('room_id').value);
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

            window.getRooms = function(unitId, selectedRoomId = null) {
                let roomSeleted = document.getElementById('room_id');
                let bedListDiv = document.querySelector('.existingRoom');
                roomSeleted.innerHTML = '<option value="">Select Room</option>';

                $.ajax({
                    url: `{{ route('beds.get.rooms', '') }}/${unitId}`,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            response.data.forEach(room => {
                                let option = document.createElement('option');
                                option.value = room.id;
                                option.textContent = room.room_number;

                                if (selectedRoomId && room.id == selectedRoomId) {
                                    option.selected = true;
                                }

                                roomSeleted.appendChild(option);
                            });

                        }
                    },
                    error: function(xhr) {
                        window.showToast('error', xhr.responseJSON?.message || 'Error loading beds');
                    }
                });
            };

            window.getBeds = function(roomId) {
                let bedListDiv = document.querySelector('.existingBeds');
                bedListDiv.innerHTML = 'Loading existing beds...';
                $.ajax({
                    url: `{{ route('beds.get.beds', '') }}/${roomId}`,
                    type: 'GET',
                    success: function(response) {
                        bedListDiv.style.display = 'block';
                        if (response.success) {
                            bedListDiv.innerHTML = '';
                            if (response.data.length > 0) {
                                let bedListHtml = '<strong>Existing Beds:</strong>';
                                response.data.forEach(bed => {
                                    bedListHtml += `<span class="text-success me-1"> ${bed.bed_number}</span>, `;
                                });
                                bedListDiv.innerHTML = bedListHtml;
                            } else {
                                bedListDiv.innerHTML = '<p>No existing beds for this room.</p>';
                            }
                        }
                    },
                    error: function(xhr) {
                        window.showToast('error', xhr.responseJSON?.message || 'Error loading beds');
                    }
                });
            };

            // console.log('Beds DataTable initialized');
        };

        // Auto-initialize
        if (typeof window.initBedsSection === 'function') {
            window.initBedsSection();
        }


    })();
</script>
<style>
    .form-check{
       margin-right: 12px;
       display: inline-block !important;
    }
</style>
