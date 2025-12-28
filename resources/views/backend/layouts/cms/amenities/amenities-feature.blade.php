<div class="row">
    {{-- how is works header content --}}
    <div class="col-lg-4">
        <div class="card box-shadow-0">
            <div class="card-header bg-light">
                <h4 class="card-title">Amenities Page - Featured Amenities</h4>
            </div>
            <div class="card-body">
                <form id="howItWorksSectionForm" method="post" action="{{ route('cms.amenities.header.update') }}">
                    @csrf

                    {{-- Title --}}
                    <div class="form-group mb-3">
                        <label for="amenities_feature_title" class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" id="amenities_feature_title"
                            placeholder="Enter title" value="{{ $data->title ?? '' }}">
                        <div class="invalid-feedback"></div>
                    </div>

                    {{-- Sub  title --}}
                    <div class="form-group mb-3">
                        <label for="amenities_feature_sub_title" class="form-label">Sub Title</label>
                        <input type="text" class="form-control" name="sub_title" id="amenities_feature_sub_title"
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

    {{-- amenities feature card item --}}
    <div class="col-lg-8">
        <div class="card box-shadow-0">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">Amenities Featured Items</h4>
                <button class="btn btn-primary btn-sm" id="addItemBtn">
                    <i class="fe fe-plus"></i> Add Item
                </button>
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table text-nowrap mb-0 table-bordered" id="featureTable">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="15%">Image</th>
                                <th width="30%">Title</th>
                                <th width="35%">Description</th>
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
<div class="modal fade" id="featureItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="featureForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id" id="featureID">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="featureItemModalLabel">Add Item</h5>
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

                        <!-- Desctription -->
                        <div class="form-group mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="summernote" class="summernote form-control @error('description') is-invalid @enderror"
                                rows="6" placeholder="Enter description"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>

                        <!-- Image -->
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Item Image <span class="text-danger"
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
                    <button type="submit" class="btn btn-primary" id="featureItemSubmitBtn">
                        <span class="spinner-border spinner-border-sm d-none" id="featureSpinner"></span>
                        <span id="featureItemSubmitText">Save Item</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    (function() {
        // Initialize Summernote
        function initSummernote() {
            if (typeof $.fn.summernote !== 'undefined') {
                $('#summernote').summernote({
                    height: 150,
                    toolbar: [
                        ['style', ['bold', 'italic', 'underline', 'clear']],
                        ['font', ['strikethrough', 'superscript', 'subscript']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['insert', ['link']],
                        ['view', ['fullscreen', 'codeview']]
                    ]
                });
            }
        }

        // Initialize header form
        if (typeof window.initFeatureItemSection === 'function') {
            window.initFeatureItemSection();
        }

        // Initialize items management with Yajra DataTables
        window.initFeaturesItems = function() {
            let featureItemModal = null;
            let featureTable = null;
            let isFeatureEditMode = false;
            let editingId = null;

            // Initialize Modal
            const modalElement = document.getElementById('featureItemModal');
            if (modalElement && typeof bootstrap !== 'undefined') {
                featureItemModal = new bootstrap.Modal(modalElement);
                modalElement.addEventListener('hidden.bs.modal', resetForm);
                // Initialize Summernote when modal opens
                modalElement.addEventListener('shown.bs.modal', initSummernote);
            }

            // Initialize Yajra DataTable
            initDataTable();

            // Add Item Button
            document.getElementById('addItemBtn')?.addEventListener('click', () => {
                isFeatureEditMode = false;
                editingId = null;
                document.getElementById('featureItemModalLabel').textContent = 'Add New Feature Item';
                document.getElementById('featureItemSubmitText').textContent = 'Save Item';
                document.getElementById('imageRequired').style.display = 'inline';
                document.getElementById('itemImage').setAttribute('required', 'required');
                if (featureItemModal) featureItemModal.show();
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
            const featureForm = document.getElementById('featureForm');
            if (featureForm) {
                featureForm.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    const btn = document.getElementById('featureItemSubmitBtn');
                    const spinner = document.getElementById('featureSpinner');
                    const text = document.getElementById('featureItemSubmitText');

                    clearErrors(this);
                    btn.disabled = true;
                    spinner.classList.remove('d-none');
                    text.textContent = isFeatureEditMode ? 'Updating...' : 'Saving...';

                    try {
                        const formData = new FormData(this);

                        // Get Summernote content
                        const description = $('#summernote').summernote('code');
                        formData.set('description', description);

                        // const url = isFeatureEditMode ?
                        //     window.route('cms.amenities.item.update') :
                        //     window.route('cms.amenities.item.store');
                        const url = isFeatureEditMode ?
                            '/admin/cms/amenities/features/item/update' :
                            '/admin/cms/amenities/features/item/store';

                        console.log('Generated URL:', url);

                        const response = await axios.post(url, formData, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Content-Type': 'multipart/form-data'
                            }
                        });

                        if (response.data.success) {
                            window.showToast('success', response.data.message);
                            if (featureItemModal) featureItemModal.hide();
                            if (featureTable) featureTable.ajax.reload(null, false);
                        }
                    } catch (error) {
                        handleError(error, this);
                    } finally {
                        btn.disabled = false;
                        spinner.classList.add('d-none');
                        text.textContent = isFeatureEditMode ? 'Update Item' : 'Save Item';
                    }
                });
            }

            // Initialize Yajra DataTable
            function initDataTable() {
                const table = document.getElementById('featureTable');
                if (!table || !$.fn.DataTable) {
                    return;
                }

                const currentUrl = window.route('cms.section', {
                    section: 'amenities-feature'
                });

                featureTable = $('#featureTable').DataTable({
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
                            data: 'description',
                            name: 'description'
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
                    language: {
                        emptyTable: "No items found. Add your first item!",
                        processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
                    }
                });

                // Edit Item - Event Delegation
                $('#featureTable').on('click', '.edit-item', function() {
                    const id = $(this).data('id');
                    const title = $(this).data('title');
                    const description = $(this).data('description');
                    const image = $(this).data('image');

                    isFeatureEditMode = true;
                    editingId = id;

                    document.getElementById('featureID').value = id;
                    document.getElementById('itemTitle').value = title;

                    // Set Summernote content
                    $('#summernote').summernote('code', description || '');

                    document.getElementById('imageRequired').style.display = 'none';
                    document.getElementById('itemImage').removeAttribute('required');

                    if (image) {
                        document.getElementById('imagePreview').src = window.assetUrl(image);
                        document.getElementById('imagePreviewContainer').style.display = 'block';
                    }

                    document.getElementById('featureItemModalLabel').textContent = 'Update Item';
                    document.getElementById('featureItemSubmitText').textContent = 'Update Item';

                    if (featureItemModal) featureItemModal.show();
                });

                // Delete Item - Event Delegation
                $('#featureTable').on('click', '.delete-feature-item', async function() {
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
                            const response = await axios.delete(
                                '/admin/cms/amenities/features/item/delete', {
                                    data: {
                                        id: id
                                    },
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest'
                                    }
                                });

                            window.showToast('success', response.data.message);
                            featureTable.ajax.reload(null, false);
                        } catch (error) {
                            window.showToast('error', error.response?.data?.message ||
                                'Failed to delete');
                        }
                    }
                });
            }

            // Helper Functions
            function resetForm() {
                const form = document.getElementById('featureForm');
                if (form) {
                    form.reset();
                    document.getElementById('featureID').value = '';
                    document.getElementById('imagePreviewContainer').style.display = 'none';

                    // Reset Summernote
                    $('#summernote').summernote('code', '');

                    clearErrors(form);
                    isFeatureEditMode = false;
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
                console.error('Error:', error);
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
        window.initFeaturesItems();
    })();
</script>
