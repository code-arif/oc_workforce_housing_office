@extends('backend.app')

@section('title', 'Dashboard')

@php
    $chartData = $properties
        ->map(function ($p) {
            return [
                'name' => \Illuminate\Support\Str::limit($p->name, 14),
                'occ' => (int) $p->occupied_beds,
                'avail' => (int) $p->available_beds,
                'rate' => (float) $p->occupancy_rate,
            ];
        })
        ->values()
        ->toArray();

    $adminName = auth()->user()->name ?? 'Admin';
    $firstName = explode(' ', $adminName)[0];
@endphp

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500;600&display=swap"
        rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        :root {
            --ink-900: #0a0f1e;
            --ink-800: #111827;
            --ink-700: #1f2937;
            --ink-600: #374151;
            --ink-500: #4b5563;
            --ink-400: #6b7280;
            --ink-300: #9ca3af;
            --ink-200: #d1d5db;
            --ink-100: #f3f4f6;
            --ink-50: #f9fafb;
            --accent: #6366f1;
            --accent-light: #818cf8;
            --accent-glow: rgba(99, 102, 241, 0.18);
            --accent-bg: #eef2ff;
            --teal: #0d9488;
            --teal-bg: #f0fdfa;
            --amber: #d97706;
            --amber-bg: #fffbeb;
            --rose: #e11d48;
            --rose-bg: #fff1f2;
            --emerald: #059669;
            --emerald-bg: #ecfdf5;
            --violet: #7c3aed;
            --violet-bg: #f5f3ff;
            --sky: #0284c7;
            --sky-bg: #f0f9ff;
            --canvas: #f4f6fb;
            --card: #ffffff;
            --border: #e5e7eb;
            --border-subtle: #f3f4f6;
            --shadow-xs: 0 1px 2px rgba(0, 0, 0, 0.05);
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.06), 0 1px 3px rgba(0, 0, 0, 0.04);
            --shadow-md: 0 8px 24px rgba(0, 0, 0, 0.08), 0 2px 8px rgba(0, 0, 0, 0.05);
            --r-sm: 10px;
            --r-md: 14px;
            --r-lg: 20px;
        }

        * {
            box-sizing: border-box;
        }

        /* body,
            * {
                font-family: 'Sora', sans-serif !important;
            } */

        .db-canvas {
            background: var(--canvas);
            min-height: 100vh;
            padding: 0 28px 60px;
        }

        /* HERO */
        .db-hero {
            position: relative;
            background: var(--ink-800);
            border-radius: 0 0 24px 24px;
            padding: 36px 36px 40px;
            margin: 0 -28px 28px;
            overflow: hidden;
        }

        .db-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 60% 80% at 75% 50%, rgba(99, 102, 241, 0.22) 0%, transparent 70%),
                radial-gradient(ellipse 40% 60% at 20% 80%, rgba(13, 148, 136, 0.15) 0%, transparent 60%);
        }

        .db-hero-grid {
            display: grid;
            grid-template-columns: 1fr auto;
            align-items: center;
            gap: 24px;
            position: relative;
            z-index: 1;
        }

        .db-greeting-eyebrow {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: var(--accent-light);
            margin-bottom: 6px;
        }

        .db-greeting-name {
            font-size: 28px;
            font-weight: 800;
            color: #fff;
            line-height: 1.15;
            letter-spacing: -0.6px;
            margin-bottom: 8px;
        }

        .db-greeting-sub {
            font-size: 13.5px;
            color: rgba(255, 255, 255, 0.5);
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .db-greeting-sub .dot-sep {
            color: rgba(255, 255, 255, 0.2);
        }

        .db-hero-stats {
            display: flex;
            gap: 2px;
        }

        .db-hero-stat {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 16px 22px;
            text-align: center;
            min-width: 100px;
        }

        .db-hero-stat:first-child {
            border-radius: var(--r-sm) 0 0 var(--r-sm);
        }

        .db-hero-stat:last-child {
            border-radius: 0 var(--r-sm) var(--r-sm) 0;
            border-left: none;
        }

        .db-hero-stat+.db-hero-stat {
            border-left: none;
            border-radius: 0;
        }

        .db-hero-stat .num {
            font-size: 22px;
            font-weight: 800;
            color: #fff;
            line-height: 1;
            font-family: 'IBM Plex Mono', monospace !important;
            letter-spacing: -0.5px;
        }

        .db-hero-stat .lbl {
            font-size: 10px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.4);
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        /* KPI */
        .kpi-strip {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 22px;
        }

        .kpi {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--r-md);
            padding: 20px 20px 18px;
            position: relative;
            overflow: hidden;
            transition: transform .2s, box-shadow .2s;
            box-shadow: var(--shadow-xs);
        }

        .kpi:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .kpi-accent-bar {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            border-radius: var(--r-md) var(--r-md) 0 0;
        }

        .kpi-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .kpi-icon-wrap {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
        }

        .kpi-trend {
            font-size: 11px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 100px;
        }

        .kpi-num {
            font-size: 30px;
            font-weight: 800;
            color: var(--ink-900);
            line-height: 1;
            letter-spacing: -1px;
            font-family: 'IBM Plex Mono', monospace !important;
            margin-bottom: 5px;
        }

        .kpi-label {
            font-size: 11.5px;
            font-weight: 500;
            color: var(--ink-400);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kpi-sparkline {
            position: absolute;
            bottom: 0;
            right: 0;
            opacity: 0.07;
            font-size: 70px;
            line-height: 1;
            padding-right: 8px;
        }

        /* MAIN GRID */
        .db-main {
            display: grid;
            grid-template-columns: 1fr 1fr 360px;
            gap: 16px;
            margin-bottom: 16px;
        }

        .db-bottom {
            display: grid;
            grid-template-columns: 1fr 1fr 360px;
            gap: 16px;
        }

        /* CARD BASE */
        .card-base {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--r-md);
            box-shadow: var(--shadow-xs);
            overflow: hidden;
        }

        .card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-subtle);
        }

        .card-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--ink-800);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-title-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            display: inline-block;
            flex-shrink: 0;
        }

        .card-action {
            font-size: 11.5px;
            font-weight: 600;
            color: var(--accent);
            text-decoration: none;
            padding: 4px 10px;
            border-radius: 6px;
            transition: background .15s;
        }

        .card-action:hover {
            background: var(--accent-bg);
            color: var(--accent);
        }

        .card-body {
            padding: 16px 20px;
        }

        /* PROPERTY TILES */
        .prop-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-bottom: 16px;
        }

        .prop-tile {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--r-md);
            overflow: hidden;
            cursor: pointer;
            transition: transform .2s, box-shadow .2s;
            box-shadow: var(--shadow-xs);
        }

        .prop-tile:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }

        .prop-tile-head {
            padding: 18px;
            background: var(--ink-800);
            position: relative;
            overflow: hidden;
        }

        .prop-tile-head::after {
            content: 'X';
            position: absolute;
            right: -8px;
            bottom: -12px;
            font-size: 64px;
            opacity: 0.05;
            color: #fff;
            font-family: serif;
        }

        .prop-tile-name {
            font-size: 14px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            padding-right: 60px;
        }

        .prop-tile-addr {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.4);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .prop-tile-occ-badge {
            position: absolute;
            top: 14px;
            right: 14px;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 9px;
            border-radius: 100px;
            font-family: 'IBM Plex Mono', monospace !important;
        }

        .prop-bar-wrap {
            margin-top: 14px;
            height: 3px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 100px;
            overflow: hidden;
        }

        .prop-bar-fill {
            height: 100%;
            border-radius: 100px;
        }

        .prop-tile-body {
            padding: 14px 16px 12px;
        }

        .prop-micro-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 6px;
            margin-bottom: 10px;
        }

        .prop-micro {
            text-align: center;
            padding: 8px 4px;
            background: var(--ink-50);
            border-radius: 8px;
        }

        .prop-micro-n {
            font-size: 15px;
            font-weight: 800;
            color: var(--ink-800);
            line-height: 1;
        }

        .prop-micro-l {
            font-size: 9.5px;
            color: var(--ink-400);
            margin-top: 2px;
            font-weight: 500;
        }

        .prop-money-chips {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 5px;
        }

        .prop-chip {
            padding: 6px 4px;
            border-radius: 7px;
            text-align: center;
        }

        .prop-chip .ca {
            font-size: 11px;
            font-weight: 700;
            line-height: 1;
            font-family: 'IBM Plex Mono', monospace !important;
        }

        .prop-chip .cl {
            font-size: 9px;
            color: var(--ink-400);
            margin-top: 1px;
            font-weight: 500;
        }

        .prop-tile-foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 9px 16px;
            border-top: 1px solid var(--border-subtle);
        }

        .prop-tile-foot a {
            font-size: 11.5px;
            font-weight: 600;
            color: var(--accent);
            text-decoration: none;
        }

        /* LIST ROWS */
        .list-row {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 11px 0;
            border-bottom: 1px solid var(--border-subtle);
            cursor: pointer;
            transition: background .15s;
        }

        .list-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .list-row:first-child {
            padding-top: 0;
        }

        .list-row:hover {
            background: var(--ink-50);
            margin: 0 -20px;
            padding-left: 20px;
            padding-right: 20px;
            border-radius: 8px;
        }

        .avi {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }

        .row-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--ink-800);
        }

        .row-meta {
            font-size: 11.5px;
            color: var(--ink-400);
            margin-top: 1px;
        }

        .pill {
            font-size: 10.5px;
            font-weight: 600;
            padding: 3px 9px;
            border-radius: 100px;
            flex-shrink: 0;
            white-space: nowrap;
        }

        .pill-amber {
            background: #fef3c7;
            color: #92400e;
        }

        .pill-red {
            background: #fee2e2;
            color: #991b1b;
        }

        .pill-green {
            background: #d1fae5;
            color: #065f46;
        }

        .pill-blue {
            background: #dbeafe;
            color: #1e40af;
        }

        /* ACTIVITY */
        .activity-item {
            display: flex;
            gap: 12px;
            padding: 10px 0;
            border-bottom: 1px solid var(--border-subtle);
        }

        .activity-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .activity-item:first-child {
            padding-top: 0;
        }

        .act-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .act-text {
            font-size: 12.5px;
            color: var(--ink-700);
            line-height: 1.45;
        }

        .act-text strong {
            color: var(--ink-900);
            font-weight: 600;
        }

        .act-time {
            font-size: 10.5px;
            color: var(--ink-400);
            margin-top: 2px;
            font-family: 'IBM Plex Mono', monospace !important;
        }

        /* QUICK ACTIONS */
        .qa-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .qa-btn {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 11px 13px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: transform .15s, box-shadow .15s;
            border: 1px solid transparent;
        }

        .qa-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .qa-btn i {
            font-size: 16px;
        }

        /* EMPTY */
        .mt-empty {
            text-align: center;
            padding: 28px 16px;
            color: var(--ink-400);
        }

        .mt-empty i {
            font-size: 24px;
            display: block;
            margin-bottom: 6px;
            opacity: 0.35;
        }

        .mt-empty p {
            font-size: 12.5px;
            margin: 0;
        }

        /* VIEW MORE */
        .view-more {
            display: block;
            text-align: center;
            font-size: 12px;
            font-weight: 600;
            color: var(--accent);
            text-decoration: none;
            margin-top: 12px;
            padding: 7px;
            border: 1px dashed var(--border);
            border-radius: 8px;
            transition: background .15s;
        }

        .view-more:hover {
            background: var(--accent-bg);
            border-color: var(--accent);
            color: var(--accent);
        }

        @keyframes blink {

            0%,
            100% {
                opacity: 1
            }

            50% {
                opacity: .4
            }
        }

        /* RESPONSIVE */
        @media (max-width: 1280px) {

            .db-main,
            .db-bottom {
                grid-template-columns: 1fr 1fr;
            }

            .kpi-strip {
                grid-template-columns: repeat(2, 1fr);
            }

            .prop-row {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 900px) {
            .db-canvas {
                padding: 0 14px 40px;
            }

            .db-hero {
                margin: 0 -14px 20px;
                padding: 24px 20px 28px;
            }

            .db-hero-grid {
                grid-template-columns: 1fr;
            }

            .db-hero-stats {
                flex-wrap: wrap;
            }

            .kpi-strip {
                grid-template-columns: repeat(2, 1fr);
            }

            .db-main,
            .db-bottom {
                grid-template-columns: 1fr;
            }

            .prop-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
    <div class="app-content main-content mt-0">
        <div class="db-canvas">

            {{-- HERO GREETING --}}
            <div class="db-hero">
                <div class="db-hero-grid">
                    <div>
                        <div class="db-greeting-eyebrow" id="greeting-label">Good Morning</div>
                        <div class="db-greeting-name">Welcome back, {{ $firstName }} 👋</div>
                        <div class="db-greeting-sub">
                            <span id="live-time"
                                style="font-family:'IBM Plex Mono',monospace;font-size:13px;color:rgba(255,255,255,0.6)">--:--:--</span>
                            <span class="dot-sep">·</span>
                            <span id="live-date"></span>
                            <span class="dot-sep">·</span>
                            <span style="color:rgba(255,255,255,0.5);">
                                <span
                                    style="width:6px;height:6px;background:#22c55e;border-radius:50%;display:inline-block;margin-right:5px;animation:blink 2s infinite;"></span>
                                System Online
                            </span>
                        </div>
                    </div>
                    <div class="db-hero-stats">
                        <div class="db-hero-stat">
                            <div class="num">{{ $totals['total_properties'] ?? 0 }}</div>
                            <div class="lbl">Properties</div>
                        </div>
                        <div class="db-hero-stat">
                            <div class="num">{{ $totals['total_beds'] ?? 0 }}</div>
                            <div class="lbl">Total Beds</div>
                        </div>
                        <div class="db-hero-stat">
                            <div class="num">{{ $totals['occupied_beds'] ?? 0 }}</div>
                            <div class="lbl">Occupied</div>
                        </div>
                        <div class="db-hero-stat">
                            @php $globalRate = $totals['total_beds'] > 0 ? round(($totals['occupied_beds']/$totals['total_beds'])*100) : 0; @endphp
                            <div class="num">{{ $globalRate }}%</div>
                            <div class="lbl">Occupancy</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- KPI STRIP --}}
            <div class="kpi-strip">
                <div class="kpi">
                    <div class="kpi-accent-bar" style="background:linear-gradient(90deg,#6366f1,#a5b4fc)"></div>
                    <div class="kpi-top">
                        <div class="kpi-icon-wrap" style="background:var(--accent-bg);color:var(--accent)"><i
                                class="bi bi-buildings-fill"></i></div>
                        <span class="kpi-trend" style="background:#eef2ff;color:#4338ca">Active</span>
                    </div>
                    <div class="kpi-num">{{ $totals['total_properties'] ?? 0 }}</div>
                    <div class="kpi-label">Total Properties</div>
                    <div class="kpi-sparkline">🏢</div>
                </div>
                <div class="kpi">
                    <div class="kpi-accent-bar" style="background:linear-gradient(90deg,#059669,#6ee7b7)"></div>
                    <div class="kpi-top">
                        <div class="kpi-icon-wrap" style="background:var(--emerald-bg);color:var(--emerald)"><i
                                class="bi bi-check-circle-fill"></i></div>
                        @php $avPct = $totals['total_beds'] > 0 ? round(($totals['available_beds']/$totals['total_beds'])*100) : 0; @endphp
                        <span class="kpi-trend"
                            style="background:var(--emerald-bg);color:var(--emerald)">{{ $avPct }}%</span>
                    </div>
                    <div class="kpi-num">{{ $totals['available_beds'] ?? 0 }}</div>
                    <div class="kpi-label">Available Beds</div>
                    <div class="kpi-sparkline">🛏</div>
                </div>
                <div class="kpi">
                    <div class="kpi-accent-bar" style="background:linear-gradient(90deg,#d97706,#fcd34d)"></div>
                    <div class="kpi-top">
                        <div class="kpi-icon-wrap" style="background:var(--amber-bg);color:var(--amber)"><i
                                class="bi bi-person-fill-check"></i></div>
                        @php $occPct = $totals['total_beds'] > 0 ? round(($totals['occupied_beds']/$totals['total_beds'])*100) : 0; @endphp
                        <span class="kpi-trend"
                            style="background:var(--amber-bg);color:var(--amber)">{{ $occPct }}%</span>
                    </div>
                    <div class="kpi-num">{{ $totals['occupied_beds'] ?? 0 }}</div>
                    <div class="kpi-label">Occupied Beds</div>
                    <div class="kpi-sparkline">👤</div>
                </div>
                <div class="kpi">
                    <div class="kpi-accent-bar" style="background:linear-gradient(90deg,#e11d48,#fda4af)"></div>
                    <div class="kpi-top">
                        <div class="kpi-icon-wrap" style="background:var(--rose-bg);color:var(--rose)"><i
                                class="bi bi-exclamation-circle-fill"></i></div>
                        <span class="kpi-trend" style="background:var(--rose-bg);color:var(--rose)">Action</span>
                    </div>
                    <div class="kpi-num">{{ $pendingApplications->count() + $unsignedLeases->count() }}</div>
                    <div class="kpi-label">Needs Attention</div>
                    <div class="kpi-sparkline">⚡</div>
                </div>
            </div>

            {{-- PROPERTY TILES --}}
            <div class="card-base" style="margin-bottom:16px;">
                <div class="card-head">
                    <div class="card-title">
                        <span class="card-title-dot" style="background:var(--accent)"></span>
                        Properties Overview
                        <span
                            style="font-size:11px;font-weight:500;color:var(--ink-400);font-family:'IBM Plex Mono',monospace;">({{ $properties->count() }}
                            active)</span>
                    </div>
                    <a href="{{ route('property.index') }}" class="card-action">View all →</a>
                </div>
                <div class="card-body">
                    @if ($properties->isEmpty())
                        <div class="mt-empty"><i class="bi bi-buildings"></i>
                            <p>No properties. <a href="{{ route('property.create') }}">Add one</a></p>
                        </div>
                    @else
                        <div class="prop-row">
                            @foreach ($properties as $property)
                                @php
                                    $oc = $property->occupancy_rate;
                                    $barColor = $oc >= 80 ? '#10b981' : ($oc >= 50 ? '#f59e0b' : '#ef4444');
                                    $badgeBg = $oc >= 80 ? '#d1fae5' : ($oc >= 50 ? '#fef3c7' : '#fee2e2');
                                    $badgeTxt = $oc >= 80 ? '#065f46' : ($oc >= 50 ? '#92400e' : '#991b1b');
                                @endphp
                                <div class="prop-tile"
                                    onclick="window.location='{{ route('property.show', $property->id) }}'">
                                    <div class="prop-tile-head">
                                        <div class="prop-tile-name">{{ $property->name }}</div>
                                        <div class="prop-tile-addr">{{ $property->address ?? 'No address provided' }}
                                        </div>
                                        <span class="prop-tile-occ-badge"
                                            style="background:{{ $badgeBg }};color:{{ $badgeTxt }}">{{ $oc }}%</span>
                                        <div class="prop-bar-wrap">
                                            <div class="prop-bar-fill"
                                                style="width:{{ $oc }}%;background:{{ $barColor }};"></div>
                                        </div>
                                    </div>
                                    <div class="prop-tile-body">
                                        <div class="prop-micro-grid">
                                            <div class="prop-micro">
                                                <div class="prop-micro-n">{{ $property->total_units }}</div>
                                                <div class="prop-micro-l">Units</div>
                                            </div>
                                            <div class="prop-micro">
                                                <div class="prop-micro-n">{{ $property->total_rooms }}</div>
                                                <div class="prop-micro-l">Rooms</div>
                                            </div>
                                            <div class="prop-micro">
                                                <div class="prop-micro-n">{{ $property->total_beds }}</div>
                                                <div class="prop-micro-l">Beds</div>
                                            </div>
                                        </div>
                                        <div class="prop-money-chips">
                                            <div class="prop-chip" style="background:#f0fdf4;">
                                                <div class="ca" style="color:#16a34a;">
                                                    ${{ number_format($property->total_paid ?? 0, 0) }}</div>
                                                <div class="cl">Paid</div>
                                            </div>
                                            <div class="prop-chip" style="background:#fef2f2;">
                                                <div class="ca" style="color:#dc2626;">
                                                    ${{ number_format($property->total_due ?? 0, 0) }}</div>
                                                <div class="cl">Due</div>
                                            </div>
                                            <div class="prop-chip" style="background:#eff6ff;">
                                                <div class="ca" style="color:#2563eb;">{{ $property->active_leases }}
                                                </div>
                                                <div class="cl">Leases</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="prop-tile-foot">
                                        <span
                                            style="font-size:11px;color:var(--ink-400);">{{ $property->occupied_beds }}/{{ $property->total_beds }}
                                            occupied</span>
                                        <a href="{{ route('property.show', $property->id) }}">Details →</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- MAIN 3-COL --}}
            <div class="db-main">

                {{-- Pending Applications --}}
                <div class="card-base">
                    <div class="card-head">
                        <div class="card-title">
                            <span class="card-title-dot" style="background:#f59e0b"></span>
                            Pending Applications
                        </div>
                        @if ($pendingApplications->count() > 0)
                            <span class="pill pill-amber">{{ $pendingApplications->count() }}</span>
                        @endif
                    </div>
                    <div class="card-body">
                        @forelse($pendingApplications as $i => $app)
                            @php
                                $n = $app->profile
                                    ? trim($app->profile->first_name . ' ' . $app->profile->last_name)
                                    : $app->email;
                                $ini = strtoupper(substr($n, 0, 2));
                                $avColors = ['#6366f1', '#8b5cf6', '#0891b2', '#059669', '#d97706', '#e11d48'];
                                $avBg = $avColors[$i % count($avColors)];
                            @endphp
                            <div class="list-row" onclick="window.location='{{ route('tenants.index') }}'">
                                <div class="avi" style="background:{{ $avBg }}">{{ $ini }}</div>
                                <div style="flex:1;min-width:0;">
                                    <div class="row-name">{{ $n }}</div>
                                    <div class="row-meta"><i
                                            class="bi bi-calendar3 me-1"></i>{{ $app->created_at->format('M d, Y') }}
                                    </div>
                                </div>
                                <span class="pill pill-amber">Pending</span>
                            </div>
                        @empty
                            <div class="mt-empty"><i class="bi bi-check2-all"></i>
                                <p>All caught up!</p>
                            </div>
                        @endforelse
                        @if ($pendingApplications->count() > 0)
                            <a href="{{ route('tenants.index') }}" class="view-more">See all applications →</a>
                        @endif
                    </div>
                </div>

                {{-- Unsigned Leases --}}
                <div class="card-base">
                    <div class="card-head">
                        <div class="card-title">
                            <span class="card-title-dot" style="background:#e11d48"></span>
                            Unsigned Leases
                        </div>
                        @if ($unsignedLeases->count() > 0)
                            <span class="pill pill-red">{{ $unsignedLeases->count() }}</span>
                        @endif
                    </div>
                    <div class="card-body">
                        @forelse($unsignedLeases as $i => $lease)
                            @php
                                $tn = $lease->tenant?->profile
                                    ? trim(
                                        $lease->tenant->profile->first_name . ' ' . $lease->tenant->profile->last_name,
                                    )
                                    : $lease->tenant?->email ?? 'N/A';
                                $ini = strtoupper(substr($tn, 0, 2));
                                $rC = ['#ef4444', '#f97316', '#e11d48', '#dc2626', '#b91c1c'];
                                $rb = $rC[$i % count($rC)];
                            @endphp
                            <div class="list-row" onclick="viewLease({{ $lease->id }})">
                                <div class="avi" style="background:{{ $rb }}">{{ $ini }}</div>
                                <div style="flex:1;min-width:0;">
                                    <div class="row-name">{{ $tn }}</div>
                                    <div class="row-meta">
                                        <i class="bi bi-buildings me-1"></i>{{ $lease->property?->name ?? 'N/A' }}
                                        · {{ $lease->created_at->format('M d') }}
                                    </div>
                                </div>
                                <span class="pill pill-red">Unsigned</span>
                            </div>
                        @empty
                            <div class="mt-empty"><i class="bi bi-file-earmark-check"></i>
                                <p>All leases signed!</p>
                            </div>
                        @endforelse
                        @if ($unsignedLeases->count() > 0)
                            <a href="{{ route('leases.index') }}" class="view-more">See all leases →</a>
                        @endif
                    </div>
                </div>

                {{-- Recent Maintenance list --}}
                <div class="card-base" style="display:flex;flex-direction:column;">
                    <div class="card-head">
                        <div class="card-title">
                            <span class="card-title-dot" style="background:#f97316"></span>
                            Recent Maintenance
                        </div>
                        <a href="{{ route('maintanance.index') }}" class="card-action">View all →</a>
                    </div>
                    <div class="card-body" style="padding:0;flex:1;overflow-y:auto;max-height:340px;">
                        @forelse($recentMaintenance as $req)
                            @php
                                $tenantName = $req->tenant?->profile
                                    ? trim($req->tenant->profile->first_name . ' ' . $req->tenant->profile->last_name)
                                    : $req->tenant?->email ?? 'N/A';

                                $statusConfig = [
                                    'pending' => ['color' => '#d97706', 'bg' => '#fef3c7', 'label' => 'Pending'],
                                    'in_progress' => [
                                        'color' => '#2563eb',
                                        'bg' => '#dbeafe',
                                        'label' => 'In Progress',
                                    ],
                                    'completed' => ['color' => '#059669', 'bg' => '#d1fae5', 'label' => 'Completed'],
                                    'rejected' => ['color' => '#dc2626', 'bg' => '#fee2e2', 'label' => 'Rejected'],
                                    'cancelled' => ['color' => '#6b7280', 'bg' => '#f3f4f6', 'label' => 'Cancelled'],
                                ];
                                $sc = $statusConfig[$req->status] ?? $statusConfig['pending'];

                                $categoryIcons = [
                                    'ac' => '❄️',
                                    'appliance' => '🔧',
                                    'electrical' => '⚡',
                                    'heat' => '🔥',
                                    'kitchen' => '🍳',
                                    'plumbing' => '🚿',
                                    'other' => '🔩',
                                ];
                                $icon = $categoryIcons[$req->category] ?? '🔩';
                            @endphp

                            <div class="list-row"
                                style="padding:12px 20px;border-bottom:1px solid var(--border-subtle);margin:0;">
                                {{-- Category Icon --}}
                                <div
                                    style="
                    width:36px;height:36px;border-radius:9px;
                    background:var(--amber-bg);
                    display:flex;align-items:center;justify-content:center;
                    font-size:17px;flex-shrink:0;">
                                    {{ $icon }}
                                </div>

                                {{-- Info --}}
                                <div style="flex:1;min-width:0;">
                                    <div class="row-name" style="display:flex;align-items:center;gap:6px;">
                                        {{ \Illuminate\Support\Str::limit($req->title ?? ucfirst($req->category), 28) }}
                                        @if ($req->is_urgent)
                                            <span
                                                style="font-size:9px;font-weight:700;padding:2px 6px;border-radius:100px;background:#fee2e2;color:#dc2626;letter-spacing:.4px;">URGENT</span>
                                        @endif
                                    </div>
                                    <div class="row-meta">
                                        <i class="bi bi-buildings me-1"></i>{{ $req->property?->name ?? 'N/A' }}
                                        @if ($tenantName !== 'N/A')
                                            · {{ $tenantName }}
                                        @endif
                                    </div>
                                </div>

                                {{-- Right side: status + date --}}
                                <div
                                    style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;flex-shrink:0;">
                                    <span
                                        style="
                        font-size:10px;font-weight:600;padding:3px 8px;
                        border-radius:100px;white-space:nowrap;
                        background:{{ $sc['bg'] }};color:{{ $sc['color'] }};">
                                        {{ $sc['label'] }}
                                    </span>
                                    <span
                                        style="font-size:10px;color:var(--ink-400);font-family:'IBM Plex Mono',monospace;">
                                        {{ $req->created_at->format('M d') }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="mt-empty" style="padding:40px 16px;">
                                <i class="bi bi-tools"
                                    style="font-size:28px;display:block;margin-bottom:8px;opacity:.3;"></i>
                                <p style="font-size:12.5px;color:var(--ink-400);">No maintenance requests yet</p>
                            </div>
                        @endforelse
                    </div>

                    {{-- Footer --}}
                    @if ($recentMaintenance->count() > 0)
                        <div style="padding:10px 20px;border-top:1px solid var(--border-subtle);background:var(--ink-50);">
                            <a href="{{ route('maintenance.index') }}"
                                style="display:block;text-align:center;font-size:12px;font-weight:600;color:var(--accent);text-decoration:none;">
                                See all maintenance requests →
                            </a>
                        </div>
                    @endif
                </div>

            </div>

            {{-- BOTTOM 3-COL --}}
            <div class="db-bottom">

                {{-- Occupancy Chart --}}
                <div class="card-base">
                    <div class="card-head">
                        <div class="card-title">
                            <span class="card-title-dot" style="background:#7c3aed"></span>
                            Occupancy by Property
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-wrap">
                            <canvas id="occupancyChart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Recent Tenants --}}
                <div class="card-base">
                    <div class="card-head">
                        <div class="card-title">
                            <span class="card-title-dot" style="background:#0891b2"></span>
                            Recent Active Tenants
                        </div>
                        <a href="{{ route('tenants.index') }}" class="card-action">All →</a>
                    </div>
                    <div class="card-body">
                        @forelse($recentTenants as $i => $tenant)
                            @php
                                $tn = $tenant->profile
                                    ? trim($tenant->profile->first_name . ' ' . $tenant->profile->last_name)
                                    : 'Unknown';
                                $ini = collect(explode(' ', $tn))
                                    ->map(fn($w) => strtoupper(substr($w, 0, 1)))
                                    ->take(2)
                                    ->join('');
                                $cl = $tenant->leases->where('status', 'ACTIVE')->first();
                                $asg = $cl?->assignments->where('is_current', true)->first();
                                $bed = $asg?->bed;
                                $unit = $bed?->room?->unit;
                                $tc = ['#4f46e5', '#0891b2', '#059669', '#d97706', '#e11d48', '#7c3aed'];
                                $tbg = $tc[$i % count($tc)];
                            @endphp
                            <div class="list-row">
                                <div class="avi" style="background:{{ $tbg }};font-size:11px;">
                                    {{ $ini }}</div>
                                <div style="flex:1;min-width:0;">
                                    <div class="row-name">{{ $tn }}</div>
                                    <div class="row-meta">
                                        @if ($unit && $bed)
                                            <i class="bi bi-door-closed me-1"></i>Unit {{ $unit->unit_number }},
                                            {{ $bed->bed_label }}
                                        @else
                                            <i class="bi bi-slash-circle me-1"></i>No assignment
                                        @endif
                                    </div>
                                </div>
                                <span style="font-size:10.5px;color:var(--ink-400);font-family:'IBM Plex Mono',monospace;">
                                    {{ $tenant->created_at->format('M d') }}
                                </span>
                            </div>
                        @empty
                            <div class="mt-empty"><i class="bi bi-people"></i>
                                <p>No recent tenants</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Quick Actions + Activity --}}
                <div style="display:flex;flex-direction:column;gap:14px;">
                    <div class="card-base">
                        <div class="card-head">
                            <div class="card-title">
                                <span class="card-title-dot" style="background:#f59e0b"></span>
                                Quick Actions
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="qa-grid">
                                <a href="{{ route('property.create') }}" class="qa-btn"
                                    style="background:var(--accent-bg);color:var(--accent);border-color:#c7d2fe;">
                                    <i class="bi bi-plus-circle-fill"></i> New Property
                                </a>
                                <a href="{{ route('tenants.index') }}" class="qa-btn"
                                    style="background:var(--emerald-bg);color:var(--emerald);border-color:#a7f3d0;">
                                    <i class="bi bi-people-fill"></i> Tenants
                                </a>
                                <a href="{{ route('leases.index') }}" class="qa-btn"
                                    style="background:var(--violet-bg);color:var(--violet);border-color:#ddd6fe;">
                                    <i class="bi bi-file-earmark-text-fill"></i> Leases
                                </a>
                                <a href="{{ route('property.index') }}" class="qa-btn"
                                    style="background:var(--sky-bg);color:var(--sky);border-color:#bae6fd;">
                                    <i class="bi bi-grid-3x3-gap-fill"></i> Properties
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-base" style="flex:1;">
                        <div class="card-head">
                            <div class="card-title">
                                <span class="card-title-dot" style="background:#0891b2"></span>
                                Recent Activity
                            </div>
                        </div>
                        <div class="card-body">
                            @if ($pendingApplications->count() > 0)
                                <div class="activity-item">
                                    <div class="act-icon" style="background:var(--amber-bg);color:var(--amber)">📋</div>
                                    <div>
                                        <div class="act-text"><strong>{{ $pendingApplications->count() }} new
                                                application(s)</strong> waiting for your review</div>
                                        <div class="act-time">Just now</div>
                                    </div>
                                </div>
                            @endif
                            @if ($unsignedLeases->count() > 0)
                                <div class="activity-item">
                                    <div class="act-icon" style="background:var(--rose-bg);color:var(--rose)">✍️</div>
                                    <div>
                                        <div class="act-text"><strong>{{ $unsignedLeases->count() }} lease(s)</strong>
                                            pending tenant signature</div>
                                        <div class="act-time">Awaiting action</div>
                                    </div>
                                </div>
                            @endif
                            @foreach ($recentTenants->take(2) as $rt)
                                @php $rtn = $rt->profile ? trim($rt->profile->first_name.' '.$rt->profile->last_name) : $rt->email; @endphp
                                <div class="activity-item">
                                    <div class="act-icon" style="background:var(--emerald-bg);color:var(--emerald)">👤
                                    </div>
                                    <div>
                                        <div class="act-text"><strong>{{ $rtn }}</strong> joined as active tenant
                                        </div>
                                        <div class="act-time">{{ $rt->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                            @endforeach
                            @if ($pendingApplications->count() === 0 && $unsignedLeases->count() === 0 && $recentTenants->count() === 0)
                                <div class="mt-empty"><i class="bi bi-activity"></i>
                                    <p>No recent activity</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://kit.fontawesome.com/aadff4f1c9.js" crossorigin="anonymous"></script>
    <script>
        function updateClock() {
            const now = new Date();
            const pad = n => String(n).padStart(2, '0');
            const timeEl = document.getElementById('live-time');
            if (timeEl) timeEl.textContent = pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now
                .getSeconds());
            const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const dateEl = document.getElementById('live-date');
            if (dateEl) dateEl.textContent = days[now.getDay()] + ', ' + months[now.getMonth()] + ' ' + now.getDate() +
                ', ' + now.getFullYear();
            const greet = document.getElementById('greeting-label');
            if (greet) {
                const hr = now.getHours();
                greet.textContent = hr < 12 ? 'Good Morning \u2600\uFE0F' : hr < 17 ? 'Good Afternoon \uD83C\uDF24' :
                    'Good Evening \uD83C\uDF19';
            }
        }
        updateClock();
        setInterval(updateClock, 1000);

        function viewLease(id) {
            window.location.href = '/admin/leases/' + id;
        }

        const propData = {!! json_encode($chartData) !!};
        if (propData.length > 0) {
            const ctx = document.getElementById('occupancyChart');
            if (ctx) {
                const palette = ['#6366f1', '#0891b2', '#059669', '#d97706', '#e11d48', '#7c3aed', '#0d9488', '#f97316'];
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: propData.map(function(p) {
                            return p.name;
                        }),
                        datasets: [{
                                label: 'Occupied',
                                data: propData.map(function(p) {
                                    return p.occ;
                                }),
                                backgroundColor: palette,
                                borderRadius: 6,
                                borderSkipped: false,
                            },
                            {
                                label: 'Available',
                                data: propData.map(function(p) {
                                    return p.avail;
                                }),
                                backgroundColor: palette.map(function(c) {
                                    return c + '28';
                                }),
                                borderRadius: 6,
                                borderSkipped: false,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom',
                                labels: {
                                    font: {
                                        family: 'Sora',
                                        size: 11,
                                        weight: '600'
                                    },
                                    color: '#6b7280',
                                    padding: 14,
                                    boxWidth: 9,
                                    boxHeight: 9,
                                    borderRadius: 3,
                                    useBorderRadius: true
                                }
                            },
                            tooltip: {
                                backgroundColor: '#111827',
                                titleFont: {
                                    family: 'Sora',
                                    size: 12,
                                    weight: '700'
                                },
                                bodyFont: {
                                    family: 'Sora',
                                    size: 12
                                },
                                padding: 10,
                                cornerRadius: 8
                            }
                        },
                        scales: {
                            x: {
                                stacked: true,
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        family: 'Sora',
                                        size: 10
                                    },
                                    color: '#9ca3af'
                                }
                            },
                            y: {
                                stacked: true,
                                grid: {
                                    color: '#f3f4f6'
                                },
                                ticks: {
                                    font: {
                                        family: 'Sora',
                                        size: 10
                                    },
                                    color: '#9ca3af',
                                    stepSize: 1
                                },
                                border: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }
        }
    </script>
@endpush
