<div class="row">
    {{-- Your form HTML same as before --}}
    <div class="col-lg-4">
        <div class="card box-shadow-0">
            <div class="card-header bg-light">
                <h4 class="card-title">Home Page - How It Works</h4>
            </div>
            <div class="card-body">
                <form id="howItWorksSectionForm" method="post" action="{{ route('cms.home.how-it-works.update') }}">
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
                        <button class="btn btn-primary" type="submit" id="submitButton">
                            <span class="spinner-border spinner-border-sm d-none" id="howItWorkSpinner"></span>
                            <span id="submitBtnText">Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function initHowItWorksSection() {
        console.log('How It Works section initialized');

        // Remove previous listeners to avoid duplicates
        const form = document.getElementById('howItWorksSectionForm');
        if (!form) {
            console.error('Form not found');
            return;
        }

        // Clone and replace to remove all old event listeners
        const newForm = form.cloneNode(true);
        form.parentNode.replaceChild(newForm, form);

        // Add new event listener
        newForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            e.stopPropagation();

            const submitBtn = this.querySelector('#submitButton');
            const spinner = this.querySelector('#howItWorkSpinner');
            const btnText = this.querySelector('#submitBtnText');

            // Disable button and show loading
            submitBtn.disabled = true;
            spinner.classList.remove('d-none');
            btnText.textContent = 'Saving...';

            // Clear previous errors
            clearFormErrors(this);

            try {
                const formData = new FormData(this);

                const response = await axios.post(this.action, formData, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'multipart/form-data'
                    }
                });

                if (response.data.success) {
                    showToast('success', response.data.message || 'Updated successfully!');
                } else {
                    showToast('error', response.data.message || 'Failed to update!');
                }
            } catch (error) {
                console.error('Error:', error);

                if (error.response?.status === 422 && error.response?.data?.errors) {
                    // Validation errors
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
                        showToast('error', errors[field][0]);
                    });
                } else {
                    showToast('error', error.response?.data?.message || 'Something went wrong!');
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
    }

    // Auto-initialize if on how-it-works section
    if (document.getElementById('how-it-works-tab')?.classList.contains('active')) {
        initHowItWorksSection();
    }
</script>
