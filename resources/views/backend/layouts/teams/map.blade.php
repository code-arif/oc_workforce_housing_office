@extends('backend.app')

@section('title', 'Team Work Map')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <div class="page-header mt-4">
                    <div class="card shadow-sm mb-2 border-0">
                        <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <h1 class="page-title mb-0">Team Work Map</h1>
                            </div>
                            <div class="text-end">
                                <a href="{{ route('team.list') }}" class="btn btn-primary btn-sm">
                                    <i class="fe fe-arrow-left me-1"></i> Back to List
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card box-shadow-0">
                            <div class="card-body">
                                <div id="map" style="height: 800px; width: 100%;"></div>
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
        function initMap() {
            const map = new google.maps.Map(document.getElementById('map'), {
                zoom: 10,
                center: {
                    lat: 23.8103,
                    lng: 90.4125
                },
            });

            const works = @json($works);

            // Step 1: Filter and sort by date & time
            const validWorks = works
                .filter(work => work.latitude && work.longitude)
                .sort((a, b) => {
                    const date = new Date(`${a.work_date} ${a.time}`);
                });

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
            const waypoints = validWorks.slice(1, -1).map(work => ({
                location: {
                    lat: parseFloat(work.latitude),
                    lng: parseFloat(work.longitude)
                },
                stopover: true,
            }));

            // Step 2: Request road-following directions
            directionsService.route({
                origin,
                destination,
                waypoints,
                travelMode: google.maps.TravelMode.DRIVING,
                optimizeWaypoints: false,
            }, (response, status) => {
                if (status !== 'OK') {
                    console.error('Directions request failed:', status);
                    return;
                }

                const route = response.routes[0];
                let totalDistance = 0;
                let totalDuration = 0;

                // Step 3: Draw colored segments
                route.legs.forEach((leg, index) => {
                    totalDistance += leg.distance.value;
                    totalDuration += leg.duration.value;

                    const work = validWorks[index];
                    let strokeColor = '#0000FF';
                    if (work.is_completed) strokeColor = '#00FF00';
                    else if (work.is_rescheduled) strokeColor = '#FFFF00';

                    new google.maps.Polyline({
                        path: leg.steps.flatMap(step => step.path),
                        strokeColor,
                        strokeOpacity: 1.0,
                        strokeWeight: 5,
                        map,
                    });

                    // Distance label at mid-point
                    const midIndex = Math.floor(leg.steps.length / 2);
                    const midLatLng = leg.steps[midIndex]?.end_location;
                    if (midLatLng) {
                        new google.maps.InfoWindow({
                            content: `<div style="font-size:13px; font-weight:bold;">${leg.distance.text}</div>`,
                            position: midLatLng,
                        }).open(map);
                    }
                });

                // Step 4: Custom numbered pin markers
                validWorks.forEach((work, index) => {
                    let iconColor = '#0000FF';
                    if (work.is_completed) iconColor = '#00FF00';
                    else if (work.is_rescheduled) iconColor = '#FFFF00';

                    const svgMarker = {
                        path: "M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z",
                        fillColor: iconColor,
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
                        map: map,
                        icon: svgMarker,
                        label: {
                            text: (index + 1).toString(),
                            color: "#fff",
                            fontSize: "12px",
                            fontWeight: "bold",
                        },
                        title: work.title,
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
                        </div>
                    `,
                    });
                    marker.addListener("click", () => infoWindow.open(map, marker));
                });

                // Step 5: Auto fit map
                const bounds = new google.maps.LatLngBounds();
                validWorks.forEach(work => {
                    bounds.extend({
                        lat: parseFloat(work.latitude),
                        lng: parseFloat(work.longitude)
                    });
                });
                map.fitBounds(bounds);

                // Step 6: Summary card
                const totalKm = (totalDistance / 1000).toFixed(2);
                const totalMin = Math.round(totalDuration / 60);
                const summaryDiv = document.createElement('div');
                summaryDiv.innerHTML = `
            <div style="background:#fff; padding:10px 15px; border-radius:8px; box-shadow:0 0 6px rgba(0,0,0,0.3); font-size:14px;">
                <b>Total Distance:</b> ${totalKm} km |
                <b>Estimated Duration:</b> ${totalMin} min
            </div>
        `;
                map.controls[google.maps.ControlPosition.TOP_CENTER].push(summaryDiv);
            });
        }
    </script>
@endpush
