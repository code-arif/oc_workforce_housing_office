{{-- ===================== Accordion Items Management ===================== --}}
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">
                    Housing Options - Accordion Items
                    <span class="badge bg-secondary ms-1">{{ count($accordions ?? []) }}</span>
                </h3>
                <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1" id="addAccordionBtn">
                    <i class="fe fe-plus"></i>
                    <span>Add New Item</span>
                </button>
            </div>
            <div class="card-body">
                @if (empty($accordions) || $accordions->isEmpty())
                    <div class="text-center py-5">
                        <i class="fe fe-list" style="font-size: 48px; color: #ccc;"></i>
                        <p class="text-muted mt-3">No accordion items found. Add your first item!</p>
                    </div>
                @else
                    <div id="sortable-accordions">
                        @foreach ($accordions as $accordion)
                            @php
                                $meta = $accordion->metadata ?? [];
                                $cards = $meta['cards'] ?? [];
                                $bottomText = $meta['bottom_text'] ?? '';
                            @endphp
                            <div class="card border mb-3 sortable-accordion-item" data-id="{{ $accordion->id }}">
                                <div class="card-body p-3">

                                    {{-- Header Row --}}
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="me-3 drag-handle-accordion text-muted" style="cursor:move;"
                                            title="Drag to reorder">
                                            <i class="fe fe-menu" style="font-size:20px;"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h5 class="mb-0 fw-bold">{{ $accordion->title }}</h5>
                                            <small class="text-muted">
                                                Order: {{ $accordion->order ?? '—' }} &nbsp;|&nbsp;
                                                {{ count($cards) }} property card(s)
                                            </small>
                                        </div>
                                        <div class="d-flex gap-1">
                                            <button
                                                class="btn btn-sm btn-info edit-accordion d-inline-flex align-items-center gap-1"
                                                data-id="{{ $accordion->id }}" data-title="{{ $accordion->title }}"
                                                data-bottom-text="{{ $bottomText }}"
                                                data-cards="{{ json_encode($cards) }}">
                                                <i class="fe fe-edit"></i>
                                                <span>Edit</span>
                                            </button>
                                            <button
                                                class="btn btn-sm btn-danger delete-accordion d-inline-flex align-items-center"
                                                data-id="{{ $accordion->id }}">
                                                <i class="fe fe-trash-2"></i>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Property Cards Preview --}}
                                    @if (count($cards) > 0)
                                        <div class="row g-2 mb-3">
                                            @foreach ($cards as $card)
                                                <div class="col-md-4 col-lg-3">
                                                    <div class="card border h-100" style="overflow:hidden;">
                                                        <div style="position:relative;">
                                                            <img src="{{ asset($card['image']) }}"
                                                                style="width:100%; height:120px; object-fit:cover;"
                                                                onerror="this.src='https://via.placeholder.com/300x120?text=No+Image'">
                                                            <span class="badge bg-dark"
                                                                style="position:absolute; bottom:8px; left:8px; font-size:11px;">
                                                                ${{ $card['price'] }} / Month
                                                            </span>
                                                        </div>
                                                        <div class="card-body p-2">
                                                            <p class="mb-0 fw-semibold small">
                                                                {{ $card['property_type'] }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="alert alert-warning mb-3 py-2 small">
                                            <i class="fe fe-alert-circle me-1"></i> No property cards added yet.
                                        </div>
                                    @endif

                                    {{-- Bottom Text Preview --}}
                                    @if ($bottomText)
                                        <div class="border-top pt-2">
                                            <small class="text-muted fst-italic">
                                                <i class="fe fe-align-left me-1"></i>{{ $bottomText }}
                                            </small>
                                        </div>
                                    @endif

                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ===================== Add / Edit Modal ===================== --}}
