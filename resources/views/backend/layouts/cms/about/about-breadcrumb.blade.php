<div class="row">
    <div class="col-lg-12">
        <div class="card box-shadow-0">
            <div class="card-header bg-light">
                <h4 class="card-title">About Us Page - Header Section</h4>
            </div>
            <div class="card-body">
                <form id="apartmentForm" method="post" action="{{ route('cms.about/breadcrumb/update') }}"
                    enctype="multipart/form-data">
                    @csrf

                    {{-- Title --}}
                    <div class="form-group mb-3">
                        <label for="about-us-breadcrumb_title" class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" id="about-us-breadcrumb_title"
                            placeholder="Enter title" value="{{ $data->title ?? '' }}">
                        <div class="invalid-feedback"></div>
                    </div>

                    {{-- Description --}}
                    <div class="form-group mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" id="summernote" class="summernote form-control @error('description') is-invalid @enderror"
                            rows="6" placeholder="Enter description">{{ $data->description ?? old('description') }}</textarea>
                        @error('description')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Image --}}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label for="image" class="form-label">Image</label>
                                <input type="file" class="dropify form-control"
                                    data-default-file="{{ !empty($data->image) && file_exists(public_path($data->image)) ? asset($data->image) : asset('default/placeholder-image.avif') }}"
                                    name="image" id="image" accept="image/*">
                                <small class="text-muted">Recommended: 1920x1080px (Max: 2MB)</small>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button class="btn btn-primary" type="submit" id="submitButton">
                            <span class="spinner-border spinner-border-sm d-none" id="apartmentSpinner"></span>
                            <span id="submitBtnText">Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        // Define initialization function
        window.initApartmentSection = function() {

            const form = document.getElementById('apartmentForm');
            if (!form) {
                console.error('Form not found');
                return;
            }

            // Clone and replace to remove all old event listeners
            const newForm = form.cloneNode(true);
            form.parentNode.replaceChild(newForm, form);

            // Re-initialize Dropify on new form
            if (typeof $.fn.dropify !== 'undefined') {
                $(newForm).find('.dropify').dropify({
                    messages: {
                        'default': 'Drag and drop a file here or click',
                        'replace': 'Drag and drop or click to replace',
                        'remove': 'Remove',
                        'error': 'Sorry, the file is too large'
                    }
                });
            }

            // Add submit event listener
            newForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                e.stopPropagation();

                const submitBtn = this.querySelector('#submitButton');
                const spinner = this.querySelector('#apartmentSpinner');
                const btnText = this.querySelector('#submitBtnText');

                // Disable button and show loading
                submitBtn.disabled = true;
                spinner.classList.remove('d-none');
                btnText.textContent = 'Saving...';

                // Clear previous errors
                clearFormErrors(this);

                try {
                    const formData = new FormData(this);

                    // Log form data for debugging
                    console.log('Form Data:');
                    for (let [key, value] of formData.entries()) {
                        console.log(key, value);
                    }

                    const response = await axios.post(this.action, formData, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'multipart/form-data'
                        }
                    });

                    if (response.data.success) {
                        window.showToast('success', response.data.message ||
                            'Updated successfully!');

                        // Update dropify preview if new image was uploaded
                        if (response.data.image) {
                            const dropifyWrapper = this.querySelector('.dropify-wrapper');
                            if (dropifyWrapper) {
                                const dropifyPreview = dropifyWrapper.querySelector(
                                    '.dropify-preview');
                                if (dropifyPreview) {
                                    const imgElement = dropifyPreview.querySelector('img');
                                    if (imgElement) {
                                        imgElement.src = window.assetUrl(response.data.image);
                                    }
                                }
                            }
                        }
                    } else {
                        window.showToast('error', response.data.message || 'Failed to update!');
                    }
                } catch (error) {

                    if (error.response?.status === 422 && error.response?.data?.errors) {
                        // Validation errors
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
                            'Something went wrong!');
                    }
                } finally {
                    // Re-enable button
                    submitBtn.disabled = false;
                    spinner.classList.add('d-none');
                    btnText.textContent = 'Save Changes';
                }

                return false;
            });

            // Image file validation
            const imageInput = newForm.querySelector('#image');
            if (imageInput) {
                imageInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        // Check file size (2MB max)
                        if (file.size > 2 * 1024 * 1024) {
                            window.showToast('error', 'Image size should not exceed 2MB');
                            e.target.value = '';

                            // Reset dropify
                            if (typeof $.fn.dropify !== 'undefined') {
                                const dropify = $(e.target).data('dropify');
                                if (dropify) {
                                    dropify.resetPreview();
                                    dropify.clearElement();
                                }
                            }
                            return;
                        }

                        // Check file type
                        const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp',
                            'image/avif'
                        ];
                        if (!validTypes.includes(file.type)) {
                            window.showToast('error',
                                'Please upload a valid image file (JPEG, PNG, WebP, AVIF)');
                            e.target.value = '';

                            // Reset dropify
                            if (typeof $.fn.dropify !== 'undefined') {
                                const dropify = $(e.target).data('dropify');
                                if (dropify) {
                                    dropify.resetPreview();
                                    dropify.clearElement();
                                }
                            }
                            return;
                        }
                    }
                });
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
        if (typeof window.initApartmentSection === 'function') {
            window.initApartmentSection();
        }
    })();
</script>

<style>
    /* Dropify custom styles */
    .dropify-wrapper {
        border: 2px dashed #D9A600;
        border-radius: 0.375rem;
    }

    .dropify-wrapper:hover {
        border-color: #D9A600;
    }

    .dropify-message p {
        font-size: 14px;
        color: #6b7280;
    }

    .dropify-preview {
        background-color: #f9fafb;
    }
</style>
