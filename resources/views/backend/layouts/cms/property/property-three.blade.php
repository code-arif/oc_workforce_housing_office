<div class="row">
    <div class="col-lg-12">
        <div class="card box-shadow-0">
            <div class="card-header bg-light">
                <h4 class="card-title">Property Page - Property Three</h4>
            </div>
            <div class="card-body">
                <form id="propertyThreeForm" method="post" action="{{ route('cms.property.three.update') }}"
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

                    {{-- Multiple Gallery Images --}}
                    <div class="form-group mb-3">
                        <label class="form-label fw-semibold">Gallery Images <small
                                class="text-muted">(Multiple)</small></label>

                        @php
                            $galleryImages = [];
                            if (!empty($data->metadata)) {
                                $meta = is_array($data->metadata)
                                    ? $data->metadata
                                    : json_decode($data->metadata, true);
                                $galleryImages = $meta['images'] ?? [];
                            }
                        @endphp

                        <div class="d-flex flex-wrap gap-2 mb-2" id="propertyThreeGalleryPreview">
                            @foreach ($galleryImages as $img)
                                <div class="img-thumb-wrapper position-relative" data-path="{{ $img }}">
                                    <img src="{{ asset($img) }}" alt="Gallery"
                                        style="width:100%;height:100%;object-fit:cover;">
                                    <button type="button" class="img-remove-btn" title="Remove">&#10005;</button>
                                    <input type="hidden" name="existing_images[]" value="{{ $img }}">
                                </div>
                            @endforeach
                        </div>

                        <div class="custom-upload-zone" id="propertyThreeDropZone">
                            <div class="upload-icon">🖼️</div>
                            <p class="mb-1">Drag & Drop images here or <span class="upload-browse-text">Browse</span>
                            </p>
                            <small class="text-muted">JPEG, PNG, WebP, AVIF, HEIC, HEIF and all formats · Max 5MB
                                each</small>
                            <input type="file" id="propertyThreeImages" name="images[]" accept="image/*" multiple
                                style="display:none;">
                        </div>

                        <div class="invalid-feedback" id="propertyThreeImagesError"></div>
                        <div class="d-flex flex-wrap gap-2 mt-2" id="propertyThreeNewImagesPreview"></div>
                    </div>

                    {{-- Video Upload --}}
                    <div class="form-group mb-3">
                        <label class="form-label fw-semibold">Property Video</label>

                        @php
                            $existingVideo = null;
                            if (!empty($data->metadata)) {
                                $meta = is_array($data->metadata)
                                    ? $data->metadata
                                    : json_decode($data->metadata, true);
                                $existingVideo = $meta['video'] ?? null;
                            }
                        @endphp

                        <div id="propertyThreeVideoPreview"
                            class="{{ $existingVideo && file_exists(public_path($existingVideo)) ? '' : 'd-none' }}">
                            <div class="video-preview-wrapper position-relative d-inline-block mt-1">
                                @if ($existingVideo && file_exists(public_path($existingVideo)))
                                    <video controls
                                        style="max-width:100%;max-height:200px;border-radius:8px;display:block;">
                                        <source src="{{ asset($existingVideo) }}">
                                    </video>
                                    <input type="hidden" name="existing_video" value="{{ $existingVideo }}"
                                        id="propertyThreeExistingVideoPath">
                                    <button type="button" class="video-remove-btn"
                                        id="propertyThreeRemoveExistingVideo" title="Remove Video">&#10005;</button>
                                @endif
                            </div>
                            <small class="text-warning d-block mt-1">Uploading a new video will replace the existing
                                one.</small>
                        </div>

                        <div class="custom-upload-zone mt-2" id="propertyThreeVideoDropZone">
                            <div class="upload-icon">🎬</div>
                            <p class="mb-1">Drag & Drop video here or <span class="upload-browse-text">Browse</span>
                            </p>
                            <small class="text-muted">MP4, WebM, OGG, MOV · Max 50MB</small>
                            <input type="file" id="propertyThreeVideo" name="video"
                                accept="video/mp4,video/webm,video/ogg,video/quicktime,.mov" style="display:none;">
                        </div>

                        <div class="invalid-feedback" id="propertyThreeVideoError"></div>
                        <div class="mt-2" id="propertyThreeNewVideoPreview"></div>
                    </div>

                    <div class="form-group">
                        <button class="btn btn-primary" type="submit" id="propertyThreeSubmitButton">
                            <span class="spinner-border spinner-border-sm d-none" id="propertyThreeSpinner"></span>
                            <span id="propertyThreeSubmitBtnText">Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        window.initPropertyThreeSection = function() {
            const form = document.getElementById('propertyThreeForm');
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

            // ── Image state ───────────────────────────────────────────────
            let newImageFiles = new DataTransfer();

            const imageDropZone = newForm.querySelector('#propertyThreeDropZone');
            const imagesInput = newForm.querySelector('#propertyThreeImages');
            const imagesPreview = newForm.querySelector('#propertyThreeNewImagesPreview');
            const galleryPreview = newForm.querySelector('#propertyThreeGalleryPreview');

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
                    this.value = '';
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

                imagesPreview.innerHTML = '';
                Array.from(newImageFiles.files).forEach((f, i) => addNewImageThumb(f, i));
            }

            // ── Existing images remove ────────────────────────────────────
            if (galleryPreview) {
                galleryPreview.querySelectorAll('.img-remove-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const wrapper = this.closest('.img-thumb-wrapper');

                        const removedInput = document.createElement('input');
                        removedInput.type = 'hidden';
                        removedInput.name = 'removed_images[]';
                        removedInput.value = wrapper.dataset.path;
                        newForm.appendChild(removedInput);

                        const existingInput = wrapper.querySelector(
                            'input[name="existing_images[]"]');
                        if (existingInput) existingInput.disabled = true;

                        wrapper.remove();
                    });
                });
            }

            // ── Video Drop Zone ───────────────────────────────────────────
            const videoDropZone = newForm.querySelector('#propertyThreeVideoDropZone');
            const videoInput = newForm.querySelector('#propertyThreeVideo');
            const videoPreviewBox = newForm.querySelector('#propertyThreeNewVideoPreview');
            const existingVideoWrap = newForm.querySelector('#propertyThreeVideoPreview');
            const removeExistingBtn = newForm.querySelector('#propertyThreeRemoveExistingVideo');
            const existingVideoPath = newForm.querySelector('#propertyThreeExistingVideoPath');

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
                    if (e.dataTransfer.files.length) handleVideoFile(e.dataTransfer.files[0]);
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
                        <button type="button" class="video-remove-btn" id="propertyThreeRemoveNewVideo" title="Remove">&#10005;</button>
                    </div>
                    <small class="text-success d-block mt-1">Selected: ${file.name}</small>
                `;

                videoPreviewBox.querySelector('#propertyThreeRemoveNewVideo')
                    ?.addEventListener('click', () => {
                        videoInput.value = '';
                        videoPreviewBox.innerHTML = '';
                    });
            }

            if (removeExistingBtn) {
                removeExistingBtn.addEventListener('click', () => {
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

                const submitBtn = this.querySelector('#propertyThreeSubmitButton');
                const spinner = this.querySelector('#propertyThreeSpinner');
                const btnText = this.querySelector('#propertyThreeSubmitBtnText');

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

        if (typeof window.initPropertyThreeSection === 'function') {
            window.initPropertyThreeSection();
        }
    })();
</script>

@include('backend.layouts.cms.property._style')
