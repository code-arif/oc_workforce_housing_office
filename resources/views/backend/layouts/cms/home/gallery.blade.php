<div class="row">
    <div class="col-lg-12">
        <div class="card box-shadow-0">
            <div class="card-header bg-light">
                <h4 class="card-title">Upload your properties multiple image for display</h4>
            </div>
            <div class="card-body">
                <form id="galleryUploadForm" method="post" action="{{ route('cms.gallery.section.update') }}"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="form-group mb-3">
                        <label for="gallery" class="form-label">Select Images</label>
                        <input type="file" class="form-control" name="gallery[]" id="gallery" multiple
                            accept="image/*">
                        <small class="text-muted">You can select multiple images (Max: 2MB each, Formats: JPEG, PNG,
                            WebP, AVIF)</small>
                        <div class="invalid-feedback"></div>
                    </div>

                    <!-- Image Preview Container -->
                    <div id="imagePreviewContainer" class="row mb-3" style="display: none;">
                        <div class="col-12">
                            <label class="form-label">Selected Images Preview:</label>
                        </div>
                        <div id="imagePreviewGrid" class="col-12 d-flex flex-wrap gap-3"></div>
                    </div>

                    <div class="form-group">
                        <button class="btn btn-primary" type="submit" id="submitButton" disabled>
                            <span class="spinner-border spinner-border-sm d-none" id="gallerySpinner"></span>
                            <span id="submitBtnText">Upload Images</span>
                        </button>
                    </div>
                </form>

                <!-- Uploaded Images Section -->
                <div class="mt-5" id="uploadedImagesSection">
                    <h5 class="mb-3">Uploaded Images</h5>
                    <div id="uploadedImagesGrid" class="row g-3">
                        @forelse($galleries ?? [] as $gallery)
                            <div class="col-md-3 col-sm-4 col-6 gallery-item" data-id="{{ $gallery->id }}">
                                <div class="card shadow-sm">
                                    <img src="{{ asset($gallery->image_path) }}" class="card-img-top"
                                        alt="Gallery Image" style="height: 200px; object-fit: cover;">
                                    <div class="card-body p-2">
                                        <button type="button" class="btn btn-danger btn-sm w-100 delete-image"
                                            data-id="{{ $gallery->id }}">
                                            <i class="fa fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <p class="text-muted text-center">No images uploaded yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        window.initGalleryUpload = function() {
            const form = document.getElementById('galleryUploadForm');
            if (!form) {
                console.error('Gallery form not found');
                return;
            }

            const newForm = form.cloneNode(true);
            form.parentNode.replaceChild(newForm, form);

            const galleryInput = newForm.querySelector('#gallery');
            const previewContainer = newForm.querySelector('#imagePreviewContainer');
            const previewGrid = newForm.querySelector('#imagePreviewGrid');
            const submitBtn = newForm.querySelector('#submitButton');
            const spinner = newForm.querySelector('#gallerySpinner');
            const btnText = newForm.querySelector('#submitBtnText');

            let selectedFiles = [];

            // Handle file selection
            galleryInput.addEventListener('change', function(e) {
                const files = Array.from(e.target.files);
                selectedFiles = [];
                previewGrid.innerHTML = '';

                if (files.length === 0) {
                    previewContainer.style.display = 'none';
                    submitBtn.disabled = true;
                    return;
                }

                let validFiles = [];
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/avif'];
                const maxSize = 2 * 1024 * 1024; // 2MB

                files.forEach((file, index) => {
                    // Validate file type
                    if (!validTypes.includes(file.type)) {
                        window.showToast('error', `${file.name} is not a valid image format`);
                        return;
                    }

                    // Validate file size
                    if (file.size > maxSize) {
                        window.showToast('error', `${file.name} exceeds 2MB size limit`);
                        return;
                    }

                    validFiles.push(file);

                    // Create preview
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        const previewItem = document.createElement('div');
                        previewItem.className = 'position-relative';
                        previewItem.style.width = '150px';
                        previewItem.innerHTML = `
                            <img src="${event.target.result}" class="img-thumbnail" style="width: 150px; height: 150px; object-fit: cover;">
                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 remove-preview" data-index="${index}">
                                <i class="fa fa-times"></i>
                            </button>
                            <small class="d-block text-center mt-1 text-truncate" style="max-width: 150px;">${file.name}</small>
                        `;
                        previewGrid.appendChild(previewItem);
                    };
                    reader.readAsDataURL(file);
                });

                selectedFiles = validFiles;

                if (validFiles.length > 0) {
                    previewContainer.style.display = 'block';
                    submitBtn.disabled = false;
                } else {
                    previewContainer.style.display = 'none';
                    submitBtn.disabled = true;
                    galleryInput.value = '';
                }
            });

            // Remove individual preview
            previewGrid.addEventListener('click', function(e) {
                if (e.target.closest('.remove-preview')) {
                    const index = parseInt(e.target.closest('.remove-preview').dataset.index);
                    selectedFiles.splice(index, 1);

                    // Update file input
                    const dt = new DataTransfer();
                    selectedFiles.forEach(file => dt.items.add(file));
                    galleryInput.files = dt.files;

                    // Trigger change to rebuild preview
                    galleryInput.dispatchEvent(new Event('change'));
                }
            });

            // Form submission
            newForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                e.stopPropagation();

                submitBtn.disabled = true;
                spinner.classList.remove('d-none');
                btnText.textContent = 'Uploading...';

                clearFormErrors(this);

                try {
                    const formData = new FormData();

                    // Add CSRF token
                    formData.append('_token', this.querySelector('[name="_token"]').value);

                    // Add all selected files
                    selectedFiles.forEach((file, index) => {
                        formData.append(`gallery[${index}]`, file);
                    });

                    const response = await axios.post(this.action, formData, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'multipart/form-data'
                        }
                    });

                    if (response.data.success) {
                        window.showToast('success', response.data.message);

                        // Add new images to uploaded section
                        if (response.data.images && response.data.images.length > 0) {
                            const uploadedGrid = document.getElementById('uploadedImagesGrid');

                            // Remove "no images" message if exists
                            const noImagesMsg = uploadedGrid.querySelector('.text-muted');
                            if (noImagesMsg) {
                                noImagesMsg.remove();
                            }

                            response.data.images.forEach(image => {
                                const imageItem = document.createElement('div');
                                imageItem.className =
                                    'col-md-3 col-sm-4 col-6 gallery-item';
                                imageItem.dataset.id = image.id;
                                imageItem.innerHTML = `
                                    <div class="card shadow-sm">
                                        <img src="${image.image_url}" class="card-img-top" alt="Gallery Image" style="height: 200px; object-fit: cover;">
                                        <div class="card-body p-2">
                                            <button type="button" class="btn btn-danger btn-sm w-100 delete-image" data-id="${image.id}">
                                                <i class="fa fa-trash"></i> Delete
                                            </button>
                                        </div>
                                    </div>
                                `;
                                uploadedGrid.insertBefore(imageItem, uploadedGrid
                                    .firstChild);
                            });
                        }

                        // Reset form
                        this.reset();
                        previewGrid.innerHTML = '';
                        previewContainer.style.display = 'none';
                        selectedFiles = [];
                        submitBtn.disabled = true;
                    }
                } catch (error) {
                    if (error.response?.status === 422 && error.response?.data?.errors) {
                        const errors = error.response.data.errors;
                        Object.keys(errors).forEach(field => {
                            const input = this.querySelector(`[name="${field}"]`);
                            if (input) {
                                input.classList.add('is-invalid');
                                const feedback = input.parentElement.querySelector(
                                    '.invalid-feedback');
                                if (feedback) {
                                    feedback.textContent = errors[field][0];
                                    feedback.style.display = 'block';
                                }
                            }
                        });
                        window.showToast('error', Object.values(errors).flat()[0]);
                    } else {
                        window.showToast('error', error.response?.data?.message ||
                            'Failed to upload images!');
                    }
                } finally {
                    submitBtn.disabled = false;
                    spinner.classList.add('d-none');
                    btnText.textContent = 'Upload Images';
                }

                return false;
            });

            // Handle delete image
            document.querySelectorAll('.delete-image').forEach(btn => {
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
                        const response = await axios.delete(window.route('cms.gallery.item.delete', {
                            id: id
                        }), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        showToast('success', response.data.message);
                        setTimeout(() => window.cmsManager.loadSection('gallery'), 1000);
                    } catch (error) {
                        showToast('error', error.response?.data?.message || 'Failed to delete');
                    }
                }
            }

            function clearFormErrors(form) {
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => {
                    el.textContent = '';
                    el.style.display = 'none';
                });
            }
        };

        // Auto-execute initialization
        if (typeof window.initGalleryUpload === 'function') {
            window.initGalleryUpload();
        }
    })();
</script>

<style>
    .gallery-item {
        transition: transform 0.2s;
    }

    .gallery-item:hover {
        transform: translateY(-5px);
    }

    #imagePreviewGrid {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .remove-preview {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
</style>
