<!--APP-SIDEBAR-->
<div class="sticky">
    <div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
    <div class="app-sidebar" style="overflow: scroll">
        <div class="side-header">
            <a class="header-brand1" href="{{ route('dashboard') }}">
                <img src="{{ asset($settings->logo ?? 'default/logo.png') }}" class="header-brand-img desktop-logo"
                    alt="logo">
                <img src="{{ asset($settings->logo ?? 'default/logo.png') }}" class="header-brand-img toggle-logo"
                    alt="logo">
                <img src="{{ asset($settings->logo ?? 'default/logo.png') }}" class="header-brand-img light-logo"
                    alt="logo">
                <img src="{{ asset($settings->logo ?? 'default/logo.png') }}" class="header-brand-img light-logo1"
                    alt="logo">
            </a>
        </div>
        <div class="main-sidemenu">
            <div class="slide-left disabled" id="slide-left"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
                    width="24" height="24" viewBox="0 0 24 24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z" />
                </svg>
            </div>

            <ul class="side-menu mt-2">
                <li>
                    <h3>Menu</h3>
                </li>

                {{-- Dashboard --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('dashboard') ? 'has-link' : '' }}"
                        href="{{ route('dashboard') }}">
                        <i class="fa-solid fa-gauge-high"></i>
                        <span class="side-menu__label">Dashboard</span>
                    </a>
                </li>

                @can('property.list')
                {{-- Properties --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('property.index') ? 'has-link' : '' }}" data-bs-toggle="slide" href="#">
                        <i class="fa-solid fa-bed-pulse"></i>
                        <span class="side-menu__label">Manage Property</span>
                        <i class="angle fa fa-angle-right ms-auto"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('property.index') }}" class="slide-item">Properties</a></li>
                    </ul>
                </li>
                @endcan


                {{-- Tenants --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('tenants.index') ? 'has-link' : '' }}"
                        href="{{ route('tenants.index') }}">
                        <i class="fa fa-users"></i>
                        <span class="side-menu__label">Tenants</span>
                    </a>
                </li>

                {{-- Applications --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('#') ? 'has-link' : '' }}"
                        href="{{ route('tenants.applications.index') }}">
                        <i class="fa-solid fa-clipboard-list"></i>
                        <span class="side-menu__label">Applications</span>
                    </a>
                </li>

                {{-- Leases and files --}}
                <li class="slide">

                    <a class="side-menu__item {{ request()->routeIs('seasons.list') ? 'has-link' : '' }}" data-bs-toggle="slide" href="#">
                       <i class="fa-solid fa-file"></i>
                        <span class="side-menu__label">Manage Leases & Files</span>
                        <i class="angle fa fa-angle-right ms-auto"></i>
                    </a>
                    <ul class="slide-menu">
                        @can('seasons.list')
                        <li><a href="{{ route('seasons.list') }}" class="slide-item">Seasons</a></li>
                        @endcan
                        @can('lease.template.list')
                        <li><a href="{{route('lease-templates.index')}}" class="slide-item">Lease Templates</a></li>
                        @endcan
                        @can('lease.list')
                        <li><a href="{{route('leases.index')}}" class="slide-item">Leases</a></li>
                        @endcan
                    </ul>
                </li>

                {{-- Income --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('#') ? 'has-link' : '' }}"
                        href="">
                        <i class="fa-solid fa-chart-line"></i>
                        <span class="side-menu__label">Income</span>
                    </a>
                </li>

                

                {{-- Maintanence --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('maintanance.index') ? 'has-link' : '' }}"
                        href="{{ route('maintanance.index') }}">
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                        <span class="side-menu__label">Maintanence</span>
                    </a>
                </li>

                {{-- FAQ   created by Rayhann --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('faq.*') ? 'has-link' : '' }}"
                    href="{{ route('faq.index') }}">
                        <i class="fa-solid fa-circle-question"></i>
                        <span class="side-menu__label">FAQ</span>
                    </a>
                </li>


                {{-- Messaging --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('messaging.index') ? 'has-link' : '' }}"
                        href="{{ route('messaging.index') }}">
                        <i class="fa-solid fa-message"></i>
                        <span class="side-menu__label">Messaging</span>
                    </a>
                </li>

                {{-- Items Management --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('items.*') ? 'has-link' : '' }}"
                    href="{{ route('items.index') }}">
                        <i class="fa-solid fa-box"></i>
                        <span class="side-menu__label">Listing</span>
                    </a>
                </li>


                @can('cms.view')
                {{-- Frontend --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('cms.index') ? 'has-link' : '' }}" href="{{ route('cms.index') }}">
                        <i class="fa-solid fa-layer-group"></i>
                        <span class="side-menu__label">Platform</span>
                    </a>
                </li>
                @endcan


                {{-- Reports --}}
                <li class="slide">
                    
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <i class="fa-solid fa-explosion"></i>
                        <span class="side-menu__label">Reports</span>
                        <i class="angle fa fa-angle-right ms-auto"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('reports.property.index') }}" class="slide-item {{ request()->routeIs('reports.property.*') ? 'active' : '' }}">Property Reports</a></li>
                        <li><a href="{{ route('reports.rent.index') }}" class="slide-item {{ request()->routeIs('reports.rent.*') ? 'active' : '' }}">Rent Reports</a></li>
                        <li><a href="#" class="slide-item">Tenant Reports</a></li>
                        <li><a href="#" class="slide-item">Lease Reports</a></li>
                    </ul>
                </li>

                @can('user-management.users.list' )
                {{-- User Management --}}
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <i class="fa-solid fa-users-gear"></i>
                        <span class="side-menu__label">User Management</span>
                        <i class="angle fa fa-angle-right ms-auto"></i>
                    </a>
                    <ul class="slide-menu">
                        @can('user-management.users.list')
                        <li><a href="{{ route('user-management.users.index') }}" class="slide-item">Users</a></li>
                        @endcan
                        @can('user-management.roles.list')
                        <li><a href="{{ route('user-management.roles.index') }}" class="slide-item">Roles</a></li>
                        @endcan
                        @can('user-management.permissions.list')
                        <li><a href="{{ route('user-management.permissions.index') }}" class="slide-item">Permissions</a></li>
                        @endcan
                    </ul>
                </li>
                @endcan
                {{-- Settings --}}
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <i class="fa fa-cog"></i>
                        <span class="side-menu__label">Settings</span>
                        <i class="angle fa fa-angle-right ms-auto"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('setting.general.index') }}" class="slide-item">General Settings</a>
                        </li>
                        <li><a href="{{ route('setting.profile.index') }}" class="slide-item">Profile Settings</a>
                        </li>
                        <li><a href="{{ route('social.profile.index') }}" class="slide-item">Social Profile</a></li>
                    </ul>
                </li>
            </ul>


            <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
                    width="24" height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z" />
                </svg>
            </div>
        </div>
    </div>
