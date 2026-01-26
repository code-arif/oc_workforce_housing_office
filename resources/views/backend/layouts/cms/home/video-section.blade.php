<!-- Video Section Content -->
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Video Section ({{ count($videos ?? []) }} / 2)</h3>
                @if(count($videos ?? []) < 2)
                <button type="button" class="btn btn-primary" id="addVideoBtn">
                    <i class="fe fe-plus me-2"></i> Add New Video
                </button>
                @endif
            </div>
            <div class="card-body">
                @if (empty($videos) || $videos->isEmpty())
                    <div class="text-center py-5">
                        <i class="fe fe-video" style="font-size: 48px; color: #ccc;"></i>
                        <p class="text-muted mt-3">No videos found. Add your first video! (Maximum 2 videos)</p>
                    </div>
                @else
                    <div id="sortable-videos" class="row">
                        @foreach ($videos as $video)
                            <div class="col-md-6 mb-3 sortable-item" data-id="{{ $video->id }}">
                                <div class="card border">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center mb-2">
                                            <div class="me-2 drag-handle" style="cursor: move;">
                                                <i class="fe fe-menu" style="font-size: 20px;"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <p class="mb-0 fw-bold">{{ $video->title }}</p>
                                                @if($video->subtitle)
                                                    <small class="text-muted">{{ $video->subtitle }}</small>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="mb-2">
                                            <video class="img-fluid border" style="width: 100%; max-height: 200px;" controls>
                                                <source src="{{ asset($video->video_url) }}" type="video/mp4">
                                                Your browser does not support the video tag.
                                            </video>
                                        </div>

                                        @if($video->key_points && count($video->key_points) > 0)
                                            <div class="mb-2">
                                                <strong class="d-block mb-1">Key Points:</strong>
                                                <ul class="mb-0 ps-3">
                                                    @foreach($video->key_points as $point)
                                                        @if(!empty($point))
                                                            <li class="text-muted small">{{ $point }}</li>
                                                        @endif
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="form-check form-switch mb-0">
                                                <input class="form-check-input status-toggle custom-toggle"
                                                    type="checkbox" data-id="{{ $video->id }}"
                                                    {{ $video->status ? 'checked' : '' }} style="margin-left:0px;">
                                            </div>

                                            <div>
                                                <button class="btn btn-sm btn-info edit-video me-1"
                                                    data-id="{{ $video->id }}" 
                                                    data-title="{{ $video->title }}"
                                                    data-subtitle="{{ $video->subtitle }}"
                                                    data-keypoints="{{ json_encode($video->key_points) }}"
                                                    data-status="{{ $video->status }}">
                                                    <i class="fe fe-edit"></i>
                                                </button>

                                                <button class="btn btn-sm btn-danger delete-video"
                                                    data-id="{{ $video->id }}">
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

