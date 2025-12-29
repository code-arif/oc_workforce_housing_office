@extends('backend.app')

@section('title', 'Manage Property')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                {{-- PAGE-HEADER --}}
                <div class="page-header">
                    <div>
                        <h1 class="page-title" id="dynamic-title">Properties</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Manage Property</a></li>
                            <li class="breadcrumb-item active" aria-current="page" id="dynamic-breadcrumb">Properties</li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    <!-- Main Content Area -->
                    <div class="col-lg-10 col-xl-10 col-md-10 col-sm-12">
                        <div id="dynamic-content">
                            @include('backend.layouts.properties.sections.properties', [
                                'properties' => $properties ?? []
                            ])
                        </div>
                    </div>

                    <!-- Right Sidebar Tabs -->
                    <div class="col-lg-2 col-xl-2 col-md-2 col-sm-12">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="card-title text-white mb-0">Property Management</h5>
                            </div>
                            <div class="card-body p-0" style="height: 70vh; overflow-x:auto">
                                <div class="nav flex-column nav-pills" id="property-tabs" role="tablist">
                                    <h6 class="px-3 pt-2 pb-2 mb-0 text-uppercase text-muted bg-light" style="font-size: 0.75rem;">
                                        Property Hierarchy
                                    </h6>

                                    <a class="nav-link active" id="properties-tab" href="javascript:void(0);" data-section="properties"
                                        data-title="Properties" data-breadcrumb="Properties">
                                        <i class="fa-solid fa-home me-2"></i> Properties
                                    </a>

                                    <a class="nav-link" id="property-types-tab" href="javascript:void(0);"
                                        data-section="property-types" data-title="Property Types"
                                        data-breadcrumb="Property Types">
                                        <i class="fa-solid fa-layer-group me-2"></i> Property Types
                                    </a>

                                    <a class="nav-link" id="units-tab" href="javascript:void(0);"
                                        data-section="units" data-title="Units"
                                        data-breadcrumb="Units">
                                        <i class="fa-solid fa-building me-2"></i> Units
                                    </a>

                                    <a class="nav-link" id="rooms-tab" href="javascript:void(0);"
                                        data-section="rooms" data-title="Rooms"
                                        data-breadcrumb="Rooms">
                                        <i class="fa-solid fa-door-open me-2"></i> Rooms
                                    </a>

                                    <a class="nav-link" id="beds-tab" href="javascript:void(0);"
                                        data-section="beds" data-title="Beds"
                                        data-breadcrumb="Beds">
                                        <i class="fa-solid fa-bed me-2"></i> Beds
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
    <script src="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/js/iziToast.min.css"></script>
    <script src="https://cdn.jsdelivr.net/npm/izitoast@1.4.0/dist/js/iziToast.min.js"></script>

    <script>
        // Configuration
        const BASE_URL = "{{ url('admin/property') }}";

        // CSRF Token setup
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (csrfToken) {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
        }

        // Route helper
        window.route = function(name, params = {}) {
            const routes = {
                'property.index': BASE_URL,
                'property.section': BASE_URL + '/section/' + (params.section || params),
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

        // Property Manager Class
        class PropertyManager {
            constructor() {
                this.currentSection = 'properties';
                this.initTabs();
            }

            initTabs() {
                document.querySelectorAll("#property-tabs .nav-link").forEach(tab => {
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
                document.querySelectorAll("#property-tabs .nav-link").forEach(tab => {
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

                    const response = await axios.get(window.route('property.section', {
                        section: section
                    }), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    console.log('Section loaded:', section);

                    contentDiv.innerHTML = response.data;

                    // Wait for DOM to update, then run inline scripts
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

        // Initialize Property Manager
        document.addEventListener("DOMContentLoaded", function() {
            console.log('Property Manager initializing...');
            window.propertyManager = new PropertyManager();

            const activeTab = document.querySelector("#property-tabs .nav-link.active");
            if (activeTab) {
                const section = activeTab.getAttribute("data-section");
                console.log('Initial section:', section);

                if (section === 'properties' && typeof initPropertiesSection === 'function') {
                    initPropertiesSection();
                }
                // Note: how-it-works will initialize via inline script in blade
            }
            console.log('Property Manager ready');
        });
    </script>
@endpush
