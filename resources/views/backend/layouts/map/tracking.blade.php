@extends('backend.app')

@section('title', 'Real-Time Location Tracking')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <!-- Header -->
                <div class="page-header">
                    <div class="card shadow-sm mb-3 border-0">
                        <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <h1 class="page-title mb-1">
                                    <i class="fa fa-map-marker text-danger me-2"></i>
                                    Real-Time Location Tracking
                                </h1>
                                <p class="text-muted mb-0">Monitor team leaders' live location via WebSocket</p>
                            </div>
                            <div class="d-flex gap-2 align-items-center">
                                <button class="btn btn-primary" id="refreshLocations">
                                    <i class="fa fa-refresh"></i> Refresh
                                </button>
                                <button class="btn btn-outline-primary" id="fitBoundsBtn">
                                    <i class="fa fa-arrows-alt"></i> Fit All
                                </button>
                                <button class="btn btn-outline-secondary" id="clearMapBtn">
                                    <i class="fa fa-eraser"></i> Clear
                                </button>
                                <span class="badge bg-success fs-6 px-3 py-2" id="connectionStatus">
                                    <i class="fa fa-circle pulse"></i> Connecting...
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Map Container -->
                    <div class="col-xl-9 col-lg-8 col-12">
                        <div class="card shadow-sm">
                            <!-- Map Controls -->
                            <div class="card-header bg-white border-bottom py-4">
                                <div class="row align-items-center gx-3">
                                    <!-- Title -->
                                    <div class="col-auto">
                                        <h3 class="card-title mb-0 d-flex align-items-center">
                                            <i class="fa fa-map text-primary me-2"></i>
                                            Live Map View
                                        </h3>
                                    </div>

                                    <!-- Team Filter -->
                                    <div class="col-auto" style="margin-right: 25px">
                                        <select class="form-select form-select-sm" id="teamFilter"
                                            style="min-width: 180px;">
                                            <option value="">All Teams</option>
                                            @foreach ($teams as $team)
                                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Show Routes Switch -->
                                    <div class="col-auto" style="margin-right: 25px">
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input custom-switch" type="checkbox" id="showRoutes">
                                            <label class="form-check-label ms-3 fw-semibold" for="showRoutes">
                                                Show Routes
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Show Works Switch -->
                                    <div class="col-auto" style="margin-right: 25px">
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input custom-switch" type="checkbox"
                                                id="showWorkMarkers" checked>
                                            <label class="form-check-label ms-3 fw-semibold" for="showWorkMarkers">
                                                Show Works
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Map -->
                            <div class="card-body p-0">
                                <div id="map" style="height: 700px; width: 100%;"></div>
                            </div>

                            <!-- Map Legend -->
                            <div class="card-footer bg-light">
                                <div class="d-flex justify-content-around flex-wrap">
                                    <div class="legend-item">
                                        <span class="legend-marker" style="background: #28a745;"></span>
                                        <small>Work Completed</small>
                                    </div>
                                    <div class="legend-item">
                                        <span class="legend-marker" style="background: #ffc107;"></span>
                                        <small>Work Rescheduled</small>
                                    </div>
                                    <div class="legend-item">
                                        <span class="legend-marker" style="background: #007bff;"></span>
                                        <small>Work Pending</small>
                                    </div>
                                    <div class="legend-item">
                                        <span class="legend-marker" style="background: #dc3545;"></span>
                                        <small>Work In Progress</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-xl-3 col-lg-4 col-12">
                        <!-- Stats Cards -->
                        <div class="row mb-3">
                            <div class="col-6">
                                <div class="card shadow-sm">
                                    <div class="card-body text-center py-3">
                                        <i class="fa fa-users fa-2x text-primary mb-2"></i>
                                        <h3 class="mb-0" id="totalTeams">0</h3>
                                        <small class="text-muted">Active Teams</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card shadow-sm">
                                    <div class="card-body text-center py-3">
                                        <i class="fa fa-user fa-2x text-success mb-2"></i>
                                        <h3 class="mb-0" id="totalLeaders">0</h3>
                                        <small class="text-muted">Team Leaders</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Active Teams List -->
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h4 class="card-title mb-0">
                                    <i class="fa fa-list-ul me-2"></i>
                                    Active Teams
                                </h4>
                            </div>
                            <div class="card-body p-2">
                                <input type="text" class="form-control mb-2" id="teamSearch"
                                    placeholder="🔍 Search teams...">
                            </div>
                            <div id="teamList" style="max-height: 550px; overflow-y: auto;">
                                <div class="text-center p-4">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="mt-2 mb-0">Loading teams...</p>
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
    <style>
        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 5px 10px;
        }

        .legend-marker {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
        }

        .team-item {
            transition: all 0.3s ease;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
        }

        .team-item:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }

        .team-item.active {
            background: linear-gradient(90deg, #e3f2fd 0%, #ffffff 100%);
            border-left: 4px solid #007bff;
        }

        .team-avatar {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            color: white;
            background: linear-gradient(135deg, #007bff 0%, #28a745 100%);
        }

        .custom-switch {
            width: 2.8rem;
            height: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .custom-switch:checked {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .form-check-label {
            font-size: 0.9rem;
            font-weight: 500;
        }
    </style>

    <!-- Custom CSS -->
    <style>
        /* Make all items stay in one line with wrapping if screen small */
        .card-header .row {
            flex-wrap: nowrap;
            overflow-x: auto;
            white-space: nowrap;
        }

        /* Custom switch size */
        .custom-switch {
            width: 2.8em !important;
            height: 1.5em !important;
        }

        .custom-switch:checked {
            background-color: #13bfa6 !important;
            /* Red when active */
            border-color: #13bfa6 !important;
        }

        /* Make switch knob slightly bigger */
        .custom-switch::before {
            height: 1.1em !important;
            width: 1.1em !important;
            margin-top: 0.15em;
        }

        /* Optional: adjust label alignment */
        .form-check-label {
            user-select: none;
            font-size: 0.95rem;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        let map;
        let markers = {};
        let infoWindows = {};
        let polylines = {};
        let workMarkers = {};
        let selectedTeamId = null;
        let allLocationsData = [];
        let currentTeamRoute = null;
        let echo;

        // Routes configuration
        const routes = {
            locations: '{{ route('admin.tracking.locations') }}',
            teams: '{{ route('admin.tracking.teams') }}',
            teamHistory: '{{ route('admin.tracking.team.history', ['teamId' => ':teamId']) }}',
            teamRoute: '{{ route('admin.tracking.team.route', ['teamId' => ':teamId']) }}'
        };

        // Initialize Laravel Echo with Reverb
        function initializeEcho() {
            console.log('🔌 Connecting to Reverb WebSocket...');

            echo = new window.Pusher('{{ config('broadcasting.connections.reverb.key') }}', {
                wsHost: '{{ config('broadcasting.connections.reverb.options.host') }}',
                wsPort: {{ config('broadcasting.connections.reverb.options.port') }},
                wssPort: {{ config('broadcasting.connections.reverb.options.port') }},
                forceTLS: {{ config('broadcasting.connections.reverb.options.scheme') === 'https' ? 'true' : 'false' }},
                enabledTransports: ['ws', 'wss'],
                cluster: 'mt1',
                disableStats: true,
            });

            const channel = echo.subscribe('location-tracking');

            channel.bind('pusher:subscription_succeeded', () => {
                console.log('✅ Connected to WebSocket');
                updateConnectionStatus('connected');
            });

            channel.bind('pusher:subscription_error', (error) => {
                console.error('❌ WebSocket connection error:', error);
                updateConnectionStatus('error');
            });

            channel.bind('location.updated', (data) => {
                console.log('📍 Real-time location received:', data);
                handleRealtimeLocation(data);
            });

            echo.connection.bind('connected', () => {
                console.log('🟢 WebSocket Connected');
                updateConnectionStatus('connected');
            });

            echo.connection.bind('disconnected', () => {
                console.log('🔴 WebSocket Disconnected');
                updateConnectionStatus('disconnected');
            });

            echo.connection.bind('error', (error) => {
                console.error('❌ WebSocket Error:', error);
                updateConnectionStatus('error');
            });
        }

        // Handle real-time location updates
        function handleRealtimeLocation(data) {
            if (selectedTeamId && data.team_id != selectedTeamId) {
                return;
            }

            const key = `${data.team_id}_${data.user_id}`;
            const position = {
                lat: parseFloat(data.latitude),
                lng: parseFloat(data.longitude)
            };

            if (markers[key]) {
                markers[key].setPosition(position);
                if (markers[key].getAnimation() === null) {
                    markers[key].setAnimation(google.maps.Animation.BOUNCE);
                    setTimeout(() => markers[key].setAnimation(null), 1000);
                }
            } else {
                createMarker(data, position, key);
            }

            if (infoWindows[key]) {
                infoWindows[key].setContent(createInfoWindowContent(data));
            }

            updateTeamInList(data);

            // Update route if "Show Routes" is enabled
            if ($('#showRoutes').is(':checked') && selectedTeamId) {
                fetchTeamRoute(selectedTeamId);
            }
        }

        // Create new marker
        function createMarker(data, position, key) {
            const marker = new google.maps.Marker({
                position: position,
                map: map,
                title: `${data.user_name} (${data.team_name})`,
                animation: google.maps.Animation.DROP,
                icon: createCustomMarker(data.team_id, true)
            });

            const infoWindow = new google.maps.InfoWindow({
                content: createInfoWindowContent(data)
            });

            marker.addListener('click', () => {
                closeAllInfoWindows();
                infoWindow.open(map, marker);
            });

            markers[key] = marker;
            infoWindows[key] = infoWindow;
        }

        // Update connection status badge
        function updateConnectionStatus(status) {
            const badge = $('#connectionStatus');
            const statusConfig = {
                'connected': {
                    class: 'bg-success',
                    icon: 'fa-circle pulse',
                    text: 'Live'
                },
                'disconnected': {
                    class: 'bg-warning',
                    icon: 'fa-exclamation-circle',
                    text: 'Reconnecting...'
                },
                'error': {
                    class: 'bg-danger',
                    icon: 'fa-times-circle',
                    text: 'Connection Error'
                }
            };

            const config = statusConfig[status];
            badge.removeClass().addClass(`badge fs-6 px-3 py-4 ${config.class}`);
            badge.html(`<i class="fa ${config.icon}"></i> ${config.text}`);
        }

        // Initialize Google Map
        function initMap() {
            console.log('🗺️ Initializing Google Maps...');

            map = new google.maps.Map(document.getElementById('map'), {
                center: {
                    lat: 23.8103,
                    lng: 90.4125
                },
                zoom: 12,
                mapTypeId: 'roadmap',
                styles: [{
                    featureType: 'poi',
                    elementType: 'labels',
                    stylers: [{
                        visibility: 'on'
                    }]
                }]
            });

            console.log('✅ Map initialized');
        }

        // Fetch initial locations
        function fetchLocations() {
            console.log('📡 Fetching initial locations...');

            const teamId = $('#teamFilter').val();
            const url = routes.locations + (teamId ? `?team_id=${teamId}` : '');

            $.ajax({
                url: url,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        console.log(`✅ Received ${response.data.length} locations`);
                        allLocationsData = response.data;
                        updateMap(response.data);
                        updateTeamList(response.data);
                    }
                },
                error: function(xhr) {
                    console.error('❌ Failed to fetch locations:', xhr);
                    alert('Failed to fetch locations. Please try again.');
                }
            });
        }

        // Fetch team route (NEW)
        function fetchTeamRoute(teamId) {
            const url = routes.teamRoute.replace(':teamId', teamId);

            $.ajax({
                url: url,
                method: 'GET',
                data: {
                    hours: 24
                },
                success: function(response) {
                    if (response.success) {
                        console.log('✅ Team route fetched:', response.data);
                        currentTeamRoute = response.data;
                        drawTeamRoute(response.data);
                        drawWorkMarkers(response.data.works);
                    }
                },
                error: function(xhr) {
                    console.error('❌ Failed to fetch team route:', xhr);
                }
            });
        }

        // Draw team route polyline (NEW)
        function drawTeamRoute(routeData) {
            // Clear existing polylines
            Object.values(polylines).forEach(polyline => polyline.setMap(null));
            polylines = {};

            if (!routeData.route || routeData.route.length < 2) {
                console.log('No route data to draw');
                return;
            }

            const path = routeData.route.map(loc => ({
                lat: parseFloat(loc.latitude),
                lng: parseFloat(loc.longitude)
            }));

            const polyline = new google.maps.Polyline({
                path: path,
                geodesic: true,
                strokeColor: '#FF0000',
                strokeOpacity: 0.8,
                strokeWeight: 3,
                map: map
            });

            polylines[`team_${routeData.team.id}`] = polyline;

            // Fit bounds to route
            const bounds = new google.maps.LatLngBounds();
            path.forEach(point => bounds.extend(point));
            map.fitBounds(bounds);
        }

        // Draw work markers (NEW)
        function drawWorkMarkers(works) {
            // Clear existing work markers
            Object.values(workMarkers).forEach(marker => marker.setMap(null));
            workMarkers = {};

            if (!$('#showWorkMarkers').is(':checked')) {
                return;
            }

            works.forEach(work => {
                if (!work.latitude || !work.longitude) return;

                const position = {
                    lat: parseFloat(work.latitude),
                    lng: parseFloat(work.longitude)
                };

                const status = work.tracking_status || 'pending';
                const color = getWorkColor(status);

                const marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    title: work.title,
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 10,
                        fillColor: color,
                        fillOpacity: 0.9,
                        strokeColor: '#ffffff',
                        strokeWeight: 2
                    }
                });

                const infoWindow = new google.maps.InfoWindow({
                    content: createWorkInfoWindowContent(work)
                });

                marker.addListener('click', () => {
                    closeAllInfoWindows();
                    infoWindow.open(map, marker);
                });

                workMarkers[`work_${work.id}`] = marker;
            });
        }

        // Get work marker color based on status (NEW)
        function getWorkColor(status) {
            const colors = {
                'completed': '#28a745',
                'in_progress': '#dc3545',
                'pending': '#007bff',
                'rescheduled': '#ffc107'
            };
            return colors[status] || '#6c757d';
        }

        // Create work info window content (NEW)
        function createWorkInfoWindowContent(work) {
            const status = work.tracking_status || 'pending';
            const statusBadge = {
                'completed': '<span class="badge bg-success">Completed</span>',
                'in_progress': '<span class="badge bg-danger">In Progress</span>',
                'pending': '<span class="badge bg-primary">Pending</span>',
                'rescheduled': '<span class="badge bg-warning">Rescheduled</span>'
            };

            const startTime = work.start_datetime ? new Date(work.start_datetime).toLocaleString() : 'N/A';
            const completedAt = work.completed_at ? new Date(work.completed_at).toLocaleString() : 'N/A';

            return `<div style="padding: 15px; min-width: 250px;">
                <h5 style="margin: 0 0 10px; color: #333; border-bottom: 2px solid ${getWorkColor(status)}; padding-bottom: 8px;">
                    📋 ${work.title}
                </h5>
                <div style="font-size: 13px; line-height: 1.8;">
                    <div><strong>Status:</strong> ${statusBadge[status]}</div>
                    <div><strong>📍 Location:</strong> ${work.location || 'N/A'}</div>
                    <div><strong>🕐 Scheduled:</strong> ${startTime}</div>
                    ${status === 'completed' ? `<div><strong>✅ Completed:</strong> ${completedAt}</div>` : ''}
                    ${work.description ? `<div class="mt-2"><strong>Description:</strong><br>${work.description}</div>` : ''}
                </div>
            </div>`;
        }

        // Clear map (NEW)
        function clearMap() {
            Object.values(markers).forEach(marker => marker.setMap(null));
            Object.values(polylines).forEach(polyline => polyline.setMap(null));
            Object.values(workMarkers).forEach(marker => marker.setMap(null));

            markers = {};
            polylines = {};
            workMarkers = {};

            console.log('✅ Map cleared');
        }

        // Update map with locations
        function updateMap(locations) {
            if (!map) return;

            const bounds = new google.maps.LatLngBounds();
            let hasLocations = false;

            locations.forEach(location => {
                const key = `${location.team_id}_${location.user_id}`;
                const position = {
                    lat: parseFloat(location.latitude),
                    lng: parseFloat(location.longitude)
                };

                bounds.extend(position);
                hasLocations = true;

                if (!markers[key]) {
                    createMarker(location, position, key);
                }
            });

            if (hasLocations && !selectedTeamId) {
                map.fitBounds(bounds);
                if (map.getZoom() > 15) map.setZoom(15);
            }

            updateStats(locations);
        }

        // Create custom marker icon
        function createCustomMarker(teamId, isLeader) {
            const color = getTeamColor(teamId);
            return {
                path: google.maps.SymbolPath.CIRCLE,
                scale: isLeader ? 16 : 12,
                fillColor: color,
                fillOpacity: 1,
                strokeColor: '#ffffff',
                strokeWeight: isLeader ? 4 : 3
            };
        }

        // Create info window content
        function createInfoWindowContent(location) {
            const time = new Date(location.tracked_at);
            const timeStr = time.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });

            const speed = location.speed ? parseFloat(location.speed).toFixed(1) + ' km/h' : 'N/A';
            const accuracy = location.accuracy ? Math.round(location.accuracy) + 'm' : 'N/A';
            const battery = location.battery_level || 'N/A';
            const teamColor = getTeamColor(location.team_id);

            return `<div style="padding: 15px; min-width: 250px;">
                <h5 style="margin: 0 0 10px; color: ${teamColor}; border-bottom: 2px solid ${teamColor}; padding-bottom: 8px;">
                    <i class="fa fa-user-circle"></i> ${location.user_name}
                    <span style="background: #ffc107; color: #000; padding: 2px 6px; border-radius: 3px; font-size: 11px; margin-left: 5px;">LEADER</span>
                </h5>
                <div style="font-size: 13px; line-height: 1.8;">
                    <div><strong>📋 Team:</strong> ${location.team_name}</div>
                    <div><strong>🕐 Time:</strong> ${timeStr}</div>
                </div>
            </div>`;
        }

        // Update team list
        function updateTeamList(locations) {
            const teamData = {};

            locations.forEach(loc => {
                if (!teamData[loc.team_id]) {
                    teamData[loc.team_id] = {
                        name: loc.team_name,
                        leaders: []
                    };
                }
                teamData[loc.team_id].leaders.push(loc);
            });

            let html = '';
            Object.keys(teamData).forEach(teamId => {
                const team = teamData[teamId];
                const lastUpdate = new Date(team.leaders[0].tracked_at);
                const timeAgo = getTimeAgo(lastUpdate);
                const teamColor = getTeamColor(teamId);

                html += `<div class="team-item p-3" data-team-id="${teamId}">
                    <div class="d-flex align-items-center">
                        <div class="team-avatar me-3" style="background: ${teamColor};">
                            ${team.name.charAt(0).toUpperCase()}
                        </div>
                        <div class="flex-fill">
                            <strong class="d-block">${team.name}</strong>
                            <small class="text-muted">
                                <i class="fa fa-user-circle"></i> ${team.leaders.length} Leader(s)
                            </small>
                            <div class="text-muted small mt-1">
                                <i class="fa fa-clock-o"></i> ${timeAgo}
                            </div>
                        </div>
                        <span class="badge bg-success">${team.leaders.length}</span>
                    </div>
                </div>`;
            });

            $('#teamList').html(html || '<div class="text-center p-4 text-muted">No active teams</div>');

            $('.team-item').click(function() {
                const teamId = $(this).data('team-id');
                selectedTeamId = teamId;
                $('#teamFilter').val(teamId);

                // Fetch locations and route
                fetchLocations();

                if ($('#showRoutes').is(':checked')) {
                    fetchTeamRoute(teamId);
                }

                $('.team-item').removeClass('active');
                $(this).addClass('active');
            });
        }

        // Update specific team in list
        function updateTeamInList(data) {
            const teamItem = $(`.team-item[data-team-id="${data.team_id}"]`);
            if (teamItem.length) {
                const timeAgo = getTimeAgo(new Date(data.tracked_at));
                teamItem.find('.text-muted.small.mt-1').html(`<i class="fa fa-clock-o"></i> ${timeAgo}`);
            }
        }

        // Update statistics
        function updateStats(locations) {
            const teams = new Set(locations.map(l => l.team_id));
            $('#totalTeams').text(teams.size);
            $('#totalLeaders').text(locations.length);
        }

        // Get team color
        function getTeamColor(teamId) {
            const colors = ['#007bff', '#28a745', '#dc3545', '#ffc107', '#17a2b8', '#6f42c1', '#fd7e14', '#20c997'];
            return colors[teamId % colors.length];
        }

        // Get time ago
        function getTimeAgo(date) {
            const seconds = Math.floor((new Date() - date) / 1000);
            if (seconds < 60) return seconds + 's ago';
            if (seconds < 3600) return Math.floor(seconds / 60) + 'm ago';
            if (seconds < 86400) return Math.floor(seconds / 3600) + 'h ago';
            return Math.floor(seconds / 86400) + 'd ago';
        }

        // Close all info windows
        function closeAllInfoWindows() {
            Object.values(infoWindows).forEach(iw => iw.close());
        }

        // Load Google Maps
        function loadGoogleMaps() {
            if (typeof google !== 'undefined') {
                initializeApp();
            } else {
                const script = document.createElement('script');
                script.src =
                    'https://maps.googleapis.com/maps/api/js?key=AIzaSyBfGOjmqKtEBRsfVN9szUo_tac20wcI9HM&libraries=geometry&callback=initializeApp';
                script.async = true;
                script.defer = true;
                document.head.appendChild(script);
            }
        }

        // Initialize application
        function initializeApp() {
            console.log('🚀 Initializing real-time tracking...');
            initMap();
            fetchLocations();
            initializeEcho();
        }

        // Document ready
        $(document).ready(function() {
            // Refresh locations button
            $('#refreshLocations').click(fetchLocations);

            // Team filter change
            $('#teamFilter').change(function() {
                selectedTeamId = $(this).val() || null;
                clearMap();
                fetchLocations();

                if ($('#showRoutes').is(':checked') && selectedTeamId) {
                    fetchTeamRoute(selectedTeamId);
                }
            });

            // Fit bounds button
            $('#fitBoundsBtn').click(function() {
                if (allLocationsData.length > 0) {
                    const bounds = new google.maps.LatLngBounds();
                    allLocationsData.forEach(loc => {
                        bounds.extend({
                            lat: parseFloat(loc.latitude),
                            lng: parseFloat(loc.longitude)
                        });
                    });
                    map.fitBounds(bounds);
                }
            });

            // Clear map button (NEW)
            $('#clearMapBtn').click(function() {
                clearMap();
                selectedTeamId = null;
                $('#teamFilter').val('');
                $('.team-item').removeClass('active');
                console.log('✅ Map and filters cleared');
            });

            // Show routes toggle (NEW)
            $('#showRoutes').change(function() {
                if ($(this).is(':checked')) {
                    if (selectedTeamId) {
                        fetchTeamRoute(selectedTeamId);
                    } else {
                        alert('Please select a team first to show routes');
                        $(this).prop('checked', false);
                    }
                } else {
                    // Clear polylines
                    Object.values(polylines).forEach(polyline => polyline.setMap(null));
                    polylines = {};
                }
            });

            // Show work markers toggle (NEW)
            $('#showWorkMarkers').change(function() {
                if ($(this).is(':checked')) {
                    if (currentTeamRoute && currentTeamRoute.works) {
                        drawWorkMarkers(currentTeamRoute.works);
                    }
                } else {
                    // Hide work markers
                    Object.values(workMarkers).forEach(marker => marker.setMap(null));
                }
            });

            // Team search
            $('#teamSearch').on('keyup', function() {
                const search = $(this).val().toLowerCase();
                $('.team-item').each(function() {
                    $(this).toggle($(this).text().toLowerCase().includes(search));
                });
            });

            // Load application
            loadGoogleMaps();
        });

        // Expose to global scope
        window.initializeApp = initializeApp;
    </script>
@endpush
