<div class="row">
    <div class="col-lg-12">
        <div class="card box-shadow-0">
            <div class="card-header bg-light">
                <h4 class="card-title">Property Page - Our Offer</h4>
            </div>
            <div class="card-body">
                <form id="propertyOurOfferForm" method="post" action="{{ route('cms.property.our-offer.update') }}"
                    enctype="multipart/form-data">
                    @csrf

                    {{-- Title --}}
                    <div class="form-group mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" id="title"
                            placeholder="Enter title" value="{{ $data->title ?? '' }}">
                        <div class="invalid-feedback"></div>
                    </div>

                    {{-- Sub Title --}}
                    <div class="form-group mb-3">
                        <label for="sub_title" class="form-label">Sub Title</label>
                        <input type="text" class="form-control" name="sub_title" id="sub_title"
                            placeholder="Enter Sub Title" value="{{ $data->sub_title ?? '' }}">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="form-group">
                        <button class="btn btn-primary" type="submit" id="propertyOurOfferSubmitButton">
                            <span class="spinner-border spinner-border-sm d-none" id="propertyOurOfferSpinner"></span>
                            <span id="propertyOfferSubmitBtnText">Save Changes</span>
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
        window.initPropertyOfferSection = function() {

            const form = document.getElementById('propertyOurOfferForm');
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

                const submitBtn = this.querySelector('#propertyOurOfferSubmitButton');
                const spinner = this.querySelector('#propertyOurOfferSpinner');
                const btnText = this.querySelector('#propertyOfferSubmitBtnText');

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

            function clearFormErrors(form) {
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => {
                    el.textContent = '';
                    el.style.display = 'none';
                });
            }
        };

        // Auto-execute initialization
        if (typeof window.initPropertyOfferSection === 'function') {
            window.initPropertyOfferSection();
        }
    })();
</script>
