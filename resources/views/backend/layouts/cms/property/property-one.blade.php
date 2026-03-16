<div class="row">
    <div class="col-lg-12">
        <div class="card box-shadow-0">
            <div class="card-header bg-light">
                <h4 class="card-title">Property Page - Property One</h4>
            </div>
            <div class="card-body">
                <form id="propertyOneForm" method="post" action="{{ route('cms.property.one.update') }}"
                    enctype="multipart/form-data">
                    @csrf

                    {{-- Title --}}
                    <div class="form-group mb-3">
                        <label class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" placeholder="Enter title"
                            value="{{ $data->title ?? '' }}">
                        <div class="invalid-feedback"></div>
                    </div>

                    {{-- Description --}}
                    <div class="form-group mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control summernote" name="description" placeholder="Enter Description">{{ $data->description ?? '' }}</textarea>
                        <div class="invalid-feedback"></div>
                    </div>

                    {{-- Primary Image --}}
                    {{-- <div class="form-group mb-3">
                        <label class="form-label">Primary Image</label>
                        <input type="file" class="dropify form-control"
                            data-default-file="{{ !empty($data->image) && file_exists(public_path($data->image)) ? asset($data->image) : asset('default/placeholder-image.avif') }}"
                            name="image" id="propertyOneImage" accept="image/*">
                        <small class="text-muted">Recommended: 1920x1080px (Max: 2MB)</small>
                        <div class="invalid-feedback"></div>
                    </div> --}}

                    {{-- Multiple Gallery Images --}}
                    <div class="form-group mb-3">
                        <label class="form-label fw-semibold">Gallery Images <small
                                class="text-muted">(Multiple)</small></label>

                        {{-- Existing gallery images preview --}}
                        @php
                            $galleryImages = $data->metadata['images'] ?? [];
                        @endphp

                        <div class="d-flex flex-wrap gap-2 mb-2" id="propertyOneGalleryPreview">
                            @foreach ($galleryImages as $index => $img)
                                <div class="img-thumb-wrapper position-relative" data-path="{{ $img }}">
                                    <img src="{{ asset($img) }}" alt="Gallery"
                                        style="width:100px;height:75px;object-fit:cover;border-radius:6px;border:1px solid #ddd;">
                                    <button type="button" class="img-remove-btn" title="Remove">&#10005;</button>
                                    <input type="hidden" name="existing_images[]" value="{{ $img }}">
                                </div>
                            @endforeach
                        </div>

                        {{-- Custom File Drop Zone --}}
                        <div class="custom-upload-zone" id="propertyOneDropZone">
                            <div class="upload-icon">🖼️</div>
                            <p class="mb-1">Drag & Drop images here or <span class="upload-browse-text">Browse</span>
                            </p>
                            <small class="text-muted">JPEG, PNG, WebP, AVIF, HEIC, HEIF and all formats · Max 5MB
                                each</small>
                            <input type="file" id="propertyOneImages" name="images[]" accept="image/*" multiple
                                style="display:none;">
                        </div>

                        <div class="invalid-feedback" id="propertyOneImagesError"></div>

                        {{-- New images preview --}}
                        <div class="d-flex flex-wrap gap-2 mt-2" id="propertyOneNewImagesPreview"></div>
                    </div>

                    {{-- Video Upload --}}
                    {{-- Video Upload --}}
                    <div class="form-group mb-3">
                        <label class="form-label fw-semibold">Property Video</label>

                        @php $existingVideo = $data->metadata['video'] ?? null; @endphp

                        <div id="propertyOneVideoPreview"
                            class="{{ $existingVideo && file_exists(public_path($existingVideo)) ? '' : 'd-none' }}">
                            <div class="video-preview-wrapper position-relative d-inline-block mt-1">
                                @if ($existingVideo && file_exists(public_path($existingVideo)))
                                    <video controls
                                        style="max-width:100%;max-height:200px;border-radius:8px;display:block;">
                                        <source src="{{ asset($existingVideo) }}">
                                    </video>
                                    <input type="hidden" name="existing_video" value="{{ $existingVideo }}"
                                        id="existingVideoPath">
                                    <button type="button" class="video-remove-btn" id="propertyOneRemoveExistingVideo"
                                        title="Remove Video">&#10005;</button>
                                @endif
                            </div>
                            <small class="text-warning d-block mt-1">Uploading a new video will replace the existing
                                one.</small>
                        </div>

                        {{-- Custom Video Drop Zone --}}
                        <div class="custom-upload-zone mt-2" id="propertyOneVideoDropZone">
                            <div class="upload-icon">🎬</div>
                            <p class="mb-1">Drag & Drop video here or <span class="upload-browse-text">Browse</span>
                            </p>
                            <small class="text-muted">MP4, WebM, OGG, MOV · Max 50MB</small>
                            <input type="file" id="propertyOneVideo" name="video"
                                accept="video/mp4,video/webm,video/ogg,video/quicktime,.mov" style="display:none;">
                        </div>

                        <div class="invalid-feedback" id="propertyOneVideoError"></div>

                        {{-- New video preview --}}
                        <div class="mt-2" id="propertyOneNewVideoPreview"></div>
                    </div>

                    <div class="form-group">
                        <button class="btn btn-primary" type="submit" id="propertyOneSubmitButton">
                            <span class="spinner-border spinner-border-sm d-none" id="propertyOneSpinner"></span>
                            <span id="propertyOneSubmitBtnText">Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        window.initPropertyOneSection = function() {
            const form = document.getElementById('propertyOneForm');
            if (!form) return;

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

            // ── Track new image files (DataTransfer trick) ────────────────
            let newImageFiles = new DataTransfer();

            // ── Image Drop Zone ───────────────────────────────────────────
            const imageDropZone = newForm.querySelector('#propertyOneDropZone');
            const imagesInput = newForm.querySelector('#propertyOneImages');
            const imagesPreview = newForm.querySelector('#propertyOneNewImagesPreview');
            const galleryPreview = newForm.querySelector('#propertyOneGalleryPreview');

            if (imageDropZone && imagesInput) {
                imageDropZone.addEventListener('click', () => imagesInput.click());

                ['dragenter', 'dragover'].forEach(ev =>
                    imageDropZone.addEventListener(ev, e => {
                        e.preventDefault();
                        imageDropZone.classList.add('dragover');
                    }));
                ['dragleave', 'drop'].forEach(ev =>
                    imageDropZone.addEventListener(ev, e => {
                        e.preventDefault();
                        imageDropZone.classList.remove('dragover');
                    }));

                imageDropZone.addEventListener('drop', e => {
                    handleNewImages(e.dataTransfer.files);
                });

                imagesInput.addEventListener('change', function() {
                    handleNewImages(this.files);
                    this.value = ''; // reset input — actual files are in newImageFiles DataTransfer
                });
            }

            function handleNewImages(files) {
                const maxSize = 5 * 1024 * 1024;

                Array.from(files).forEach(file => {
                    const isImage = file.type.startsWith('image/') || file.type === '';
                    if (!isImage) {
                        window.showToast('error', `"${file.name}" is not a valid image.`);
                        return;
                    }
                    if (file.size > maxSize) {
                        window.showToast('error', `"${file.name}" exceeds 5MB limit.`);
                        return;
                    }
                    newImageFiles.items.add(file);
                    addNewImageThumb(file, newImageFiles.files.length - 1);
                });
            }

            function addNewImageThumb(file, idx) {
                const wrapper = document.createElement('div');
                wrapper.classList.add('img-thumb-wrapper', 'position-relative');
                wrapper.dataset.newIdx = idx;

                const img = document.createElement('img');
                img.alt = file.name;
                img.style.cssText = 'width:100%;height:100%;object-fit:cover;';

                if (file.type === 'image/heic' || file.type === 'image/heif' || file.type === '') {
                    img.src = '';
                    const label = document.createElement('small');
                    label.textContent = file.name;
                    label.style.cssText =
                        'font-size:9px;position:absolute;bottom:2px;left:2px;color:#fff;text-shadow:0 0 3px #000;max-width:96px;overflow:hidden;white-space:nowrap;';
                    wrapper.appendChild(label);
                } else {
                    const reader = new FileReader();
                    reader.onload = e => {
                        img.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'img-remove-btn';
                btn.innerHTML = '&#10005;';
                btn.title = 'Remove';
                btn.addEventListener('click', () => removeNewImage(parseInt(wrapper.dataset.newIdx)));

                wrapper.appendChild(img);
                wrapper.appendChild(btn);
                imagesPreview.appendChild(wrapper);
            }

            function removeNewImage(removeIdx) {
                const newDT = new DataTransfer();
                Array.from(newImageFiles.files).forEach((f, i) => {
                    if (i !== removeIdx) newDT.items.add(f);
                });
                newImageFiles = newDT;

                // Re-render preview with corrected indices
                imagesPreview.innerHTML = '';
                Array.from(newImageFiles.files).forEach((f, i) => addNewImageThumb(f, i));
            }

            // ── Existing images remove ────────────────────────────────────
            if (galleryPreview) {
                galleryPreview.querySelectorAll('.img-remove-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const wrapper = this.closest('.img-thumb-wrapper');
                        // Add hidden input to track removed existing images
                        const removedInput = document.createElement('input');
                        removedInput.type = 'hidden';
                        removedInput.name = 'removed_images[]';
                        removedInput.value = wrapper.dataset.path;
                        newForm.appendChild(removedInput);

                        // Remove the hidden existing_images[] input for this image
                        const existingInput = wrapper.querySelector(
                            'input[name="existing_images[]"]');
                        if (existingInput) existingInput.disabled = true;

                        wrapper.remove();
                    });
                });
            }

            // ── Video Drop Zone ───────────────────────────────────────────
            const videoDropZone = newForm.querySelector('#propertyOneVideoDropZone');
            const videoInput = newForm.querySelector('#propertyOneVideo');
            const videoPreviewBox = newForm.querySelector('#propertyOneNewVideoPreview');
            const existingVideoWrap = newForm.querySelector('#propertyOneVideoPreview');
            const removeExistingBtn = newForm.querySelector('#propertyOneRemoveExistingVideo');
            const existingVideoPath = newForm.querySelector('#existingVideoPath');

            if (videoDropZone && videoInput) {
                videoDropZone.addEventListener('click', () => videoInput.click());

                ['dragenter', 'dragover'].forEach(ev =>
                    videoDropZone.addEventListener(ev, e => {
                        e.preventDefault();
                        videoDropZone.classList.add('dragover');
                    }));
                ['dragleave', 'drop'].forEach(ev =>
                    videoDropZone.addEventListener(ev, e => {
                        e.preventDefault();
                        videoDropZone.classList.remove('dragover');
                    }));
                videoDropZone.addEventListener('drop', e => {
                    const files = e.dataTransfer.files;
                    if (files.length) handleVideoFile(files[0]);
                });

                videoInput.addEventListener('change', function() {
                    if (this.files[0]) handleVideoFile(this.files[0]);
                });
            }

            function handleVideoFile(file) {
                const maxSize = 50 * 1024 * 1024;
                const validTypes = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'];

                if (file.size > maxSize) {
                    window.showToast('error', 'Video size should not exceed 50MB.');
                    videoInput.value = '';
                    return;
                }
                if (!validTypes.includes(file.type)) {
                    window.showToast('error', 'Please upload a valid video (MP4, WebM, OGG, MOV).');
                    videoInput.value = '';
                    return;
                }

                const url = URL.createObjectURL(file);
                videoPreviewBox.innerHTML = `
                <div class="video-preview-wrapper position-relative d-inline-block">
                    <video controls style="max-width:100%;max-height:200px;border-radius:8px;display:block;">
                        <source src="${url}" type="${file.type}">
                    </video>
                    <button type="button" class="video-remove-btn" id="removeNewVideo" title="Remove">&#10005;</button>
                </div>
                <small class="text-success d-block mt-1">Selected: ${file.name}</small>
            `;

                videoPreviewBox.querySelector('#removeNewVideo')?.addEventListener('click', () => {
                    videoInput.value = '';
                    videoPreviewBox.innerHTML = '';
                });
            }

            // Remove existing video button
            if (removeExistingBtn) {
                removeExistingBtn.addEventListener('click', () => {
                    // Mark existing video for removal
                    const removeVideoInput = document.createElement('input');
                    removeVideoInput.type = 'hidden';
                    removeVideoInput.name = 'remove_video';
                    removeVideoInput.value = '1';
                    newForm.appendChild(removeVideoInput);

                    if (existingVideoPath) existingVideoPath.disabled = true;
                    if (existingVideoWrap) existingVideoWrap.classList.add('d-none');
                });
            }

            // ── Form Submit ───────────────────────────────────────────────
            newForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                e.stopPropagation();

                const submitBtn = this.querySelector('#propertyOneSubmitButton');
                const spinner = this.querySelector('#propertyOneSpinner');
                const btnText = this.querySelector('#propertyOneSubmitBtnText');

                submitBtn.disabled = true;
                spinner.classList.remove('d-none');
                btnText.textContent = 'Saving...';
                clearFormErrors(this);

                try {
                    const formData = new FormData(this);
                    formData.set('description', $(this).find('.summernote').summernote('code'));

                    formData.delete('images[]');
                    Array.from(newImageFiles.files).forEach(file => {
                        formData.append('images[]', file);
                    });

                    const response = await axios.post(this.action, formData, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'multipart/form-data'
                        }
                    });

                    if (response.data.success) {
                        window.showToast('success', response.data.message ||
                            'Updated successfully!');
                        if (imagesPreview) imagesPreview.innerHTML = '';
                        if (videoPreviewBox) videoPreviewBox.innerHTML = '';
                        newImageFiles = new DataTransfer();
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
            });

            function clearFormErrors(form) {
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => {
                    el.textContent = '';
                    el.style.display = 'none';
                });
            }
        };

        if (typeof window.initPropertyOneSection === 'function') {
            window.initPropertyOneSection();
        }
    })();
</script>

@include('backend.layouts.cms.property._style')
