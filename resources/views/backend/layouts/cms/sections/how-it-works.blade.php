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
    // Global function define koro - window.initHowItWorksSection
    function initHowItWorksSection() {
        console.log('How It Works section initialized');

        const csrfToken = $('meta[name="csrf-token"]').attr('content');

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });

        // Remove previous event listeners to avoid duplicates
        $('#howItWorksSectionForm').off('submit');

        // $('#howItWorksSectionForm').on('submit', function(e) {
        //     e.preventDefault();
        //     e.stopPropagation();

        //     let url = $(this).attr('action');
        //     let formData = new FormData(this);

        //     $('#howItWorkSpinner').removeClass('d-none');
        //     $('#submitBtnText').text('Saving...');
        //     $('#submitButton').prop('disabled', true);

        //     $.ajax({
        //         url: url,
        //         type: "POST",
        //         data: formData,
        //         contentType: false,
        //         processData: false,

        //         success: function(res) {
        //             console.log('Success:', res);
        //             if (res.success) {
        //                 toastr.success(res.message ?? 'Updated successfully!');
        //             } else {
        //                 toastr.error(res.message ?? 'Failed to update!');
        //             }
        //         },

        //         error: function(xhr) {
        //             console.log('Error:', xhr.responseJSON);
        //             if (xhr.status === 422 && xhr.responseJSON?.errors) {
        //                 $.each(xhr.responseJSON.errors, function(key, value) {
        //                     toastr.error(value[0]);
        //                 });
        //             } else {
        //                 toastr.error(xhr.responseJSON?.message ?? 'Something went wrong!');
        //             }
        //         },

        //         complete: function() {
        //             $('#howItWorkSpinner').addClass('d-none');
        //             $('#submitBtnText').text('Save Changes');
        //             $('#submitButton').prop('disabled', false);
        //         }
        //     });

        //     return false;
        // });
    }


        // Initialize if hero tab is active
    if (document.getElementById('how-it-works-tab')?.classList.contains('active')) {
        initHeroSection();
    }
</script>
