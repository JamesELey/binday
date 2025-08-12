<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bin Collections Map</title>
    <link href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
    <style>
        body { max-width: 1100px; margin: 1rem auto; }
        #map { height: 600px; border-radius: 8px; }
        .legend { display:flex; gap: .5rem; align-items:center; }
        .dot { width: 12px; height: 12px; border-radius: 50%; display:inline-block; }
    </style>
</head>
<body>
<header>
    <h2>Bin Collections Map</h2>
    <p><a href="{{ route('bins.index') }}">Back to list</a></p>
</header>

<main>
    <div class="grid">
        <div>
            <div class="legend"><span class="dot" style="background:#2f855a"></span> Residual Waste</div>
            <div class="legend"><span class="dot" style="background:#3182ce"></span> Recycling + Cardboard</div>
            <div class="legend"><span class="dot" style="background:#2f8f46"></span> Garden Waste</div>
        </div>
        <div class="muted">Pins show saved bin schedules. If any have no coordinates, they are geocoded when saved.</div>
    </div>

    <div id="map"></div>
</main>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script>
    const colorFor = (type) => {
        switch(type){
            case 'Residual Waste': return '#2f855a';
            case 'Recycling + Cardboard': return '#3182ce';
            case 'Garden Waste': return '#2f8f46';
            default: return '#666';
        }
    };

    function dotIcon(color){
        const html = `<span style="display:inline-block;width:14px;height:14px;border-radius:50%;background:${color};border:2px solid #fff;box-shadow:0 0 0 1px rgba(0,0,0,.25);"></span>`;
        return L.divIcon({ html, className: 'bin-dot', iconSize: [18, 18], iconAnchor: [9, 9], popupAnchor: [0, -9] });
    }

    async function loadData(){
        const res = await fetch('{{ route('api.bins') }}');
        const items = await res.json();
        return items.filter(i => i.lat && i.lng);
    }

    async function init(){
        const items = await loadData();
        const map = L.map('map');
        const tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        if(items.length){
            const cluster = L.markerClusterGroup({ showCoverageOnHover: false, spiderfyOnEveryZoom: true });
            items.forEach(i => {
                const marker = L.marker([i.lat, i.lng], { icon: dotIcon(colorFor(i.bin_type)), title: `${i.bin_type} @ ${i.address}` });
                marker.bindPopup(`<strong>${i.bin_type}</strong><br>${i.address}<br>${i.postcode}<br>Start: ${i.start_date ?? '-'}`);
                cluster.addLayer(marker);
            });
            map.addLayer(cluster);
            map.fitBounds(cluster.getBounds().pad(0.2));
        } else {
            map.setView([52.3555, -1.1743], 6); // UK fallback
        }
    }

    init();
</script>
</body>
</html>