</div>
<!--/APP-SIDEBAR-->


{{-- sidebar style --}}
<style>
    .slide i {
        margin-top: 8px;
    }

    /* Base menu item styling */
    .side-menu__item {
        display: flex;
        align-items: center;
        padding: 10px 15px;
        color: #333;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        border-radius: 6px;
        margin: 2px 8px;
    }

    .side-menu__item:hover {
        background-color: rgba(217, 166, 0, 0.1);
        color: #D9A600;
    }

    /* Active link styling */
    .side-menu__item.has-link {
        background-color: rgba(217, 166, 0, 0.15);
        color: #D9A600;
        font-weight: 600;
        border-left: 3px solid #D9A600;
    }

    .side-menu__item.has-link:hover {
        background-color: rgba(217, 166, 0, 0.2);
        color: #D9A600;
    }

    /* Icon alignment fix */
    .side-menu__item i,
    .side-menu__item svg {
        width: 22px;
        height: 22px;
        flex-shrink: 0;
        display: inline-block;
        text-align: center;
        margin-right: 10px;
        color: inherit;
    }

    /* Active link icon color */
    .side-menu__item.has-link i {
        color: #D9A600;
    }

    /* Label */
    .side-menu__label {
        flex: 1;
        display: inline-block;
    }

    /* Submenu items */
    .slide-menu {
        padding-left: 20px;
    }

    .slide-menu .slide-item {
        display: block;
        padding: 8px 15px;
        color: #555;
        font-size: 14px;
        text-decoration: none;
        transition: all 0.3s ease;
        border-radius: 4px;
        margin: 2px 0;
    }

    .slide-menu .slide-item:hover {
        background-color: rgba(217, 166, 0, 0.1);
        color: #D9A600;
    }

    /* Active submenu item */
    .slide-menu .slide-item.active {
        background-color: rgba(217, 166, 0, 0.15);
        color: #D9A600;
        font-weight: 500;
    }

    /* Optional: heading styling */
    .side-menu h3 {
        font-size: 13px;
        text-transform: uppercase;
        margin: 20px 15px 10px;
        color: #777;
        letter-spacing: 0.5px;
    }

    /* Settings menu arrow color when active */
    .side-menu__item[aria-expanded="true"] .angle,
    .side-menu__item.active .angle {
        color: #D9A600;
        transform: rotate(90deg);
    }

    /* Settings menu when expanded */
    .side-menu__item[aria-expanded="true"] {
        background-color: rgba(217, 166, 0, 0.1);
        color: #D9A600;
    }
</style>
