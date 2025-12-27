<div class="row">
    {{-- how is works header content --}}
    <div class="col-lg-4">
        <div class="card box-shadow-0">
            <div class="card-header bg-light">
                <h4 class="card-title">Home Page - How It Works</h4>
            </div>
            <div class="card-body">
                <form id="howItWorksSectionForm" method="post" action="{{ route('cms.home.how-it-works.update') }}">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="how_it_works_title" class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" id="how_it_works_title"
                            placeholder="Enter title" value="{{ $data->title ?? '' }}">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="how_it_works_sub_title" class="form-label">Sub Title</label>
                        <input type="text" class="form-control" name="sub_title" id="how_it_works_sub_title"
                            placeholder="Enter sub title" value="{{ $data->sub_title ?? '' }}">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <button class="btn btn-primary" type="submit" id="submitButton">
                            <span class="spinner-border spinner-border-sm d-none" id="howItWorkSpinner"></span>
                            <span id="submitBtnText">Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- how it works card item --}}
    <div class="col-lg-8">
        <div class="card box-shadow-0">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">How it works Items</h4>
                <button class="btn btn-primary btn-sm" id="addItemBtn">
                    <i class="fe fe-plus me-1"></i> Add Item
                </button>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table text-nowrap mb-0 table-bordered" id="itemsTable">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="15%">Icon</th>
                                <th width="30%">Title</th>
                                <th width="35%">Subtitle</th>
                                <th width="15%">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Item Modal (Add/Edit) -->
