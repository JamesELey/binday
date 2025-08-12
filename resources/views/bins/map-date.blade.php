<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collections Map by Date</title>
    <link href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body { max-width: 1100px; margin: 1rem auto; }
        #map { height: 600px; border-radius: 8px; }
        form { margin-bottom: 1rem; }
    </style>
</head>
<body>
<header>
    <h2>Collections Map by Date</h2>
    <p><a href="{{ route('bins.index') }}">Back to Home</a></p>
</header>

<main>
    <form method="GET" action="{{ route('bins.mapByDate') }}">
        <label>Pick date
            <input type="date" name="date" value="{{ request('date') }}" required>
        </label>
        <button type="submit">Show</button>
    </form>

    <div id="map"></div>
</main>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const params = new URLSearchParams(window.location.search);
    const date = params.get('date');
    async function loadData(){
        if(!date){ return []; }
        const res = await fetch(`{{ route('api.bins') }}?date=${encodeURIComponent(date)}`);
        const items = await res.json();
        return items.filter(i => i.lat && i.lng);
    }

    async function init(){
        const map = L.map('map');
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
        const items = await loadData();
        if(items.length){
            const group = L.featureGroup();
            items.forEach(i => {
                const m = L.circleMarker([i.lat, i.lng], { radius: 7, color: '#2b6cb0', fillColor: '#2b6cb0', fillOpacity: .9 }).addTo(group);
                m.bindPopup(`<strong>${i.bin_type}</strong><br>${i.address}<br>${i.postcode}<br>Start: ${i.start_date ?? '-'}${i.recurs_biweekly ? '<br>(Repeats every 2 weeks)' : ''}`);
            });
            group.addTo(map);
            map.fitBounds(group.getBounds().pad(0.2));
        } else {
            map.setView([52.5, -1.5], 6);
        }
    }
    init();
</script>

</body>
</html>


