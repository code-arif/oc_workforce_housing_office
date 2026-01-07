<div class="row">
    <div class="col-lg-12">
        <div class="card box-shadow-0">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Pricing Plans Management</h4>
                <button class="btn btn-primary btn-sm" id="addPlanBtn">
                    <i class="fe fe-plus"></i> Add Plan
                </button>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap mb-0" id="pricingTable">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="20%">Name</th>
                                <th width="15%">Price Per Bed</th>
                                <th width="15%">Tenants Per Room</th>
                                <th width="25%">Amenities</th>
                                <th width="8%">Status</th>
                                <th width="12%">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pricing Plan Modal -->
<div class="modal fade" id="planModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="planForm">
                @csrf
                <input type="hidden" name="id" id="planId">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="planModalLabel">Add Pricing Plan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Plan Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="planName" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Price Per Bed <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" name="price_per_bed"
                                id="planPrice" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tenants Per Room <span class="text-danger">*</span></label>
                            <input type="number" min="1" max="20" class="form-control"
                                name="tenants_per_room" id="planTenants" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Amenities (Select multiple)</label>
                            <select class="form-select" name="amenities[]" id="planAmenities" multiple>
                                <option value="Security">Security</option>
                                <option value="Free High-Speed Internet">Free High-Speed Internet</option>
                                <option value="Laundry">Laundry</option>
                                <option value="Lounges">Lounges</option>
                                <option value="Onsite Office Staff">Onsite Office Staff</option>
                                <option value="Table Tennis">Table Tennis</option>
                                <option value="Gym">Gym</option>
                                <option value="Swimming Pool">Swimming Pool</option>
                                <!-- Add more as needed -->
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple</small>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label">Description (Optional)</label>
                            <textarea class="form-control" name="description" id="planDescription" rows="4"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="planSubmitBtn">
                        <span class="spinner-border spinner-border-sm d-none" id="planSpinner"></span>
                        <span id="planSubmitText">Save Plan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<script>
    (function() {
        let planModal = null;
        let pricingTable = null;
        let isEditMode = false;

        const modalEl = document.getElementById('planModal');
        if (modalEl) {
            planModal = new bootstrap.Modal(modalEl);
        }

        function initDataTable() {

            const table = document.getElementById('pricingTable');
            if (!table || !$.fn.DataTable) {
                return;
            }

            const currentUrl = window.route('cms.section', {
                section: 'pricing-item'
            });

            pricingTable = $('#pricingTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: currentUrl,
                    type: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    error: function(xhr, error, thrown) {
                        console.error('DataTable Error:', error, xhr.responseText);
                        window.showToast('error', 'Failed to load items');
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'price',
                        name: 'price'
                    },
                    {
                        data: 'tenants',
                        name: 'tenants'
                    },
                    {
                        data: 'amenities_list',
                        name: 'amenities_list'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'action',
                        name: 'action'
                    }
                ],
                order: [
                    [0, 'desc']
                ],
                pageLength: 10,
                language: {
                    emptyTable: "No pricing plans yet. Add your first one!"
                }
            });
        }

        initDataTable();

        // Add Button
        $('#addPlanBtn').on('click', function() {
            isEditMode = false;
            $('#planForm')[0].reset();
            $('#planId').val('');
            $('#planModalLabel').text('Add Pricing Plan');
            $('#planSubmitText').text('Save Plan');
            planModal.show();
        });

        // Edit Button
        $('#pricingTable').on('click', '.edit-plan', function() {
            isEditMode = true;
            const data = $(this).data();

            $('#planId').val(data.id);
            $('#planName').val(data.name);
            $('#planPrice').val(data.price);
            $('#planTenants').val(data.tenants);
            $('#planDescription').val(data.description);

            // Set multi-select amenities
            $('#planAmenities').val(data.amenities);

            $('#planModalLabel').text('Update Pricing Plan');
            $('#planSubmitText').text('Update Plan');
            planModal.show();
        });

        // Form Submit
        $('#planForm').on('submit', async function(e) {
            e.preventDefault();
            const btn = $('#planSubmitBtn');
            const spinner = $('#planSpinner');
            const text = $('#planSubmitText');

            btn.prop('disabled', true);
            spinner.removeClass('d-none');
            text.text(isEditMode ? 'Updating...' : 'Saving...');

            try {
                const formData = new FormData(this);
                const url = isEditMode ?
                    '{{ route('cms.pricing.update') }}' :
                    '{{ route('cms.pricing.store') }}';

                const response = await axios.post(url, formData);

                if (response.data.success) {
                    window.showToast('success', response.data.message);
                    planModal.hide();
                    pricingTable.ajax.reload(null, false);
                }
            } catch (error) {
                if (error.response?.status === 422) {
                    window.showToast('error', Object.values(error.response.data.errors)[0][0]);
                } else {
                    window.showToast('error', error.response?.data?.message || 'Something went wrong');
                }
            } finally {
                btn.prop('disabled', false);
                spinner.addClass('d-none');
                text.text(isEditMode ? 'Update Plan' : 'Save Plan');
            }
        });

        // Delete
        $('#pricingTable').on('click', '.delete-plan', async function() {
            const id = $(this).data('id');

            const result = await Swal.fire({
                title: 'Are you sure?',
                text: "This plan will be deleted permanently!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!'
            });

            if (result.isConfirmed) {
                try {
                    await axios.delete('{{ route('cms.pricing.delete') }}', {
                        data: {
                            id
                        }
                    });
                    window.showToast('success', 'Plan deleted successfully');
                    pricingTable.ajax.reload(null, false);
                } catch (error) {
                    window.showToast('error', 'Failed to delete');
                }
            }
        });

    })();
</script>
