@extends('backend.app')

@section('title', 'CMS Manager')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                {{-- PAGE-HEADER --}}
                <div class="page-header">
                    <div>
                        <h1 class="page-title" id="dynamic-title">CMS Manager</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">CMS</a></li>
                            <li class="breadcrumb-item active" aria-current="page" id="dynamic-breadcrumb">Dashboard</li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    <!-- Main Content Area -->
                    <div class="col-lg-10 col-xl-10 col-md-10 col-sm-12">
                        <div id="dynamic-content">
                            @include('backend.layouts.cms.home.hero', [
                                'data' => $heroData ?? null,
                                'sliders' => $sliders ?? [],
                            ])
                        </div>
                    </div>

                    <!-- Right Sidebar Tabs -->
                    <div class="col-lg-2 col-xl-2 col-md-2 col-sm-12">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title text-white mb-0">CMS Sections</h5>
                            </div>
                            <div class="card-body p-0">
                                <div class="nav flex-column nav-pills" id="cms-tabs" role="tablist">
                                    <h6 class="px-3 pt-3 pb-2 mb-0 text-uppercase text-muted" style="font-size: 0.75rem;">
                                        Home Page
                                    </h6>

                                    <a class="nav-link active" id="hero-tab" href="javascript:void(0);" data-section="hero"
                                        data-title="Hero Section" data-breadcrumb="Hero">
                                        <i class="fe fe-home me-2"></i> Hero Section
                                    </a>

                                    <a class="nav-link" id="how-it-works-tab" href="javascript:void(0);"
                                        data-section="how-it-works" data-title="How It Works"
                                        data-breadcrumb="How It Works">
                                        <i class="fe fe-play-circle me-2"></i> How It Works
                                    </a>

                                    <a class="nav-link" id="employee-and-sponsor-tab" href="javascript:void(0);"
                                        data-section="employee-and-sponsor" data-title="Employers & Sponsor"
                                        data-breadcrumb="Employers & Sponsor">
                                        <i class="fe fe-users me-2"></i>Employers & Sponsor
                                    </a>

                                    <a class="nav-link" id="prime-location-tab" href="javascript:void(0);"
                                        data-section="prime-location" data-title="Prime Location"
                                        data-breadcrumb="Prime Location">
                                        <i class="fa-solid fa-location-crosshairs me-2"></i> Prime Locations
                                    </a>

                                    <a class="nav-link" id="apartment-tab" href="javascript:void(0);"
                                        data-section="apartment" data-title="Apartment" data-breadcrumb="Apartment">
                                        <i class="fa-solid fa-building me-2"></i> Apartment
                                    </a>

                                    <a class="nav-link" id="gallery-tab" href="javascript:void(0);" data-section="gallery"
                                        data-title="Gallery Section" data-breadcrumb="Gallery">
                                        <i class="fa-solid fa-images me-2"></i> Gallery
                                    </a>

                                    {{-- about us page --}}
                                    <h6 class="px-3 pt-3 pb-2 mb-0 text-uppercase text-muted" style="font-size: 0.75rem;">
                                        About Us
                                    </h6>

                                    <a class="nav-link" id="about-us-breadcrumb-tab" href="javascript:void(0);"
                                        data-section="about-us-breadcrumb" data-title="About Us Breadcrumb"
                                        data-breadcrumb="About Us Breadcrumb">
                                        <i class="fa-regular fa-address-card me-2"></i> About Us Breadcrumb
                                    </a>

                                    <a class="nav-link" id="about-contact-breadcrumb-tab" href="javascript:void(0);"
                                        data-section="about-contact-breadcrumb" data-title="Contact us Breadcrumb"
                                        data-breadcrumb="Contact us Breadcrumb">
                                        <i class="fa-solid fa-phone me-2"></i> Contact us Breadcrumb
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/css/iziToast.min.css">
    <style>
        .nav-pills .nav-link {
            border-radius: 0;
            padding: 0.75rem 1rem;
            color: #495057;
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }

        .nav-pills .nav-link:hover {
            background-color: rgba(217, 166, 0, 0.15);
            color: #000 !important;
            border-left-color: #521aac;
        }

        .nav-pills .nav-link.active {
            background-color: rgba(82, 26, 172, 0.1);
            color: #000 !important;
            border-left-color: #521aac;
            font-weight: 500;
        }

        .nav-pills .nav-link i {
            width: 20px;
            text-align: center;
        }

        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            border-radius: 0.25rem;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeIn 0.3s ease;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/js/iziToast.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <script>
        // Configuration
        const BASE_URL = "{{ url('admin/cms') }}";

        // CSRF Token setup
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (csrfToken) {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
        }

        // Route helper - FIXED with all routes
        window.route = function(name, params = {}) {
            const routes = {
                'cms.index': BASE_URL,
                'cms.section': BASE_URL + '/section/' + (params.section || params),
                'cms.home.hero.section.update': BASE_URL + '/home/hero/update',
                'cms.slider.store': BASE_URL + '/home/slider/store',
                'cms.slider.status': BASE_URL + '/home/slider/' + (params.id || params) + '/status',
                'cms.slider.destroy': BASE_URL + '/home/slider/' + (params.id || params),
                'cms.slider.updateOrder': BASE_URL + '/home/slider/update-order',
                'cms.home.how-it-works.update': BASE_URL + '/home/how-it-works/update',
                'cms.home.how-it-works.store': BASE_URL + '/home/how-it-works/item/store',
                'cms.home.how-it-works.update.item': BASE_URL + '/home/how-it-works/item/update',
                'cms.home.how-it-works.delete': BASE_URL + '/home/how-it-works/item/delete',

                // employee and sponsor route
                'cms.home.employee.and.sponsor.section.update': BASE_URL + '/home/employee-and-sponsor/update',

                // prime location upate route
                'cms.home.prime.location.section.update': BASE_URL + '/home/prime-location/update',

                // apartment update route
                'cms.home.apartment.section.update': BASE_URL + '/home/apartment/update',

                // gallery image delete route
                'cms.gallery.item.delete': BASE_URL + '/gallery/item/delete/' + (params.id || params),

            };
            return routes[name] || BASE_URL;
        };

        // Asset URL helper
        window.assetUrl = function(path) {
            if (!path) return '';
            if (path.startsWith('http')) return path;
            const baseUrl = "{{ url('/') }}";
            return baseUrl + '/' + path.replace(/^\/+/, '');
        };


        // Global Toast Helper
        window.showToast = function(type, message) {
            if (typeof iziToast !== 'undefined') {
                iziToast[type]({
                    title: type === 'success' ? 'Success' : 'Error',
                    message: message,
                    position: 'topRight',
                    timeout: type === 'success' ? 3000 : 5000
                });
            } else if (typeof toastr !== 'undefined') {
                toastr[type](message);
            } else {
                alert(message);
            }
        };

        // Define How It Works Section Function GLOBALLY
        window.initHowItWorksSection = function() {
            console.log('How It Works section initialized (global)');

            const form = document.getElementById('howItWorksSectionForm');
            if (!form) {
                console.error('Form not found');
                return;
            }

            // Remove any existing submit handlers by cloning
            const newForm = form.cloneNode(true);
            form.parentNode.replaceChild(newForm, form);

            // Add submit handler
            newForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                e.stopPropagation();

                const submitBtn = this.querySelector('#submitButton');
                const spinner = this.querySelector('#howItWorkSpinner');
                const btnText = this.querySelector('#submitBtnText');

                console.log('Form action:', this.action);
                console.log('Form submitting...');

                submitBtn.disabled = true;
                spinner.classList.remove('d-none');
                btnText.textContent = 'Saving...';

                // Clear errors
                this.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                this.querySelectorAll('.invalid-feedback').forEach(el => {
                    el.textContent = '';
                    el.style.display = 'none';
                });

                try {
                    const formData = new FormData(this);

                    console.log('Sending request to:', this.action);
                    console.log('FormData:', Object.fromEntries(formData));

                    const response = await axios.post(this.action, formData, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Content-Type': 'multipart/form-data'
                        }
                    });

                    console.log('Response:', response.data);

                    if (response.data.success) {
                        window.showToast('success', response.data.message || 'Updated successfully!');
                    } else {
                        window.showToast('error', response.data.message || 'Failed to update!');
                    }
                } catch (error) {
                    console.error('Submission Error:', error);
                    console.error('Error Response:', error.response);

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
                        window.showToast('error', Object.values(errors).flat()[0]);
                    } else {
                        window.showToast('error', error.response?.data?.message || 'Something went wrong!');
                    }
                } finally {
                    submitBtn.disabled = false;
                    spinner.classList.add('d-none');
                    btnText.textContent = 'Save Changes';
                }

                return false;
            });

            console.log('How It Works form handler attached');
        };

        // CMS Manager Class
        class CmsManager {
            constructor() {
                this.currentSection = 'hero';
                this.initTabs();
            }

            initTabs() {
                document.querySelectorAll("#cms-tabs .nav-link").forEach(tab => {
                    tab.addEventListener("click", (e) => this.handleTabClick(e));
                });
            }

            handleTabClick(e) {
                e.preventDefault();
                const tab = e.currentTarget;
                const section = tab.getAttribute("data-section");
                const title = tab.getAttribute("data-title");
                const breadcrumb = tab.getAttribute("data-breadcrumb");

                this.currentSection = section;
                this.updateActiveTab(tab);
                this.updatePageInfo(title, breadcrumb);
                this.loadSection(section);
            }

            updateActiveTab(activeTab) {
                document.querySelectorAll("#cms-tabs .nav-link").forEach(tab => {
                    tab.classList.remove("active");
                });
                activeTab.classList.add("active");
            }

            updatePageInfo(title, breadcrumb) {
                document.getElementById("dynamic-title").textContent = title;
                document.getElementById("dynamic-breadcrumb").textContent = breadcrumb;
            }

            async loadSection(section) {
                const contentDiv = document.getElementById("dynamic-content");
                contentDiv.innerHTML = `
            <div class="loading-overlay">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading content...</p>
                </div>
            </div>
        `;

                try {
                    console.log('Loading section:', section);

                    const response = await axios.get(route('cms.section', {
                        section: section
                    }), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    console.log('Section loaded:', section);

                    contentDiv.innerHTML = response.data;

                    // CRITICAL: Wait for DOM to update, then run inline scripts
                    await new Promise(resolve => setTimeout(resolve, 100));

                    // Execute any inline scripts in the loaded content
                    const scripts = contentDiv.querySelectorAll('script');
                    scripts.forEach(oldScript => {
                        const newScript = document.createElement('script');
                        Array.from(oldScript.attributes).forEach(attr => {
                            newScript.setAttribute(attr.name, attr.value);
                        });
                        newScript.textContent = oldScript.textContent;
                        oldScript.parentNode.replaceChild(newScript, oldScript);
                    });

                    console.log(`Section ${section} scripts executed`);
                } catch (error) {
                    console.error('Error loading section:', error);
                    contentDiv.innerHTML = `
                <div class="alert alert-danger fade-in">
                    <h4>Error Loading Content</h4>
                    <p>Failed to load ${section} section. Please try again.</p>
                    <button onclick="location.reload()" class="btn btn-primary">Reload Page</button>
                </div>
            `;
                }
            }
        }

        // Initialize CMS Manager
        document.addEventListener("DOMContentLoaded", function() {
            console.log('CMS Manager initializing...');
            window.cmsManager = new CmsManager();

            // Initialize first section if active
            const activeTab = document.querySelector("#cms-tabs .nav-link.active");
            if (activeTab) {
                const section = activeTab.getAttribute("data-section");
                console.log('Initial section:', section);

                if (section === 'hero' && typeof initHeroSection === 'function') {
                    initHeroSection();
                }
                // Note: how-it-works will initialize via inline script in blade
            }

            console.log('CMS Manager ready');
        });
    </script>
@endpush
