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
                        <a class="side-menu__item {{ request()->routeIs('property.index') ? 'has-link' : '' }}"
                            href="{{ route('property.index') }}">
                            <i class="fa-solid fa-bed-pulse"></i>
                            <span class="side-menu__label">Manage Property</span>
                        </a>
                    </li>
                @endcan

                {{-- Applications --}}
                @can('applications.list')
                    <li class="slide">
                        <a class="side-menu__item {{ request()->routeIs('tenants.applications.*') ? 'has-link' : '' }}"
                            href="{{ route('tenants.applications.index') }}">
                            <i class="fa-solid fa-clipboard-list"></i>
                            <span class="side-menu__label">Applications</span>
                        </a>
                    </li>
                @endcan

                {{-- Tenants --}}
                @can('tenant.list')
                    <li class="slide">
                        <a class="side-menu__item {{ request()->routeIs('tenants.index') ? 'has-link' : '' }}"
                            href="{{ route('tenants.index') }}">
                            <i class="fa fa-users"></i>
                            <span class="side-menu__label">Tenants</span>
                        </a>
                    </li>
                @endcan

                {{-- Leases and files --}}
                @canany(['lease.list', 'lease.template.list', 'seasons.list'])
                    <li class="slide">
                        <a class="side-menu__item {{ request()->routeIs('seasons.*', 'leases.*', 'lease-templates.*') ? 'has-link' : '' }}"
                            data-bs-toggle="slide" href="#">
                            <i class="fa-solid fa-file"></i>
                            <span class="side-menu__label">Manage Leases & Files</span>
                            <i class="angle fa fa-angle-right ms-auto"></i>
                        </a>

                        <ul class="slide-menu">
                            @can('seasons.list')
                                <li>
                                    <a href="{{ route('seasons.list') }}"
                                        class="slide-item {{ request()->routeIs('seasons.*') ? 'active' : '' }}">Seasons</a>
                                </li>
                            @endcan

                            @can('lease.template.list')
                                <li>
                                    <a href="{{ route('lease-templates.index') }}"
                                        class="slide-item {{ request()->routeIs('lease-templates.*') ? 'active' : '' }}">Lease
                                        Templates</a>
                                </li>
                            @endcan

                            @can('lease.list')
                                <li>
                                    <a href="{{ route('leases.index') }}"
                                        class="slide-item {{ request()->routeIs('leases.*') ? 'active' : '' }}">Leases</a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcanany


                {{-- Income --}}
                @can('income')
                    <li class="slide">
                        <a class="side-menu__item {{ request()->routeIs('invoices.index') ? 'has-link' : '' }}"
                            href="{{ route('invoices.index') }}">
                            <i class="fa-solid fa-chart-line"></i>
                            <span class="side-menu__label">Income</span>
                        </a>
                    </li>
                @endcan



                @can('cms.view')
                    {{-- Frontend --}}
                    <li class="slide">

                        <a class="side-menu__item {{ request()->routeIs('cms.*', 'messaging.*', 'maintanance.*', 'items.*', 'faq.*') ? 'has-link' : '' }}"
                            data-bs-toggle="slide" href="#">
                            <i class="fa-solid fa-layer-group"></i>
                            <span class="side-menu__label">Platforms</span>
                            <i class="angle fa fa-angle-right ms-auto"></i>
                        </a>
                        <ul class="slide-menu">
                            <li><a href="{{ route('cms.index') }}"
                                    class="slide-item {{ request()->routeIs('cms.*') ? 'active' : '' }}">CMS</a></li>
                            <li><a href="{{ route('messaging.index') }}"
                                    class="slide-item {{ request()->routeIs('messaging.*') ? 'active' : '' }}">Messaging</a>
                            </li>
                            {{-- Maintanence --}}
                            <li>
                                <a class="slide-item {{ request()->routeIs('maintanance.*') ? 'active' : '' }}"
                                    href="{{ route('maintanance.index') }}"> Maintanence
                                </a>
                            </li>
                            {{-- Items & FAQ --}}
                            <li><a href="{{ route('items.index') }}"
                                    class="slide-item {{ request()->routeIs('items.*') ? 'active' : '' }}">Item Lists</a>
                            </li>
                            <li><a href="{{ route('faq.index') }}"
                                    class="slide-item {{ request()->routeIs('faq.*') ? 'active' : '' }}">FAQ</a></li>
                        </ul>
                    </li>
                @endcan


                {{-- Reports --}}
                @can('reports.list')
                    <li class="slide">
                        <a class="side-menu__item {{ request()->routeIs('reports.*') ? 'has-link' : '' }}"
                            data-bs-toggle="slide" href="#">
                            <i class="fa-solid fa-explosion"></i>
                            <span class="side-menu__label">Reports</span>
                            <i class="angle fa fa-angle-right ms-auto"></i>
                        </a>
                        <ul class="slide-menu">
                            <li><a href="{{ route('reports.property.index') }}"
                                    class="slide-item {{ request()->routeIs('reports.property.*') ? 'active' : '' }}">Property
                                    Reports</a></li>
                            <li><a href="{{ route('reports.rent.index') }}"
                                    class="slide-item {{ request()->routeIs('reports.rent.*') ? 'active' : '' }}">Rent
                                    Reports</a></li>
                            <li><a href="{{ route('reports.rent-collection.index') }}"
                                    class="slide-item {{ request()->routeIs('reports.rent-collection.*') ? 'active' : '' }}">Rent
                                    Collection</a></li>
                            <li><a href="{{ route('reports.tenant.index') }}"
                                    class="slide-item {{ request()->routeIs('reports.tenant.*') ? 'active' : '' }}">Tenant
                                    Reports</a></li>
                            {{-- <li><a href="#" class="slide-item">Lease Reports</a></li> --}}
                        </ul>
                    </li>
                @endcan

                @canany(['user-management.users.list', 'user-management.roles.list',
                    'user-management.permissions.list'])
                    {{-- User Management --}}
                    <li class="slide">
                        <a class="side-menu__item {{ request()->routeIs('user-management.*') ? 'has-link' : '' }}"
                            data-bs-toggle="slide" href="#">
                            <i class="fa-solid fa-users-gear"></i>
                            <span class="side-menu__label">User Management</span>
                            <i class="angle fa fa-angle-right ms-auto"></i>
                        </a>
                        <ul class="slide-menu">
                            @can('user-management.users.list')
                                <li><a href="{{ route('user-management.users.index') }}"
                                        class="slide-item {{ request()->routeIs('user-management.users.*') ? 'active' : '' }}">Users</a>
                                </li>
                            @endcan
                            @can('user-management.roles.list')
                                <li><a href="{{ route('user-management.roles.index') }}"
                                        class="slide-item {{ request()->routeIs('user-management.roles.*') ? 'active' : '' }}">Roles</a>
                                </li>
                            @endcan
                            @can('user-management.permissions.list')
                                <li><a href="{{ route('user-management.permissions.index') }}"
                                        class="slide-item {{ request()->routeIs('user-management.permissions.*') ? 'active' : '' }}">Permissions</a>
                                </li>
                            @endcan
                        </ul>
                    </li>
                @endcan

                {{-- Activity logs --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('activity-logs.*') ? 'has-link' : '' }}"
                        href="{{ route('activity-logs.index') }}">
                        <i class="fa-solid fa-desktop"></i>
                        <span class="side-menu__label">Activity Logs</span>
                    </a>
                </li>

                {{-- Settings --}}
                @can('settings')
                    <li class="slide">
                        <a class="side-menu__item {{ request()->routeIs('setting.*', 'social.profile.*') ? 'has-link' : '' }}"
                            data-bs-toggle="slide" href="#">
                            <i class="fa fa-cog"></i>
                            <span class="side-menu__label">Settings</span>
                            <i class="angle fa fa-angle-right ms-auto"></i>
                        </a>
                        <ul class="slide-menu">
                            <li><a href="{{ route('setting.general.index') }}"
                                    class="slide-item {{ request()->routeIs('setting.general.*') ? 'active' : '' }}">General
                                    Settings</a>
                            </li>
                            <li><a href="{{ route('setting.mail.index') }}"
                                    class="slide-item {{ request()->routeIs('setting.mail.*') ? 'active' : '' }}">Mail
                                    Settings</a>
                            </li>
                            <li><a href="{{ route('setting.profile.index') }}"
                                    class="slide-item {{ request()->routeIs('setting.profile.*') ? 'active' : '' }}">Profile
                                    Settings</a>
                            </li>
                            <li><a href="{{ route('social.profile.index') }}"
                                    class="slide-item {{ request()->routeIs('social.profile.*') ? 'active' : '' }}">Social
                                    Profile</a></li>
                            <li><a href="{{ route('setting.mail-templates.index') }}"
                                    class="slide-item {{ request()->routeIs('setting.mail-templates.*') ? 'active' : '' }}">Mail
                                    Templates</a></li>
                            <li><a href="{{ route('setting.stripe.index') }}"
                                    class="slide-item {{ request()->routeIs('setting.stripe.*') ? 'active' : '' }}">Stripe
                                    Settings</a></li>
                            <li><a href="{{ route('system-monitor.index') }}"
                                    class="slide-item {{ request()->routeIs('system-monitor.*') ? 'active' : '' }}">System
                                    Monitor</a></li>
                        </ul>
                    </li>
                @endcan
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

