@extends('backend.app', ['title' => 'Social Links'])

@push('styles')
    <link href="{{ asset('default/datatable.css') }}" rel="stylesheet" />
    <style>
        /* Custom Toggle Switch */
        .custom-toggle {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 30px;
        }

        .custom-toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 30px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.toggle-slider {
            background-color: #4CAF50;
        }

        input:checked+.toggle-slider:before {
            transform: translateX(30px);
        }
    </style>
@endpush

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Social Links</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Settings</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Social Links</li>
                        </ol>
                    </div>
                </div>

                <!-- MAIN CARD -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                                <h3 class="card-title mb-0">Manage Social Links</h3>
                                <button class="btn btn-primary btn-sm" id="addSocialBtn">
                                    <i class="fe fe-plus me-1"></i> Add Social Link
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered text-nowrap" id="socialTable">
                                        <thead>
                                            <tr>
                                                <th width="5%">#</th>
                                                <th width="15%">Platform</th>
                                                <th width="10%">Icon</th>
                                                <th width="35%">URL</th>
                                                <th width="15%">Status</th>
                                                <th width="20%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Social Link Modal -->
    <div class="modal fade" id="socialModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="socialForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" id="socialId">

                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title text-white" id="socialModalLabel">Add Social Link</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <!-- Platform -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Platform <span class="text-danger">*</span></label>
                                <select name="name" id="socialName" class="form-select" required>
                                    <option value="">Select Platform</option>
                                    <option value="facebook">Facebook</option>
                                    <option value="twitter">Twitter/X</option>
                                    <option value="instagram">Instagram</option>
                                    <option value="linkedin">LinkedIn</option>
                                    <option value="youtube">YouTube</option>
                                    <option value="tiktok">TikTok</option>
                                    <option value="pinterest">Pinterest</option>
                                    <option value="whatsapp">WhatsApp</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" id="socialStatus" class="form-select" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- URL -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Profile URL <span class="text-danger">*</span></label>
                                <input type="url" class="form-control" name="url" id="socialUrl"
                                    placeholder="https://facebook.com/yourpage" required>
                                <small class="text-muted">Enter the full URL of your social profile</small>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Icon -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Icon <span class="text-danger" id="iconRequired">*</span></label>
                                <input type="file" name="icon" id="socialIcon" class="form-control" accept="image/*">
                                <small class="text-muted">Max: 5MB | Formats: jpeg, png, jpg, gif, svg, webp</small>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- Image Preview -->
                            <div class="col-md-12 mb-3" id="iconPreviewContainer" style="display:none;">
                                <label class="form-label">Current Icon:</label>
                                <img id="iconPreview" class="img-fluid border rounded" style="max-height: 100px;">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="socialSubmitBtn">
                            <span class="spinner-border spinner-border-sm d-none" id="socialSpinner"></span>
                            <span id="socialSubmitText">Save</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function() {
            let socialModal = null;
            let socialTable = null;
            let isEditMode = false;

            // Initialize Modal
            const modalElement = document.getElementById('socialModal');
            if (modalElement && typeof bootstrap !== 'undefined') {
                socialModal = new bootstrap.Modal(modalElement);
                modalElement.addEventListener('hidden.bs.modal', resetForm);
            }

            // Initialize DataTable
            initDataTable();

            // Add Button
            document.getElementById('addSocialBtn')?.addEventListener('click', () => {
                isEditMode = false;
                document.getElementById('socialModalLabel').textContent = 'Add Social Link';
                document.getElementById('socialSubmitText').textContent = 'Save';
                document.getElementById('iconRequired').style.display = 'inline';
                document.getElementById('socialIcon').setAttribute('required', 'required');
                if (socialModal) socialModal.show();
            });

            // Image Preview
            document.getElementById('socialIcon')?.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    if (file.size > 5 * 1024 * 1024) {
                        window.showToast('error', 'Image size should not exceed 5MB');
                        e.target.value = '';
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('iconPreview').src = e.target.result;
                        document.getElementById('iconPreviewContainer').style.display = 'block';
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Form Submit
            const socialForm = document.getElementById('socialForm');
            if (socialForm) {
                socialForm.addEventListener('submit', async function(e) {
                    e.preventDefault();

                    const btn = document.getElementById('socialSubmitBtn');
                    const spinner = document.getElementById('socialSpinner');
                    const text = document.getElementById('socialSubmitText');

                    clearErrors(this);
                    btn.disabled = true;
                    spinner.classList.remove('d-none');
                    text.textContent = isEditMode ? 'Updating...' : 'Saving...';

                    try {
                        const formData = new FormData(this);

                        let url;
                        if (isEditMode) {
                            const id = document.getElementById('socialId').value;
                            url = `/admin/social/update/${id}`;
                        } else {
                            url = '/admin/social/store';
                        }

                        console.log('Submitting to:', url);

                        const response = await axios.post(url, formData, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Content-Type': 'multipart/form-data'
                            }
                        });

                        console.log('Response:', response.data);

                        if (response.data.success) {
                            window.showToast('success', response.data.message);

                            // Hide modal properly
                            if (socialModal) {
                                socialModal.hide();
                            }

                            // Reload DataTable
                            if (socialTable) {
                                socialTable.ajax.reload(null, false);
                            }

                            // Reset form
                            resetForm();
                        }
                    } catch (error) {
                        console.error('Submit error:', error);
                        handleError(error, this);
                    } finally {
                        btn.disabled = false;
                        spinner.classList.add('d-none');
                        text.textContent = isEditMode ? 'Update' : 'Save';
                    }
                });
            }

            // Initialize DataTable
            function initDataTable() {
                if (!$.fn.DataTable) return;

                socialTable = $('#socialTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '/admin/social',
                        type: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        error: function(xhr, error, thrown) {
                            console.error('DataTable Error:', error, xhr.responseText);
                        }
                    },
                    columns: [{
                            data: 'DT_RowIndex',
                            name: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'name',
                            name: 'name'
                        },
                        {
                            data: 'icon',
                            name: 'icon',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'url',
                            name: 'url'
                        },
                        {
                            data: 'status',
                            name: 'status',
                            orderable: false
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false
                        }
                    ],
                    order: [
                        [1, 'asc']
                    ],
                    pageLength: 10,
                    language: {
                        emptyTable: "No social links found. Add your first link!",
                        processing: '<div class="spinner-border text-primary" role="status"></div>'
                    }
                });

                // Status Toggle with SweetAlert Confirmation
                $('#socialTable').on('change', '.status-toggle', async function(e) {
                    e.preventDefault();
                    const id = $(this).data('id');
                    const checkbox = this;
                    const isChecked = checkbox.checked;

                    // Revert the checkbox state temporarily
                    checkbox.checked = !isChecked;

                    const result = await Swal.fire({
                        title: 'Are you sure?',
                        text: 'You want to update the status?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, update it!'
                    });

                    if (result.isConfirmed) {
                        try {
                            const response = await axios.get(`/admin/social/status/${id}`, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });

                            if (response.data.success) {
                                // Update checkbox to new state
                                checkbox.checked = isChecked;
                                window.showToast('success', response.data.message);
                            } else {
                                window.showToast('error', 'Failed to update status');
                            }
                        } catch (error) {
                            console.error('Status update error:', error);
                            window.showToast('error', 'Failed to update status');
                        }
                    }
                });

                // Edit Button
                $('#socialTable').on('click', '.edit-social', function() {
                    const id = $(this).data('id');
                    const name = $(this).data('name');
                    const url = $(this).data('url');
                    const icon = $(this).data('icon');
                    const status = $(this).data('status');

                    isEditMode = true;

                    document.getElementById('socialId').value = id;
                    document.getElementById('socialName').value = name;
                    document.getElementById('socialUrl').value = url;
                    document.getElementById('socialStatus').value = status;
                    document.getElementById('iconRequired').style.display = 'none';
                    document.getElementById('socialIcon').removeAttribute('required');

                    if (icon) {
                        const iconUrl = icon.startsWith('http') ? icon : '{{ url('/') }}/' + icon;
                        document.getElementById('iconPreview').src = iconUrl;
                        document.getElementById('iconPreviewContainer').style.display = 'block';
                    }

                    document.getElementById('socialModalLabel').textContent = 'Update Social Link';
                    document.getElementById('socialSubmitText').textContent = 'Update';

                    if (socialModal) socialModal.show();
                });

                // Delete Button
                $('#socialTable').on('click', '.delete-social', async function() {
                    const id = $(this).data('id');

                    const result = await Swal.fire({
                        title: 'Are you sure?',
                        text: "You won't be able to revert this!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel'
                    });

                    if (result.isConfirmed) {
                        try {
                            const response = await axios.delete(`/admin/social/delete/${id}`, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });

                            if (response.data.success) {
                                window.showToast('success', response.data.message);

                                // Reload table
                                if (socialTable) {
                                    socialTable.ajax.reload(null, false);
                                }
                            }
                        } catch (error) {
                            console.error('Delete error:', error);
                            window.showToast('error', error.response?.data?.message || 'Failed to delete');
                        }
                    }
                });
            }

            // Helper Functions
            function resetForm() {
                const form = document.getElementById('socialForm');
                if (form) {
                    form.reset();
                    document.getElementById('socialId').value = '';
                    document.getElementById('iconPreviewContainer').style.display = 'none';
                    clearErrors(form);
                    isEditMode = false;
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
        })();
    </script>
@endpush
