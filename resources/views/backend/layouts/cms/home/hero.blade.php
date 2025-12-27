<!-- Hero Section Content -->
<div class="row">
    <div class="col-lg-12">
        <div class="card box-shadow-0">
            <div class="card-header bg-light">
                <h4 class="card-title">Hero Section - Left Content</h4>
            </div>
            <div class="card-body">
                <form id="heroSectionForm" method="post" action="{{ route('cms.home.hero.section.update') }}">
                    @csrf
                    <div class="form-group mb-3">
                        <label for="hero_title" class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" id="hero_title"
                            placeholder="Enter title" value="{{ $data->title ?? '' }}">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group mb-3">
                        <label for="hero_sub_title" class="form-label">Sub Title</label>
                        <input type="text" class="form-control" name="sub_title" id="hero_sub_title"
                            placeholder="Enter sub title" value="{{ $data->sub_title ?? '' }}">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <button class="btn btn-primary" type="submit">
                            <span class="spinner-border spinner-border-sm d-none" id="heroSpinner"></span>
                            <span id="heroSubmitText">Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Hero Section - Right Image Carousel ({{ count($sliders ?? []) }})</h3>
                <button type="button" class="btn btn-primary" id="addSliderBtn">
                    <i class="fe fe-plus me-2"></i> Add New Slider
                </button>
            </div>
            <div class="card-body">
                @if (empty($sliders) || $sliders->isEmpty())
                    <div class="text-center py-5">
                        <i class="fe fe-image" style="font-size: 48px; color: #ccc;"></i>
                        <p class="text-muted mt-3">No sliders found. Add your first slider!</p>
                    </div>
                @else
                    <div id="sortable-sliders" class="row">
                        @foreach ($sliders as $slider)
                            <div class="col-md-6 col-lg-4 mb-3 sortable-item" data-id="{{ $slider->id }}">
                                <div class="card border">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="me-2 drag-handle" style="cursor: move;">
                                                <i class="fe fe-menu" style="font-size: 20px;"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <p class="mb-0 fw-bold">{{ $slider->title ?? 'Slider #' . $slider->id }}
                                                </p>
                                                <small class="text-muted">Order: {{ $slider->order }}</small>
                                            </div>
                                        </div>

                                        <div class="mb-2">
                                            <img src="{{ asset($slider->image) }}" class="img-fluid border"
                                                style="width: 100%; height: 150px; object-fit: cover;">
                                        </div>

                                        @if ($slider->location)
                                            <p class="mb-2 text-muted small">
                                                <i class="fe fe-map-pin me-1"></i> {{ $slider->location }}
                                            </p>
                                        @endif

                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input status-toggle custom-toggle"
                                                    type="checkbox" data-id="{{ $slider->id }}"
                                                    {{ $slider->status ? 'checked' : '' }} style="margin-left:0px;">
                                            </div>

                                            <div>
                                                {{-- EDIT BUTTON - NEW --}}
                                                <button class="btn btn-sm btn-info edit-slider me-1"
                                                    data-id="{{ $slider->id }}" data-title="{{ $slider->title }}"
                                                    data-location="{{ $slider->location }}"
                                                    data-status="{{ $slider->status }}">
                                                    <i class="fe fe-edit"></i>
                                                </button>

                                                <button class="btn btn-sm btn-danger delete-slider"
                                                    data-id="{{ $slider->id }}">
                                                    <i class="fe fe-trash-2"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Add Slider Modal -->
