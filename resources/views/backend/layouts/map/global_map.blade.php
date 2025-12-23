@extends('backend.app')

@section('title', 'Global Work Map')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <div class="page-header">
                    <div class="card shadow-sm mb-2 border-0">
                        <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <h1 class="page-title mb-0">Global Work Google Map</h1>
                            </div>
                            <div class="text-end">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card box-shadow-0">
                            <div class="card-body">
                                <div class="row">
                                    <!-- Sidebar with Team Filter -->
                                    <div class="col-md-2 bg-light">
                                        <div class="sidebar"
                                            style="max-height: 800px; overflow-y: auto; padding-right: 10px;">
                                            <div class="mb-3">
                                                <label for="teamSearch" class="form-label">Search Team:</label>
                                                <input type="text" id="teamSearch" class="form-control"
                                                    placeholder="Search teams..." oninput="dynamicSearchTeams()">
                                            </div>
                                            <div class="mb-3">
                                                <label for="teamFilter" class="form-label">Select Team:</label>
                                                <select id="teamFilter" class="form-select" onchange="filterMap()">
                                                    @foreach ($teams as $team)
                                                        <option value="{{ $team->id }}"
                                                            {{ $firstTeam && $firstTeam->id === $team->id ? 'selected' : '' }}>
                                                            {{ $team->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Map -->
                                    <div class="col-md-10">
                                        <div id="map" style="height: 800px; width: 100%;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBfGOjmqKtEBRsfVN9szUo_tac20wcI9HM&callback=initMap" async
        defer></script>

    <script>
        const filterRoute = "{{ route('works.filter', ':id') }}";
        const searchTeamsRoute = "{{ route('works.searchTeams') }}"; // Assuming a route for team search

        let map;
        let works = @json($works);

        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 10,
                center: {
                    lat: 23.8103,
                    lng: 90.4125
                },
            });

            drawWorks(works);
        }

        function drawWorks(works) {
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 10,
                center: {
                    lat: 23.8103,
                    lng: 90.4125
                },
            });

            const validWorks = works
                .filter(w => w.latitude && w.longitude)
                .sort((a, b) => new Date(`${a.start_datetime}`));

            if (validWorks.length === 0) return;

            const directionsService = new google.maps.DirectionsService();
            const origin = {
                lat: parseFloat(validWorks[0].latitude),
                lng: parseFloat(validWorks[0].longitude)
            };
            const destination = {
                lat: parseFloat(validWorks[validWorks.length - 1].latitude),
                lng: parseFloat(validWorks[validWorks.length - 1].longitude)
            };
            const waypoints = validWorks.slice(1, -1).map(w => ({
                location: {
                    lat: parseFloat(w.latitude),
                    lng: parseFloat(w.longitude)
                },
                stopover: true
            }));

            directionsService.route({
                origin,
                destination,
                waypoints,
                travelMode: google.maps.TravelMode.DRIVING,
            }, (response, status) => {
                if (status !== 'OK') return console.error('Route failed:', status);
                const route = response.routes[0];
                let totalDistance = 0;

                route.legs.forEach((leg, index) => {
                    const work = validWorks[index];
                    totalDistance += leg.distance.value;
                    let strokeColor = '#0000FF';
                    if (work.is_completed) strokeColor = '#00FF00';
                    else if (work.is_rescheduled) strokeColor = '#FFFF00';

                    new google.maps.Polyline({
                        path: leg.steps.flatMap(step => step.path),
                        strokeColor,
                        strokeWeight: 5,
                        map,
                    });

                    // Distance label midpoint
                    const midStep = leg.steps[Math.floor(leg.steps.length / 2)];
                    if (midStep) {
                        new google.maps.InfoWindow({
                            content: `<div style="font-size:13px; font-weight:bold;">${leg.distance.text}</div>`,
                            position: midStep.end_location,
                        }).open(map);
                    }
                });

                validWorks.forEach((work, i) => {
                    let color = '#0000FF';
                    if (work.is_completed) color = '#00FF00';
                    else if (work.is_rescheduled) color = '#FFFF00';

                    const svgMarker = {
                        path: "M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z",
                        fillColor: color,
                        fillOpacity: 1,
                        strokeColor: "#fff",
                        strokeWeight: 2,
                        scale: 2,
                        anchor: new google.maps.Point(12, 24),
                        labelOrigin: new google.maps.Point(12, 10),
                    };

                    const marker = new google.maps.Marker({
                        position: {
                            lat: parseFloat(work.latitude),
                            lng: parseFloat(work.longitude)
                        },
                        map,
                        icon: svgMarker,
                        label: {
                            text: (i + 1).toString(),
                            color: "#fff",
                            fontSize: "12px",
                            fontWeight: "bold"
                        },
                    });

                    const infoWindow = new google.maps.InfoWindow({
                        content: `
                        <div style="min-width:200px">
                            <h4>${work.title}</h4>
                            <p><strong>Location:</strong> ${work.location || 'N/A'}</p>
                            <p><strong>Status:</strong> ${
                                work.is_completed ? 'Completed' :
                                work.is_rescheduled ? 'Rescheduled' : 'Incomplete'
                            }</p>
                            <p><strong>Date:</strong> ${work.formatted_datetime || 'N/A'}</p>
                        </div>`,
                    });

                    marker.addListener("click", () => infoWindow.open(map, marker));
                });

                const bounds = new google.maps.LatLngBounds();
                validWorks.forEach(w => bounds.extend({
                    lat: parseFloat(w.latitude),
                    lng: parseFloat(w.longitude)
                }));
                map.fitBounds(bounds);
            });
        }

        function filterMap() {
            const teamId = document.getElementById('teamFilter').value;
            const url = filterRoute.replace(':id', teamId);

            fetch(url)
                .then(res => {
                    if (!res.ok) throw new Error(`HTTP error! Status: ${res.status}`);
                    return res.json();
                })
                .then(data => drawWorks(data))
                .catch(err => console.error('Filter Error:', err));
        }

        function dynamicSearchTeams() {
            const input = document.getElementById('teamSearch').value;
            const select = document.getElementById('teamFilter');

            if (input.length < 2) {
                // Reset to original teams if input is too short
                select.innerHTML = @json($teams).map(team =>
                    `<option value="${team.id}" ${@json($firstTeam)?.id === team.id ? 'selected' : ''}>${team.name}</option>`
                ).join('');
                return;
            }

            fetch(`${searchTeamsRoute}?query=${encodeURIComponent(input)}`)
                .then(res => {
                    if (!res.ok) throw new Error(`HTTP error! Status: ${res.status}`);
                    return res.json();
                })
                .then(teams => {
                    select.innerHTML = teams.length > 0 ?
                        teams.map(team =>
                            `<option value="${team.id}" ${@json($firstTeam)?.id === team.id ? 'selected' : ''}>${team.name}</option>`
                        ).join('') :
                        '<option value="">No teams found</option>';
                })
                .catch(err => {
                    console.error('Search Error:', err);
                    select.innerHTML = '<option value="">Error loading teams</option>';
                });
        }
    </script>
@endpush
