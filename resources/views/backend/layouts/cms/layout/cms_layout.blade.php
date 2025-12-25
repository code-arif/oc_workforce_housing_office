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
                            @include('backend.layouts.cms.sections.hero', [
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

                                    <a class="nav-link" id="about-tab" href="javascript:void(0);" data-section="about"
                                        data-title="About Section" data-breadcrumb="About">
                                        <i class="fe fe-info me-2"></i> About Section
                                    </a>

                                    <a class="nav-link" id="services-tab" href="javascript:void(0);" data-section="services"
                                        data-title="Services Section" data-breadcrumb="Services">
                                        <i class="fe fe-briefcase me-2"></i> Services Section
                                    </a>

                                    <a class="nav-link" id="testimonials-tab" href="javascript:void(0);"
                                        data-section="testimonials" data-title="Testimonials"
                                        data-breadcrumb="Testimonials">
                                        <i class="fe fe-message-square me-2"></i> Testimonials
                                    </a>

                                    <a class="nav-link" id="contact-tab" href="javascript:void(0);" data-section="contact"
                                        data-title="Contact Section" data-breadcrumb="Contact">
                                        <i class="fe fe-phone me-2"></i> Contact Section
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
            };
            return routes[name] || BASE_URL;
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
                    const response = await axios.get(route('cms.section', {
                        section: section
                    }), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    contentDiv.innerHTML = `<div class="fade-in">${response.data}</div>`;

                    // Initialize section scripts after content loaded
                    await this.initializeSectionScripts(section);
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

            async initializeSectionScripts(section) {
                // Wait for DOM to be ready
                await new Promise(resolve => setTimeout(resolve, 100));

                if (section === 'hero' && typeof initHeroSection === 'function') {
                    initHeroSection();
                }
                if (section === 'how-it-works' && typeof initHowItWorksSection === 'function') {
                    initHowItWorksSection();
                }
            }
        }

        // Initialize CMS Manager
        document.addEventListener("DOMContentLoaded", function() {
            window.cmsManager = new CmsManager();

            // Initialize first section if active
            const activeTab = document.querySelector("#cms-tabs .nav-link.active");
            if (activeTab) {
                const section = activeTab.getAttribute("data-section");
                if (section === 'hero' && typeof initHeroSection === 'function') {
                    initHeroSection();
                }
            }
        });
    </script>
@endpush