<!-- Add/Edit Video Modal -->
<div class="modal fade" id="addVideoModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">Add New Video</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="videoForm" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="video_title" class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" id="video_title"
                            placeholder="Enter title" required>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label for="video_subtitle" class="form-label">Subtitle</label>
                        <input type="text" class="form-control" name="subtitle" id="video_subtitle"
                            placeholder="Enter subtitle">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Video File <span class="text-danger">*</span></label>
                        <input type="file" name="video" id="videoFile" class="form-control" 
                            accept="video/mp4,video/avi,video/mov,video/wmv" required>
                        <small class="text-muted">Supported: MP4, AVI, MOV, WMV (Max: 50MB)</small>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Key Points</label>
                        <div id="keyPointsContainer">
                            <div class="input-group mb-2">
                                <input type="text" class="form-control" name="key_points[]" 
                                    placeholder="Enter key point">
                                <button class="btn btn-outline-danger remove-point" type="button" style="display:none;">
                                    <i class="fe fe-x"></i>
                                </button>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addKeyPointBtn">
                            <i class="fe fe-plus me-1"></i> Add Key Point
                        </button>
                    </div>

                    <div class="mb-3" style="margin-left: 12px">
                        <div class="form-check form-switch">
                            <input class="form-check-input custom-toggle" type="checkbox" name="status"
                                id="video_status" value="1" checked>
                            <label class="form-check-label" for="video_status"
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
                        <span id="modalSubmitText">Add Video</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function initVideoSection() {
        console.log('Video section initialized');
        let videoModal = null;
        let editMode = false;
        let editingId = null;

        // Initialize modal
        const modalElement = document.getElementById('addVideoModal');
        if (modalElement && typeof bootstrap !== 'undefined') {
            videoModal = new bootstrap.Modal(modalElement);
            modalElement.addEventListener('hidden.bs.modal', resetVideoForm);
        }

        // Add Video Button
        document.getElementById('addVideoBtn')?.addEventListener('click', () => {
            editMode = false;
            editingId = null;
            document.querySelector('#addVideoModal .modal-title').textContent = 'Add New Video';
            document.getElementById('modalSubmitText').textContent = 'Add Video';
            if (videoModal) videoModal.show();
        });

        // Edit Video Buttons
        document.querySelectorAll('.edit-video').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const title = this.dataset.title;
                const subtitle = this.dataset.subtitle || '';
                const status = this.dataset.status;
                const keypoints = JSON.parse(this.dataset.keypoints || '[]');

                editMode = true;
                editingId = id;

                document.getElementById('video_title').value = title;
                document.getElementById('video_subtitle').value = subtitle;
                document.getElementById('video_status').checked = status == 1;
                document.getElementById('videoFile').removeAttribute('required');

                // Populate key points
                const container = document.getElementById('keyPointsContainer');
                container.innerHTML = '';
                
                if (keypoints.length > 0) {
                    keypoints.forEach((point, index) => {
                        if (point) {
                            addKeyPointField(point);
                        }
                    });
                } else {
                    addKeyPointField('');
                }

                document.querySelector('#addVideoModal .modal-title').textContent = 'Update Video';
                document.getElementById('modalSubmitText').textContent = 'Update Video';

                if (videoModal) videoModal.show();
            });
        });

        // Add Key Point Field
        document.getElementById('addKeyPointBtn')?.addEventListener('click', () => {
            addKeyPointField('');
        });

        function addKeyPointField(value = '') {
            const container = document.getElementById('keyPointsContainer');
            const div = document.createElement('div');
            div.className = 'input-group mb-2';
            div.innerHTML = `
                <input type="text" class="form-control" name="key_points[]" 
                    placeholder="Enter key point" value="${value}">
                <button class="btn btn-outline-danger remove-point" type="button">
                    <i class="fe fe-x"></i>
                </button>
            `;
            container.appendChild(div);

            // Add remove handler
            div.querySelector('.remove-point').addEventListener('click', function() {
                div.remove();
            });
        }

        // Video File Validation
        document.getElementById('videoFile')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const maxSize = 50 * 1024 * 1024; // 50MB
                if (file.size > maxSize) {
                    showToast('error', 'Video size should not exceed 50MB');
                    e.target.value = '';
                    return;
                }
            }
        });

        // Video Form Submit
        const videoForm = document.getElementById('videoForm');
        if (videoForm) {
            videoForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const submitBtn = this.querySelector('button[type="submit"]');
                const spinner = document.getElementById('modalSpinner');
                const btnText = document.getElementById('modalSubmitText');

                submitBtn.disabled = true;
                spinner.classList.remove('d-none');
                btnText.textContent = editMode ? 'Updating...' : 'Adding...';

                // Clear previous errors
                this.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                this.querySelectorAll('.invalid-feedback').forEach(el => {
                    el.textContent = '';
                    el.style.display = 'none';
                });

                try {
                    const formData = new FormData(this);
                    const url = editMode 
                        ? route('cms.video.update', editingId)
                        : route('cms.video.store');

                    const response = await axios.post(url, formData, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'multipart/form-data'
                        }
                    });

                    if (response.data.success) {
                        showToast('success', response.data.message);
                        if (videoModal) videoModal.hide();
                        setTimeout(() => location.reload(), 1500);
                    }
                } catch (error) {
                    if (error.response?.status === 422 && error.response?.data?.errors) {
                        const errors = error.response.data.errors;
                        Object.keys(errors).forEach(field => {
                            const input = this.querySelector(`[name="${field}"]`);
                            if (input) {
                                input.classList.add('is-invalid');
                                const feedback = input.nextElementSibling;
                                if (feedback?.classList.contains('invalid-feedback')) {
                                    feedback.textContent = errors[field][0];
                                    feedback.style.display = 'block';
                                }
                            }
                        });
                        showToast('error', Object.values(errors).flat()[0]);
                    } else {
                        showToast('error', error.response?.data?.message || 'Failed to save video');
                    }
                } finally {
                    submitBtn.disabled = false;
                    spinner.classList.add('d-none');
                    btnText.textContent = editMode ? 'Update Video' : 'Add Video';
                }
            });
        }

        // Status Toggle
        document.querySelectorAll('.status-toggle').forEach(toggle => {
            toggle.addEventListener('change', async function() {
                const id = this.dataset.id;
                const status = this.checked;

                try {
                    const response = await axios.post(route('cms.video.status', id), {
                        status: status
                    });

                    if (response.data.success) {
                        showToast('success', response.data.message);
                    }
                } catch (error) {
                    showToast('error', 'Failed to update status');
                    this.checked = !status;
                }
            });
        });

        // Delete Video
        document.querySelectorAll('.delete-video').forEach(btn => {
            btn.addEventListener('click', async function() {
                if (!confirm('Are you sure you want to delete this video?')) return;

                const id = this.dataset.id;

                try {
                    const response = await axios.delete(route('cms.video.destroy', id));

                    if (response.data.success) {
                        showToast('success', response.data.message);
                        setTimeout(() => location.reload(), 1500);
                    }
                } catch (error) {
                    showToast('error', error.response?.data?.message || 'Failed to delete video');
                }
            });
        });

        // Sortable functionality
        if (typeof Sortable !== 'undefined') {
            const sortableEl = document.getElementById('sortable-videos');
            if (sortableEl) {
                Sortable.create(sortableEl, {
                    handle: '.drag-handle',
                    animation: 150,
                    onEnd: async function(evt) {
                        const items = sortableEl.querySelectorAll('.sortable-item');
                        const orders = Array.from(items).map((item, index) => ({
                            id: parseInt(item.dataset.id),
                            position: index + 1
                        }));

                        try {
                            const response = await axios.post(route('cms.video.updateOrder'), {
                                orders: orders
                            });

                            if (response.data.success) {
                                showToast('success', response.data.message);
                            }
                        } catch (error) {
                            showToast('error', 'Failed to update order');
                        }
                    }
                });
            }
        }

        function resetVideoForm() {
            const form = document.getElementById('videoForm');
            form.reset();
            form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            
            // Reset key points to single field
            const container = document.getElementById('keyPointsContainer');
            container.innerHTML = `
                <div class="input-group mb-2">
                    <input type="text" class="form-control" name="key_points[]" 
                        placeholder="Enter key point">
                    <button class="btn btn-outline-danger remove-point" type="button" style="display:none;">
                        <i class="fe fe-x"></i>
                    </button>
                </div>
            `;
            
            document.getElementById('videoFile').setAttribute('required', 'required');
            editMode = false;
            editingId = null;
        }

        console.log('Video section handlers attached');
    }

    // Auto-initialize when section loads
    if (typeof initVideoSection === 'function') {
        initVideoSection();
    }
</script>