<div class="modal fade" id="addSliderModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">Add New Slider</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="sliderForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="slider_title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" id="slider_title"
                            placeholder="Enter title" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="slider_location" class="form-label">Location</label>
                        <input type="text" class="form-control" name="location" id="slider_location"
                            placeholder="Enter Location">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Slider Image <span class="text-danger">*</span></label>
                        <input type="file" name="image" id="sliderImage" class="form-control" accept="image/*"
                            required>
                        <small class="text-muted">Recommended: 1920x1080px (Max: 2MB)</small>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3" id="imagePreview" style="display:none;">
                        <label class="form-label">Preview:</label>
                        <img id="previewImg" class="img-fluid border"
                            style="max-height: 200px; width: 100%; object-fit: cover;">
                    </div>

                    <div class="mb-3" style="margin-left: 12px">
                        <div class="form-check form-switch">
                            <input class="form-check-input custom-toggle" type="checkbox" name="status"
                                id="status" value="1" checked>
                            <label class="form-check-label" for="status"
                                style="margin-left: 22px; margin-top: 5px;">
                                Active Status
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" id="modalSpinner"></span>
                        <span id="modalSubmitText">Add Slider</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- <script>
    // Hero Section JavaScript
    function initHeroSection() {
        console.log('Hero section');
        let sliderModal = null;

        // Initialize modal
        const modalElement = document.getElementById('addSliderModal');
        if (modalElement && typeof bootstrap !== 'undefined') {
            sliderModal = new bootstrap.Modal(modalElement);
            modalElement.addEventListener('hidden.bs.modal', resetSliderForm);
        }

        // Add Slider Button
        document.getElementById('addSliderBtn')?.addEventListener('click', () => {
            if (sliderModal) sliderModal.show();
        });

        // Image Preview
        document.getElementById('sliderImage')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    showToast('error', 'Image size should not exceed 2MB');
                    e.target.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImg').src = e.target.result;
                    document.getElementById('imagePreview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        // Hero Form Submit
        document.getElementById('heroSectionForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = e.target;
            const btn = form.querySelector('button[type="submit"]');
            const spinner = document.getElementById('heroSpinner');
            const text = document.getElementById('heroSubmitText');

            btn.disabled = true;
            spinner.classList.remove('d-none');
            text.textContent = 'Saving...';

            try {
                const formData = new FormData(form);
                const response = await axios.post(form.action, formData, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.data.success) {
                    showToast('success', response.data.message);
                }
            } catch (error) {
                handleError(error, form);
            } finally {
                btn.disabled = false;
                spinner.classList.add('d-none');
                text.textContent = 'Save Changes';
            }
        });

        // Slider Form Submit
        document.getElementById('sliderForm')?.addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = e.target;
            const btn = form.querySelector('button[type="submit"]');
            const spinner = document.getElementById('modalSpinner');
            const text = document.getElementById('modalSubmitText');

            clearErrors(form);
            btn.disabled = true;
            spinner.classList.remove('d-none');
            text.textContent = 'Adding...';

            try {
                const formData = new FormData(form);
                const response = await axios.post(window.route('cms.slider.store'), formData, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.data.success) {
                    showToast('success', response.data.message);
                    if (sliderModal) sliderModal.hide();
                    setTimeout(() => window.cmsManager.loadSection('hero'), 1000);
                }
            } catch (error) {
                handleError(error, form);
            } finally {
                btn.disabled = false;
                spinner.classList.add('d-none');
                text.textContent = 'Add Slider';
            }
        });

        // Status Toggle
        document.querySelectorAll('.status-toggle').forEach(toggle => {
            toggle.addEventListener('change', async function(e) {
                const checkbox = e.target;
                const id = checkbox.dataset.id;
                const newStatus = checkbox.checked;

                checkbox.checked = !newStatus;

                const result = await Swal.fire({
                    title: 'Are you sure?',
                    text: `Do you want to ${newStatus ? 'activate' : 'deactivate'} this slider?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#521aac',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, do it!'
                });

                if (result.isConfirmed) {
                    try {
                        const response = await axios.post(window.route('cms.slider.status', {
                            id: id
                        }), {
                            status: newStatus ? 1 : 0
                        }, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        checkbox.checked = newStatus;
                        const label = document.querySelector(`.status-label-${id}`);
                        if (label) label.textContent = newStatus ? 'Active' : 'Inactive';
                        showToast('success', response.data.message);
                    } catch (error) {
                        checkbox.checked = !newStatus;
                        showToast('error', error.response?.data?.message ||
                            'Failed to update status');
                    }
                }
            });
        });

        // Delete Slider
        document.querySelectorAll('.delete-slider').forEach(btn => {
            btn.addEventListener('click', async function(e) {
                const id = e.currentTarget.dataset.id;

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
                        const response = await axios.delete(window.route('cms.slider.destroy', {
                            id: id
                        }), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        showToast('success', response.data.message);
                        setTimeout(() => window.cmsManager.loadSection('hero'), 1000);
                    } catch (error) {
                        showToast('error', error.response?.data?.message || 'Failed to delete');
                    }
                }
            });
        });

        // Sortable
        const sortableList = document.getElementById('sortable-sliders');
        if (sortableList && sortableList.children.length > 0 && typeof Sortable !== 'undefined') {
            new Sortable(sortableList, {
                animation: 150,
                handle: '.drag-handle',
                onEnd: async function() {
                    const orders = [];
                    document.querySelectorAll('.sortable-item').forEach((item, index) => {
                        orders.push({
                            id: item.dataset.id,
                            position: index + 1
                        });
                    });

                    try {
                        const response = await axios.post(window.route('cms.slider.updateOrder'), {
                            orders: orders
                        }, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        showToast('success', response.data.message);
                    } catch (error) {
                        showToast('error', 'Failed to update order');
                    }
                }
            });
        }

        // Helper Functions
        function resetSliderForm() {
            const form = document.getElementById('sliderForm');
            if (form) {
                form.reset();
                document.getElementById('imagePreview').style.display = 'none';
                clearErrors(form);
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
            let message = 'An error occurred';

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
                message = Object.values(errors).flat().join('<br>');
            } else if (error.response?.data?.message) {
                message = error.response.data.message;
            }

            showToast('error', message);
        }

        function showToast(type, message) {
            if (typeof iziToast !== 'undefined') {
                iziToast[type]({
                    title: type === 'success' ? 'Success' : 'Error',
                    message: message,
                    position: 'topRight',
                    timeout: type === 'success' ? 3000 : 5000
                });
            } else {
                alert(message);
            }
        }
    }

    // Initialize if hero tab is active
    if (document.getElementById('hero-tab')?.classList.contains('active')) {
        initHeroSection();
    }
</script> --}}

<script>
    function initHeroSection() {
        console.log('Hero section initialized');
        let sliderModal = null;
        let editMode = false;
        let editingId = null;

        // Initialize modal
        const modalElement = document.getElementById('addSliderModal');
        if (modalElement && typeof bootstrap !== 'undefined') {
            sliderModal = new bootstrap.Modal(modalElement);
            modalElement.addEventListener('hidden.bs.modal', resetSliderForm);
        }

        // Add Slider Button
        document.getElementById('addSliderBtn')?.addEventListener('click', () => {
            editMode = false;
            editingId = null;
            document.querySelector('#addSliderModal .modal-title').textContent = 'Add New Slider';
            document.getElementById('modalSubmitText').textContent = 'Add Slider';
            if (sliderModal) sliderModal.show();
        });

        // Edit Slider Buttons
        document.querySelectorAll('.edit-slider').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const title = this.dataset.title;
                const location = this.dataset.location;
                const status = this.dataset.status;

                editMode = true;
                editingId = id;

                document.getElementById('slider_title').value = title || '';
                document.getElementById('slider_location').value = location || '';
                document.getElementById('status').checked = status == 1;
                document.getElementById('sliderImage').removeAttribute('required');

                document.querySelector('#addSliderModal .modal-title').textContent = 'Update Slider';
                document.getElementById('modalSubmitText').textContent = 'Update Slider';

                if (sliderModal) sliderModal.show();
            });
        });

        // Image Preview
        document.getElementById('sliderImage')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    showToast('error', 'Image size should not exceed 2MB');
                    e.target.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('previewImg').src = e.target.result;
                    document.getElementById('imagePreview').style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });

        // Hero Form Submit
        const heroForm = document.getElementById('heroSectionForm');
        if (heroForm) {
            heroForm.removeEventListener('submit', handleHeroSubmit); // Remove old listener
            heroForm.addEventListener('submit', handleHeroSubmit);
        }

        async function handleHeroSubmit(e) {
            e.preventDefault();
            const form = e.target;
            const btn = form.querySelector('button[type="submit"]');
            const spinner = document.getElementById('heroSpinner');
            const text = document.getElementById('heroSubmitText');

            btn.disabled = true;
            spinner.classList.remove('d-none');
            text.textContent = 'Saving...';

            try {
                const formData = new FormData(form);
                const response = await axios.post(form.action, formData, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.data.success) {
                    showToast('success', response.data.message);
                }
            } catch (error) {
                handleError(error, form);
            } finally {
                btn.disabled = false;
                spinner.classList.add('d-none');
                text.textContent = 'Save Changes';
            }
        }

        // Slider Form Submit (Add/Update)
        const sliderForm = document.getElementById('sliderForm');
        if (sliderForm) {
            sliderForm.removeEventListener('submit', handleSliderSubmit);
            sliderForm.addEventListener('submit', handleSliderSubmit);
        }

        async function handleSliderSubmit(e) {
            e.preventDefault();
            const form = e.target;
            const btn = form.querySelector('button[type="submit"]');
            const spinner = document.getElementById('modalSpinner');
            const text = document.getElementById('modalSubmitText');

            clearErrors(form);
            btn.disabled = true;
            spinner.classList.remove('d-none');
            text.textContent = editMode ? 'Updating...' : 'Adding...';

            try {
                const formData = new FormData(form);

                let url = window.route('cms.slider.store');
                let method = 'POST';

                if (editMode && editingId) {
                    formData.append('_method', 'PUT');
                    url = window.route('cms.slider.update', {
                        id: editingId
                    });
                }

                const response = await axios.post(url, formData, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.data.success) {
                    showToast('success', response.data.message);
                    if (sliderModal) sliderModal.hide();
                    setTimeout(() => window.cmsManager.loadSection('hero'), 1000);
                }
            } catch (error) {
                handleError(error, form);
            } finally {
                btn.disabled = false;
                spinner.classList.add('d-none');
                text.textContent = editMode ? 'Update Slider' : 'Add Slider';
            }
        }

        // Status Toggle
        document.querySelectorAll('.status-toggle').forEach(toggle => {
            toggle.removeEventListener('change', handleStatusToggle);
            toggle.addEventListener('change', handleStatusToggle);
        });

        async function handleStatusToggle(e) {
            const checkbox = e.target;
            const id = checkbox.dataset.id;
            const newStatus = checkbox.checked;

            checkbox.checked = !newStatus;

            const result = await Swal.fire({
                title: 'Are you sure?',
                text: `Do you want to ${newStatus ? 'activate' : 'deactivate'} this slider?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#521aac',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, do it!'
            });

            if (result.isConfirmed) {
                try {
                    const response = await axios.post(window.route('cms.slider.status', {
                        id: id
                    }), {
                        status: newStatus ? 1 : 0
                    }, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    checkbox.checked = newStatus;
                    showToast('success', response.data.message);
                } catch (error) {
                    checkbox.checked = !newStatus;
                    showToast('error', error.response?.data?.message || 'Failed to update status');
                }
            }
        }

        // Delete Slider
        document.querySelectorAll('.delete-slider').forEach(btn => {
            btn.removeEventListener('click', handleDelete);
            btn.addEventListener('click', handleDelete);
        });

        async function handleDelete(e) {
            const id = e.currentTarget.dataset.id;

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
                    const response = await axios.delete(window.route('cms.slider.destroy', {
                        id: id
                    }), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    showToast('success', response.data.message);
                    setTimeout(() => window.cmsManager.loadSection('hero'), 1000);
                } catch (error) {
                    showToast('error', error.response?.data?.message || 'Failed to delete');
                }
            }
        }

        // Sortable
        const sortableList = document.getElementById('sortable-sliders');
        if (sortableList && sortableList.children.length > 0 && typeof Sortable !== 'undefined') {
            new Sortable(sortableList, {
                animation: 150,
                handle: '.drag-handle',
                onEnd: async function() {
                    const orders = [];
                    document.querySelectorAll('.sortable-item').forEach((item, index) => {
                        orders.push({
                            id: item.dataset.id,
                            position: index + 1
                        });
                    });

                    try {
                        const response = await axios.post(window.route('cms.slider.updateOrder'), {
                            orders: orders
                        }, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        showToast('success', response.data.message);
                    } catch (error) {
                        showToast('error', 'Failed to update order');
                    }
                }
            });
        }

        // Helper Functions
        function resetSliderForm() {
            const form = document.getElementById('sliderForm');
            if (form) {
                form.reset();
                document.getElementById('imagePreview').style.display = 'none';
                document.getElementById('sliderImage').setAttribute('required', 'required');
                clearErrors(form);
                editMode = false;
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
            let message = 'An error occurred';

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
                message = Object.values(errors).flat().join('<br>');
            } else if (error.response?.data?.message) {
                message = error.response.data.message;
            }

            showToast('error', message);
        }
    }

    // Auto-initialize if on hero section
    if (document.getElementById('hero-tab')?.classList.contains('active')) {
        initHeroSection();
    }
</script>


<style>
    .custom-toggle {
        width: 40px !important;
        height: 20px !important;
        cursor: pointer;
    }

    .custom-toggle:checked {
        background-color: #b18802 !important;
        border-color: #D9A600 !important;
    }

    .custom-toggle:focus {
        box-shadow: 0 0 0 0.25rem rgba(82, 26, 172, 0.25) !important;
        border-color: #D9A600 !important;
    }
</style>