<div class="modal fade" id="accordionModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="accordionModalTitle">Add New Accordion Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">&times</button>
            </div>

            <form id="accordionForm" enctype="multipart/form-data">
                @csrf

                {{-- modal-body is scrollable --}}
                <div class="modal-body">

                    {{-- ① Title --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Accordion Title <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="accordion_title" name="title"
                            placeholder="e.g. Explore Properties, Track Payments...">
                        <div class="invalid-feedback"></div>
                    </div>

                    {{-- ② Bottom Description --}}
                    <div class="mb-4">
                        <label class="form-label fw-semibold">
                            Bottom Description
                            <span class="text-muted fw-normal small">(shown below the property cards)</span>
                        </label>
                        <textarea class="form-control" id="accordion_bottom_text" name="bottom_text" rows="2"
                            placeholder="e.g. Explore with insider knowledge and seamlessly premium properties..."></textarea>
                    </div>

                    <hr class="my-3">

                    {{-- ③ Property Cards --}}
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0 fw-bold">
                            <i class="fe fe-grid me-1"></i>
                            Property Cards
                            <span class="badge bg-secondary ms-1" id="cardCount">0</span>
                        </h6>
                        <button type="button" class="btn btn-sm btn-success d-inline-flex align-items-center gap-1"
                            id="addCardBtn">
                            <i class="fe fe-plus"></i>
                            <span>Add Card</span>
                        </button>
                    </div>

                    <div id="noCardsMsg" class="text-center text-muted py-4 border rounded-1 mb-2">
                        <i class="fe fe-image" style="font-size:32px;"></i>
                        <p class="mt-2 mb-0 small">No cards added yet. Click "Add Card" to add property listings.</p>
                    </div>

                    <div id="cardsContainer" class="row g-3"></div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary d-inline-flex align-items-center gap-1"
                        data-bs-dismiss="modal">
                        <i class="fe fe-x"></i>
                        <span>Cancel</span>
                    </button>
                    <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-2">
                        <span class="spinner-border spinner-border-sm d-none" id="accordionSpinner"></span>
                        <i class="fe fe-save" id="accordionSaveIcon"></i>
                        <span id="accordionSubmitText">Save Item</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ===================== Card Template ===================== --}}
<template id="cardTemplate">
    <div class="col-md-6 col-lg-4 property-card-item">
        <div class="card border h-100">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                <span class="fw-semibold small card-number">Card #1</span>
                <button type="button"
                    class="btn btn-sm btn-outline-danger remove-card d-inline-flex align-items-center gap-1 py-0 px-2">
                    <i class="fe fe-x"></i>
                    <span>Remove</span>
                </button>
            </div>
            <div class="card-body p-3">

                <div class="mb-3">
                    <label class="form-label small fw-semibold">
                        Property Image <span class="text-danger">*</span>
                    </label>
                    <input type="file" class="form-control form-control-sm card-image-input"
                        accept="image/png,image/jpg,image/jpeg,image/webp">
                    <small class="text-muted">PNG/JPG/WEBP · Max 2MB</small>
                    <input type="hidden" class="existing-image-path">
                </div>

                <div class="existing-image-preview mb-2" style="display:none;">
                    <p class="text-muted small mb-1">Current image:</p>
                    <img class="img-fluid border rounded-1" style="max-height:100px; width:100%; object-fit:cover;">
                </div>

                <div class="new-image-preview mb-3" style="display:none;">
                    <p class="text-muted small mb-1">New preview:</p>
                    <img class="img-fluid border rounded-1" style="max-height:100px; width:100%; object-fit:cover;">
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">
                        Price / Month <span class="text-danger">*</span>
                    </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">$</span>
                        <input type="number" class="form-control card-price" placeholder="850" min="0"
                            step="50">
                    </div>
                </div>

                <div class="mb-0">
                    <label class="form-label small fw-semibold">
                        Property Type <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control form-control-sm card-type"
                        placeholder="e.g. Shared Apartment, Single Room">
                </div>

            </div>
        </div>
    </div>
</template>


