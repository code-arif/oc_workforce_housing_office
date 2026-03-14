<div class="row">
    <div class="col-lg-12">
        <div class="card box-shadow-0">
            <div class="card-header bg-light">
                <h4 class="card-title">Property Page - Property Two</h4>
            </div>
            <div class="card-body">
                <form id="propertyTwoForm" method="post" action="{{ route('cms.property.two.update') }}"
                    enctype="multipart/form-data">
                    @csrf

                    {{-- Title --}}
                    <div class="form-group mb-3">
                        <label for="property_title" class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" id="property_title"
                            placeholder="Enter title" value="{{ $data->title ?? '' }}">
                        <div class="invalid-feedback"></div>
                    </div>

                    {{-- Description --}}
                    <div class="form-group mb-3">
                        <label for="property_description" class="form-label">Description</label>
                        <textarea class="form-control summernote" name="description" id="property_description" placeholder="Enter Description">{{ $data->description ?? '' }}</textarea>
                        <div class="invalid-feedback"></div>
                    </div>

                    {{-- Primary Image --}}
                    <div class="form-group mb-3">
                        <label for="propertyTwoImage" class="form-label">Primary Image</label>
                        <input type="file" class="dropify form-control"
                            data-default-file="{{ !empty($data->image) && file_exists(public_path($data->image)) ? asset($data->image) : asset('default/placeholder-image.avif') }}"
                            name="image" id="propertyTwoImage" accept="image/*">
                        <small class="text-muted">Recommended: 1920x1080px (Max: 2MB)</small>
                        <div class="invalid-feedback"></div>
                    </div>

                    {{-- Multiple Gallery Images --}}
                    <div class="form-group mb-3">
                        <label class="form-label">
                            Gallery Images <small class="text-muted">(Multiple)</small>
                        </label>

                        @php
                            $galleryImages = [];
                            if (!empty($data->metadata)) {
                                $meta = is_array($data->metadata)
                                    ? $data->metadata
                                    : json_decode($data->metadata, true);
                                $galleryImages = $meta['images'] ?? [];
                            }
                        @endphp

                        {{-- Existing gallery preview --}}
                        @if (!empty($galleryImages))
                            <div class="d-flex flex-wrap gap-2 mb-2" id="propertyTwoGalleryPreview">
                                @foreach ($galleryImages as $img)
                                    <div class="position-relative">
                                        <img src="{{ asset($img) }}" alt="Gallery"
                                            style="width:100px;height:75px;object-fit:cover;border-radius:6px;border:1px solid #ddd;">
                                    </div>
                                @endforeach
                            </div>
                            <small class="text-warning d-block mb-1">
                                Uploading new images will replace all existing gallery images.
                            </small>
                        @endif

                        <input type="file" class="form-control" name="images[]" id="propertyTwoImages"
                            accept="image/*" multiple>
                        <small class="text-muted">Select multiple Max 2MB each. (JPEG, PNG, WebP, AVIF)</small>
                        <div class="invalid-feedback"></div>

                        {{-- New images preview --}}
                        <div class="d-flex flex-wrap gap-2 mt-2" id="propertyTwoNewImagesPreview"></div>
                    </div>

                    {{-- Video Upload --}}
                    <div class="form-group mb-3">
                        <label class="form-label">Property Video</label>

                        @php
                            $existingVideo = null;
                            if (!empty($data->metadata)) {
                                $meta = is_array($data->metadata)
                                    ? $data->metadata
                                    : json_decode($data->metadata, true);
                                $existingVideo = $meta['video'] ?? null;
                            }
                        @endphp

                        @if ($existingVideo && file_exists(public_path($existingVideo)))
                            <div class="mb-2" id="propertyTwoVideoPreview">
                                <video controls style="max-width:100%;max-height:200px;border-radius:6px;">
                                    <source src="{{ asset($existingVideo) }}">
                                </video>
                                <small class="text-warning d-block mt-1">
                                    Uploading a new video will replace the existing one.
                                </small>
                            </div>
                        @endif

                        <input type="file" class="form-control" name="video" id="propertyTwoVideo"
                            accept="video/mp4,video/webm,video/ogg,video/quicktime">
                        <small class="text-muted">Accepted: MP4, WebM, OGG, MOV (Max: 50MB)</small>
                        <div class="invalid-feedback"></div>

                        {{-- New video preview --}}
                        <div class="mt-2" id="propertyTwoNewVideoPreview"></div>
                    </div>

                    <div class="form-group">
                        <button class="btn btn-primary" type="submit" id="propertyTwoSubmitButton">
                            <span class="spinner-border spinner-border-sm d-none" id="propertyTwoSpinner"></span>
                            <span id="propertyTwoSubmitBtnText">Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        window.initPropertyTwoSection = function() {
            const form = document.getElementById('propertyTwoForm');
            if (!form) {
                console.error('Form not found');
                return;
            }

            // Clone and replace to remove all old event listeners
            const newForm = form.cloneNode(true);
            form.parentNode.replaceChild(newForm, form);

            // ── Summernote ────────────────────────────────────────────────
            if (typeof $.fn.summernote !== 'undefined') {
                $(newForm).find('.summernote').summernote({
                    placeholder: 'Your Content Here...',
                    tabsize: 2,
                    height: 150,
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'underline', 'clear']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['table', ['table']],
                        ['insert', ['link']],
                        ['view', ['fullscreen', 'codeview', 'help']]
                    ]
                });
            }

            // ── Dropify ───────────────────────────────────────────────────
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

            // ── Primary image validation ──────────────────────────────────
            const imageInput = newForm.querySelector('#propertyTwoImage');
            if (imageInput) {
                imageInput.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp',
                        'image/avif'
                    ];

                    if (file.size > 2 * 1024 * 1024) {
                        window.showToast('error', 'Image size should not exceed 2MB');
                        e.target.value = '';
                        resetDropify(e.target);
                        return;
                    }
                    if (!validTypes.includes(file.type)) {
                        window.showToast('error',
                            'Please upload a valid image file (JPEG, PNG, WebP, AVIF)');
                        e.target.value = '';
                        resetDropify(e.target);
                    }
                });
            }

            // ── Multiple images preview & validation ──────────────────────
            const imagesInput = newForm.querySelector('#propertyTwoImages');
            const imagesPreview = newForm.querySelector('#propertyTwoNewImagesPreview');

            if (imagesInput) {
                imagesInput.addEventListener('change', function() {
                    imagesPreview.innerHTML = '';
                    const maxSize = 2 * 1024 * 1024;
                    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp',
                        'image/avif'
                    ];
                    let hasError = false;

                    Array.from(this.files).forEach(file => {
                        if (file.size > maxSize) {
                            window.showToast('error', `"${file.name}" exceeds 2MB limit.`);
                            hasError = true;
                            return;
                        }
                        if (!validTypes.includes(file.type)) {
                            window.showToast('error',
                                `"${file.name}" is not a valid image type.`);
                            hasError = true;
                            return;
                        }

                        const reader = new FileReader();
                        reader.onload = e => {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.style.cssText =
                                'width:100px;height:75px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;';
                            imagesPreview.appendChild(img);
                        };
                        reader.readAsDataURL(file);
                    });

                    if (hasError) this.value = '';
                });
            }

            // ── Video preview & validation ────────────────────────────────
            const videoInput = newForm.querySelector('#propertyTwoVideo');
            const videoPreviewBox = newForm.querySelector('#propertyTwoNewVideoPreview');

            if (videoInput) {
                videoInput.addEventListener('change', function() {
                    videoPreviewBox.innerHTML = '';
                    const file = this.files[0];
                    if (!file) return;

                    const maxSize = 50 * 1024 * 1024;
                    const validTypes = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'];

                    if (file.size > maxSize) {
                        window.showToast('error', 'Video size should not exceed 50MB.');
                        this.value = '';
                        return;
                    }
                    if (!validTypes.includes(file.type)) {
                        window.showToast('error', 'Please upload a valid video (MP4, WebM, OGG, MOV).');
                        this.value = '';
                        return;
                    }

                    const url = URL.createObjectURL(file);
                    videoPreviewBox.innerHTML = `
                    <video controls style="max-width:100%;max-height:200px;border-radius:6px;">
                        <source src="${url}">
                    </video>
                    <small class="text-success d-block mt-1">New video selected: ${file.name}</small>
                `;
                });
            }

            // ── Form submit ───────────────────────────────────────────────
            newForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                e.stopPropagation();

                const submitBtn = this.querySelector('#propertyTwoSubmitButton');
                const spinner = this.querySelector('#propertyTwoSpinner');
                const btnText = this.querySelector('#propertyTwoSubmitBtnText');

                submitBtn.disabled = true;
                spinner.classList.remove('d-none');
                btnText.textContent = 'Saving...';
                clearFormErrors(this);

                try {
                    const formData = new FormData(this);

                    // Summernote content
                    formData.set('description', $(this).find('.summernote').summernote('code'));

                    const response = await axios.post(this.action, formData, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'multipart/form-data'
                        }
                    });

                    if (response.data.success) {
                        window.showToast('success', response.data.message ||
                            'Updated successfully!');

                        // Clear previews after successful save
                        if (imagesPreview) imagesPreview.innerHTML = '';
                        if (videoPreviewBox) videoPreviewBox.innerHTML = '';

                    } else {
                        window.showToast('error', response.data.message || 'Failed to update!');
                    }

                } catch (error) {
                    if (error.response?.status === 422 && error.response?.data?.errors) {
                        const errors = error.response.data.errors;
                        Object.keys(errors).forEach(field => {
                            const input = this.querySelector(`[name="${field}"]`);
                            if (input) {
                                input.classList.add('is-invalid');
                                const fb = input.parentElement.querySelector(
                                    '.invalid-feedback');
                                if (fb) {
                                    fb.textContent = errors[field][0];
                                    fb.style.display = 'block';
                                }
                            }
                        });
                        window.showToast('error', Object.values(errors).flat()[0]);
                    } else {
                        window.showToast('error', error.response?.data?.message ||
                            'Something went wrong!');
                    }
                } finally {
                    submitBtn.disabled = false;
                    spinner.classList.add('d-none');
                    btnText.textContent = 'Save Changes';
                }

                return false;
            });

            // ── Helpers ───────────────────────────────────────────────────
            function resetDropify(el) {
                if (typeof $.fn.dropify !== 'undefined') {
                    const d = $(el).data('dropify');
                    if (d) {
                        d.resetPreview();
                        d.clearElement();
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

        // Auto-execute
        if (typeof window.initPropertyTwoSection === 'function') {
            window.initPropertyTwoSection();
        }
    })();
</script>

<style>
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

    .note-editor.note-frame {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }

    .note-editor.note-frame .note-statusbar {
        background-color: #f8f9fa;
    }
</style>