<script>
    // Auto-expand parent slide menus when a sub-item is already marked active by Blade
    // Uses vanilla JS to avoid jQuery dependency (sidebar partial renders before scripts load)
    (function() {
        'use strict';

        function expandActiveParents() {
            document.querySelectorAll('.slide-menu .slide-item.active').forEach(function(item) {
                var parentUl = item.closest('.slide-menu');
                var parentLi = item.closest('.slide');
                if (parentLi && !parentLi.classList.contains('is-expanded')) {
                    parentUl.classList.add('open');
                    parentUl.style.display = '';
                    parentLi.classList.add('is-expanded');
                    var toggle = parentLi.querySelector('[data-bs-toggle="slide"]');
                    if (toggle) toggle.setAttribute('aria-expanded', 'true');
                }
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', expandActiveParents);
        } else {
            expandActiveParents();
        }
        setTimeout(expandActiveParents, 300);
    })();
</script>


{{-- sidebar style --}}
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

    /* Premium Sidebar Styling */
    .app-sidebar {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        box-shadow: 0 4px 24px 0 rgba(34, 41, 47, 0.08);
    }

    .side-menu {
        padding-top: 15px;
    }

    /* Section Headers */
    .side-menu h3 {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        margin: 25px 20px 10px;
        color: #9CA3AF;
    }

    /* Base Menu Item */
    .side-menu__item {
        display: flex;
        align-items: center;
        padding: 12px 18px;
        color: #4B5563;
        font-size: 14.5px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 8px;
        margin: 4px 12px;
        border: 1px solid transparent;
        position: relative;
        overflow: hidden;
    }

    /* Hover effect */
    .side-menu__item:hover {
        background: linear-gradient(118deg, rgba(217, 166, 0, 0.08), rgba(217, 166, 0, 0.02));
        color: #D9A600;
        transform: translateX(4px);
    }

    /* Active Link */
    .side-menu__item.has-link,
    .side-menu__item[aria-expanded="true"] {
        background: linear-gradient(118deg, rgba(217, 166, 0, 0.12), rgba(217, 166, 0, 0.03));
        color: #D9A600;
        font-weight: 600;
        border-color: rgba(217, 166, 0, 0.2);
        box-shadow: 0 2px 6px 0 rgba(217, 166, 0, 0.1);
    }

    /* Active Indication line */
    .side-menu__item.has-link::before {
        content: '';
        position: absolute;
        left: 0;
        top: 10%;
        height: 80%;
        width: 4px;
        background-color: #D9A600;
        border-radius: 0 4px 4px 0;
    }

    /* Default Icons */
    .side-menu__item i:not(.angle),
    .side-menu__item svg {
        width: 24px;
        height: 24px;
        font-size: 18px;
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        color: #6B7280;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        margin-top: 0;
    }

    /* Active Icons */
    .side-menu__item:hover i:not(.angle),
    .side-menu__item.has-link i:not(.angle),
    .side-menu__item[aria-expanded="true"] i:not(.angle) {
        color: #D9A600;
        transform: scale(1.1);
    }

    /* Dropdown Arrow */
    .side-menu__item .angle {
        margin-left: auto;
        font-size: 14px;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        color: #9CA3AF;
        margin-top: 0;
    }

    .side-menu__item[aria-expanded="true"] .angle,
    .side-menu__item.active .angle {
        color: #D9A600;
        transform: rotate(90deg);
    }

    /* Submenus */
    .slide-menu {
        padding-left: 20px;
        margin: 4px 0;
        position: relative;
    }

    /* Submenu Item */
    .slide-menu .slide-item {
        display: flex;
        align-items: center;
        padding: 8px 16px 8px 30px;
        /* added left padding for the indicator */
        color: #6B7280;
        font-size: 13.5px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        border-radius: 6px;
        margin: 2px 12px 2px 6px;
        position: relative;
    }

    /* Clean up any default theme injected icons or pseudo elements */
    .app-sidebar .side-menu .slide-menu .slide-item::after,
    .app-sidebar .side-menu .slide-menu .slide-item i {
        display: none !important;
        content: none !important;
    }

    /* Submenu Sleek Dash Indicator - Enforced */
    .app-sidebar .side-menu .slide-menu .slide-item::before {
        content: '' !important;
        position: absolute !important;
        left: 12px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        width: 6px !important;
        height: 2px !important;
        background-color: #D1D5DB !important;
        background-image: none !important;
        border: none !important;
        border-radius: 2px !important;
        transition: all 0.2s ease !important;
        font-family: inherit !important;
        font-size: 0 !important;
    }

    .app-sidebar .side-menu .slide-menu .slide-item:hover {
        background-color: rgba(217, 166, 0, 0.05);
        color: #D9A600;
        transform: translateX(4px);
    }

    /* Expand the dash on hover/active */
    .app-sidebar .side-menu .slide-menu .slide-item:hover::before,
    .app-sidebar .side-menu .slide-menu .slide-item.active::before {
        background-color: #D9A600 !important;
        width: 10px !important;
    }

    /* Active Submenu Item */
    .slide-menu .slide-item.active {
        color: #D9A600;
        font-weight: 600;
        background-color: rgba(217, 166, 0, 0.08);
    }
</style>