<script>
    function initHousingOptionSection() {
        console.log('Housing Option section initialized');

        let accordionModal = null;
        let isEditMode = false;
        let editingAccordionId = null;
        let cardIndex = 0;

        // Bootstrap Modal init
        const modalEl = document.getElementById('accordionModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            accordionModal = new bootstrap.Modal(modalEl);
            modalEl.addEventListener('hidden.bs.modal', resetAccordionForm);
        }

        // Add Button
        document.getElementById('addAccordionBtn')?.addEventListener('click', () => {
            isEditMode = false;
            editingAccordionId = null;
            document.getElementById('accordionModalTitle').textContent = 'Add New Accordion Item';
            document.getElementById('accordionSubmitText').textContent = 'Save Item';
            if (accordionModal) accordionModal.show();
        });

        // Edit Buttons
        document.querySelectorAll('.edit-accordion').forEach(btn => {
            btn.addEventListener('click', function() {
                isEditMode = true;
                editingAccordionId = this.dataset.id;

                document.getElementById('accordionModalTitle').textContent = 'Edit Accordion Item';
                document.getElementById('accordionSubmitText').textContent = 'Update Item';
                document.getElementById('accordion_title').value = this.dataset.title || '';
                document.getElementById('accordion_bottom_text').value = this.dataset.bottomText || '';

                let existingCards = [];
                try {
                    existingCards = JSON.parse(this.dataset.cards || '[]');
                } catch (e) {}
                existingCards.forEach(card => addCardToUI(card));
                updateCardCount();

                if (accordionModal) accordionModal.show();
            });
        });

        // Add Card Button
        document.getElementById('addCardBtn')?.addEventListener('click', () => {
            addCardToUI();
            updateCardCount();
        });

        // Add card DOM
        function addCardToUI(existingCard = null) {
            const template = document.getElementById('cardTemplate');
            const clone = template.content.cloneNode(true);
            const item = clone.querySelector('.property-card-item');

            cardIndex++;
            item.dataset.cardIndex = cardIndex;
            item.querySelector('.card-number').textContent = `Card #${cardIndex}`;

            if (existingCard) {
                item.querySelector('.card-price').value = existingCard.price || '';
                item.querySelector('.card-type').value = existingCard.property_type || '';
                if (existingCard.image) {
                    item.querySelector('.existing-image-path').value = existingCard.image;
                    const prev = item.querySelector('.existing-image-preview');
                    prev.querySelector('img').src = '/' + existingCard.image.replace(/^\//, '');
                    prev.style.display = 'block';
                }
            }

            item.querySelector('.card-image-input').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (!file) return;
                if (file.size > 2 * 1024 * 1024) {
                    showToast('error', 'Image must not exceed 2MB');
                    e.target.value = '';
                    return;
                }
                const reader = new FileReader();
                reader.onload = ev => {
                    const np = item.querySelector('.new-image-preview');
                    np.querySelector('img').src = ev.target.result;
                    np.style.display = 'block';
                };
                reader.readAsDataURL(file);
            });

            item.querySelector('.remove-card').addEventListener('click', () => {
                item.remove();
                renumberCards();
                updateCardCount();
            });

            document.getElementById('cardsContainer').appendChild(clone);
            document.getElementById('noCardsMsg').style.display = 'none';
        }

        function renumberCards() {
            document.querySelectorAll('#cardsContainer .property-card-item').forEach((el, i) => {
                el.querySelector('.card-number').textContent = `Card #${i + 1}`;
            });
        }

        function updateCardCount() {
            const count = document.querySelectorAll('#cardsContainer .property-card-item').length;
            document.getElementById('cardCount').textContent = count;
            document.getElementById('noCardsMsg').style.display = count === 0 ? 'block' : 'none';
        }

        // Form Submit
        const accordionForm = document.getElementById('accordionForm');
        if (accordionForm) {
            accordionForm.removeEventListener('submit', handleAccordionSubmit);
            accordionForm.addEventListener('submit', handleAccordionSubmit);
        }

        async function handleAccordionSubmit(e) {
            e.preventDefault();

            const form = e.target;
            const spinner = document.getElementById('accordionSpinner');
            const icon = document.getElementById('accordionSaveIcon');
            const text = document.getElementById('accordionSubmitText');
            const btn = form.querySelector('button[type="submit"]');

            const title = document.getElementById('accordion_title').value.trim();
            if (!title) {
                showToast('error', 'Accordion title is required');
                return;
            }

            const cardItems = document.querySelectorAll('#cardsContainer .property-card-item');
            let valid = true;
            cardItems.forEach(cardEl => {
                const price = cardEl.querySelector('.card-price').value.trim();
                const type = cardEl.querySelector('.card-type').value.trim();
                const file = cardEl.querySelector('.card-image-input').files[0];
                const existing = cardEl.querySelector('.existing-image-path').value;
                if (!price || !type || (!existing && !file)) valid = false;
            });
            if (!valid) {
                showToast('error', 'Please fill all card fields and upload images.');
                return;
            }

            const formData = new FormData();
            formData.append('_token', form.querySelector('[name="_token"]').value);
            formData.append('title', title);
            formData.append('bottom_text', document.getElementById('accordion_bottom_text').value.trim());

            cardItems.forEach((cardEl, i) => {
                formData.append(`cards[${i}][price]`, cardEl.querySelector('.card-price').value.trim());
                formData.append(`cards[${i}][property_type]`, cardEl.querySelector('.card-type').value
                    .trim());
                formData.append(`cards[${i}][existing_image]`, cardEl.querySelector('.existing-image-path')
                    .value);
                const file = cardEl.querySelector('.card-image-input').files[0];
                if (file) formData.append(`cards[${i}][image]`, file);
            });

            btn.disabled = true;
            spinner.classList.remove('d-none');
            icon.classList.add('d-none');
            text.textContent = isEditMode ? 'Updating...' : 'Saving...';

            try {
                const url = isEditMode ?
                    `/admin/cms/home/housing-option/accordion/update/${editingAccordionId}` :
                    `/admin/cms/home/housing-option/accordion/store`;

                const response = await axios.post(url, formData, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.data.success) {
                    showToast('success', response.data.message);
                    if (accordionModal) accordionModal.hide();
                    setTimeout(() => window.cmsManager.loadSection('housing-options'), 800);
                }
            } catch (err) {
                handleError(err);
            } finally {
                btn.disabled = false;
                spinner.classList.add('d-none');
                icon.classList.remove('d-none');
                text.textContent = isEditMode ? 'Update Item' : 'Save Item';
            }
        }

        // Delete
        document.querySelectorAll('.delete-accordion').forEach(btn => {
            btn.addEventListener('click', async function() {
                const id = this.dataset.id;
                const result = await Swal.fire({
                    title: 'Are you sure?',
                    text: 'This will delete the item and all its property cards.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                });
                if (!result.isConfirmed) return;
                try {
                    const response = await axios.delete(
                        `/admin/cms/home/housing-option/accordion/${id}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        }
                    );
                    showToast('success', response.data.message);
                    setTimeout(() => window.cmsManager.loadSection('housing-options'), 800);
                } catch (err) {
                    showToast('error', err.response?.data?.message || 'Failed to delete');
                }
            });
        });

        // Sortable
        const sortableList = document.getElementById('sortable-accordions');
        if (sortableList && typeof Sortable !== 'undefined') {
            new Sortable(sortableList, {
                animation: 150,
                handle: '.drag-handle-accordion',
                onEnd: async function() {
                    const orders = [];
                    document.querySelectorAll('.sortable-accordion-item').forEach((el, index) => {
                        orders.push({
                            id: el.dataset.id,
                            position: index + 1
                        });
                    });
                    try {
                        const response = await axios.post(
                            `/admin/cms/home/housing-option/accordion/update-order`, {
                                orders
                            }, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            }
                        );
                        showToast('success', response.data.message);
                    } catch (err) {
                        showToast('error', 'Failed to update order');
                    }
                }
            });
        }

        // Reset form
        function resetAccordionForm() {
            document.getElementById('accordionForm').reset();
            document.getElementById('cardsContainer').innerHTML = '';
            document.getElementById('noCardsMsg').style.display = 'block';
            document.getElementById('cardCount').textContent = '0';
            cardIndex = 0;
            isEditMode = false;
            editingAccordionId = null;
        }

        // Error handler
        function handleError(error) {
            let message = 'An error occurred';
            if (error.response?.status === 422 && error.response?.data?.errors) {
                message = Object.values(error.response.data.errors).flat().join('<br>');
            } else {
                message = error.response?.data?.message || message;
            }
            showToast('error', message);
        }
    }

    if (document.getElementById('housing-options-tab')?.classList.contains('active')) {
        initHousingOptionSection();
    }
</script>

<style>
    #sortable-accordions .sortable-chosen {
        box-shadow: 0 4px 16px rgba(82, 26, 172, 0.2);
    }

    .drag-handle-accordion:hover {
        color: #521aac;
    }

    .property-card-item .card {
        transition: box-shadow 0.15s;
    }

    .property-card-item .card:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
    }

    /* FIX MODAL SCROLL + IMAGE PREVIEW ISSUES */
    .modal-xl .modal-body {
        max-height: 65vh;
        overflow-y: auto;
        overflow-x: hidden;
        padding-bottom: 1.5rem;
        /* breathing room at bottom */
    }

    .modal-xl .modal-dialog {
        margin: 1rem;
        /* better mobile feel */
    }

    .property-card-item .new-image-preview img,
    .property-card-item .existing-image-preview img {
        max-height: 100px !important;
        width: 100%;
        object-fit: cover;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
    }

    .property-card-item .card-body {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    /* Optional: make remove button more visible */
    .remove-card {
        font-size: 0.85rem;
    }
</style>