<div class="modal fade" id="itemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="itemForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="itemID">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="itemModalLabel">Add Item</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <!-- Title -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" id="itemTitle"
                                placeholder="Enter item title">
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Sub Title -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Sub Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="sub_title" id="itemSubTitle"
                                placeholder="Enter item subtitle">
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Icon -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Item Icon <span class="text-danger"
                                    id="imageRequired">*</span></label>
                            <input type="file" name="image" id="itemImage" class="form-control" accept="image/*">
                            <small class="text-muted">Recommended: 100x100px (Max: 2MB)</small>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Image Preview -->
                        <div class="col-md-12 mb-3" id="imagePreviewContainer" style="display:none;">
                            <label class="form-label">Preview:</label>
                            <img id="imagePreview" class="img-fluid border" style="max-height: 150px;">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="itemSubmitBtn">
                        <span class="spinner-border spinner-border-sm d-none" id="itemSpinner"></span>
                        <span id="itemSubmitText">Save Item</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function() {
        console.log('How It Works section with items loaded');

        // Initialize header form
        if (typeof window.initHowItWorksSection === 'function') {
            window.initHowItWorksSection();
        }

        // Initialize items management with Yajra DataTables
        window.initHowItWorksItems = function() {
            console.log('Initializing How It Works Items with Yajra...');

            let itemModal = null;
            let itemsTable = null;
            let isEditMode = false;
            let editingId = null;

            // Initialize Modal
            const modalElement = document.getElementById('itemModal');
            if (modalElement && typeof bootstrap !== 'undefined') {
                itemModal = new bootstrap.Modal(modalElement);
                modalElement.addEventListener('hidden.bs.modal', resetForm);
            }

            // Initialize Yajra DataTable
            initDataTable();

            // Add Item Button
            document.getElementById('addItemBtn')?.addEventListener('click', () => {
                isEditMode = false;
                editingId = null;
                document.getElementById('itemModalLabel').textContent = 'Add Item';
                document.getElementById('itemSubmitText').textContent = 'Save Item';
                document.getElementById('imageRequired').style.display = 'inline';
                document.getElementById('itemImage').setAttribute('required', 'required');
                if (itemModal) itemModal.show();
            });

            // Image Preview
            document.getElementById('itemImage')?.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    if (file.size > 2 * 1024 * 1024) {
                        window.showToast('error', 'Image size should not exceed 2MB');
                        e.target.value = '';
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('imagePreview').src = e.target.result;
                        document.getElementById('imagePreviewContainer').style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Item Form Submit
            const itemForm = document.getElementById('itemForm');
            if (itemForm) {
                itemForm.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    const btn = document.getElementById('itemSubmitBtn');
                    const spinner = document.getElementById('itemSpinner');
                    const text = document.getElementById('itemSubmitText');

                    clearErrors(this);
                    btn.disabled = true;
                    spinner.classList.remove('d-none');
                    text.textContent = isEditMode ? 'Updating...' : 'Saving...';

                    try {
                        const formData = new FormData(this);
                        const url = isEditMode ?
                            window.route('cms.home.how-it-works.update.item') :
                            window.route('cms.home.how-it-works.store');

                        const response = await axios.post(url, formData, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Content-Type': 'multipart/form-data'
                            }
                        });

                        if (response.data.success) {
                            window.showToast('success', response.data.message);
                            if (itemModal) itemModal.hide();
                            if (itemsTable) itemsTable.ajax.reload(null, false);
                        }
                    } catch (error) {
                        handleError(error, this);
                    } finally {
                        btn.disabled = false;
                        spinner.classList.add('d-none');
                        text.textContent = isEditMode ? 'Update Item' : 'Save Item';
                    }
                });
            }

            // Initialize Yajra DataTable
            function initDataTable() {
                const table = document.getElementById('itemsTable');
                if (!table || !$.fn.DataTable) {
                    console.error('DataTable or jQuery not available');
                    return;
                }

                // Get current section URL
                const currentUrl = window.route('cms.section', {
                    section: 'how-it-works'
                });

                itemsTable = $('#itemsTable').DataTable({
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
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'image',
                            name: 'image',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'title',
                            name: 'title'
                        },
                        {
                            data: 'sub_title',
                            name: 'sub_title'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        }
                    ],
                    order: [
                        [0, 'asc']
                    ],
                    pageLength: 10,
                    responsive: true,
                    language: {
                        emptyTable: "No items found. Add your first item!",
                        processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
                    }
                });

                // Edit Item - Event Delegation
                $('#itemsTable').on('click', '.edit-item', function() {
                    const id = $(this).data('id');
                    const title = $(this).data('title');
                    const subtitle = $(this).data('subtitle');
                    const image = $(this).data('image');

                    isEditMode = true;
                    editingId = id;

                    document.getElementById('itemID').value = id;
                    document.getElementById('itemTitle').value = title;
                    document.getElementById('itemSubTitle').value = subtitle;
                    document.getElementById('imageRequired').style.display = 'none';
                    document.getElementById('itemImage').removeAttribute('required');

                    if (image) {
                        document.getElementById('imagePreview').src = window.assetUrl(image);
                        document.getElementById('imagePreviewContainer').style.display = 'block';
                    }

                    document.getElementById('itemModalLabel').textContent = 'Update Item';
                    document.getElementById('itemSubmitText').textContent = 'Update Item';

                    if (itemModal) itemModal.show();
                });

                // Delete Item - Event Delegation
                $('#itemsTable').on('click', '.delete-item', async function() {
                    const id = $(this).data('id');

                    const result = await Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!'
                    });

                    if (result.isConfirmed) {
                        try {
                            const response = await axios.delete(window.route(
                                'cms.home.how-it-works.delete'), {
                                data: {
                                    id: id
                                },
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });

                            window.showToast('success', response.data.message);
                            itemsTable.ajax.reload(null, false);
                        } catch (error) {
                            window.showToast('error', error.response?.data?.message ||
                                'Failed to delete');
                        }
                    }
                });
            }

            // Helper Functions
            function resetForm() {
                const form = document.getElementById('itemForm');
                if (form) {
                    form.reset();
                    document.getElementById('itemID').value = '';
                    document.getElementById('imagePreviewContainer').style.display = 'none';
                    clearErrors(form);
                    isEditMode = false;
                    editingId = null;
                }
            }

            function clearErrors(form) {
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => {
                    el.textContent = '';
                    el.style.display = 'none';
                });
            }

            function handleError(error, form = null) {
                if (error.response?.status === 422 && error.response?.data?.errors) {
                    const errors = error.response.data.errors;
                    if (form) {
                        Object.keys(errors).forEach(field => {
                            const input = form.querySelector(`[name="${field}"]`);
                            if (input) {
                                input.classList.add('is-invalid');
                                const feedback = input.nextElementSibling;
                                if (feedback?.classList.contains('invalid-feedback')) {
                                    feedback.textContent = errors[field][0];
                                    feedback.style.display = 'block';
                                }
                            }
                        });
                    }
                    window.showToast('error', Object.values(errors).flat()[0]);
                } else {
                    window.showToast('error', error.response?.data?.message || 'An error occurred');
                }
            }
        };

        // Execute items initialization
        window.initHowItWorksItems();
    })();
</script>
