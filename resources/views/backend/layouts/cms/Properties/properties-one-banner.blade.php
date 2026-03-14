<div class="row">
    <div class="col-lg-12">
        <div class="card box-shadow-0">
            <div class="card-header bg-light">
                <h4 class="card-title">Property Page - Banner Section</h4>
            </div>
            <div class="card-body">
                <form id="propertyBannerForm" method="post" action="{{ route('cms.property.banner-one.update') }}"
                    enctype="multipart/form-data">
                    @csrf

                    {{-- Title --}}
                    <div class="form-group mb-3">
                        <label for="property_title" class="form-label">Title</label>
                        <input type="text" class="form-control" name="title" id="property_title"
                            placeholder="Enter title" value="{{ $data->title ?? '' }}">
                        <div class="invalid-feedback"></div>
                    </div>

                    {{-- Sub Title --}}
                    <div class="form-group mb-3">
                        <label for="property_sub_title" class="form-label">Sub Title</label>
                        <input type="text" class="form-control" name="sub_title" id="property_sub_title"
                            placeholder="Enter Sub Title" value="{{ $data->sub_title ?? '' }}">
                        <div class="invalid-feedback"></div>
                    </div>

                    {{-- Image --}}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group mb-3">
                                <label for="image" class="form-label">Banckground Image</label>
                                <input type="file" class="dropify form-control"
                                    data-default-file="{{ !empty($data->image) && file_exists(public_path($data->image)) ? asset($data->image) : asset('default/placeholder-image.avif') }}"
                                    name="image" id="image" accept="image/*">
                                <small class="text-muted">Recommended: 1920x1080px (Max: 2MB)</small>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button class="btn btn-primary" type="submit" id="propertyBannerSubmitButton">
                            <span class="spinner-border spinner-border-sm d-none" id="propertyBannerSpinner"></span>
                            <span id="propertySubmitBtnText">Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

