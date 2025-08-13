<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collections Map by Date</title>
    <link href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css" />
    <style>
        body { max-width: 1200px; margin: 1rem auto; }
        #map { height: 700px; border-radius: 8px; }
        form { 
            margin-bottom: 1rem; 
            background: white; 
            padding: 20px; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .legend { display:flex; gap: .5rem; align-items:center; margin: 10px 0; }
        .dot { width: 12px; height: 12px; border-radius: 50%; display:inline-block; }
        .date-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
    </style>
</head>
<body>
<header>
    <h2>Collections Map by Date</h2>
    <p><a href="{{ route('bins.index') }}">Back to Home</a></p>
</header>

<main>
    <form method="GET" action="{{ route('bins.mapByDate') }}">
        <label>Pick Collection Date
            <input type="date" name="date" value="{{ request('date', date('Y-m-d')) }}" required>
        </label>
        <button type="submit">🗺️ Show Collections</button>
    </form>

    @if(request('date'))
    <div class="date-info">
        <strong>📅 Showing collections for: {{ \Carbon\Carbon::parse(request('date'))->format('l, F j, Y') }}</strong>
        <div class="legend">
            <span class="dot" style="background:#28a745"></span> Food
            <span class="dot" style="background:#007bff"></span> Recycling  
            <span class="dot" style="background:#8b4513"></span> Garden
        </div>
    </div>
    @endif

    <div id="map"></div>
</main>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script>
    const params = new URLSearchParams(window.location.search);
    const date = params.get('date');
    
    const colorFor = (type) => {
        const colors = {
            'Food': '#28a745',                // Green
            'Recycling': '#007bff',           // Blue  
            'Garden': '#8b4513',              // Brown
            // Legacy types (for backward compatibility)
            'Food Waste': '#28a745',          // Green
            'Garden Waste': '#8b4513',        // Brown
            'Residual Waste': '#6c757d',      // Gray
        };
        return colors[type] || '#6c757d'; // Default to gray
    };

    function dotIcon(color){
        const html = `<span style="display:inline-block;width:14px;height:14px;border-radius:50%;background:${color};border:2px solid #fff;box-shadow:0 0 0 1px rgba(0,0,0,.25);"></span>`;
        return L.divIcon({ html, className: 'bin-dot', iconSize: [18, 18], iconAnchor: [9, 9], popupAnchor: [0, -9] });
    }

    async function loadData(){
        if(!date){ return []; }
        
        // Load all bins data and filter by date on client side
        const res = await fetch('{{ route('api.bins') }}');
        const items = await res.json();
        
        // Filter by the selected date
        const filteredItems = items.filter(item => {
            return item.collection_date === date && item.latitude && item.longitude;
        });
        
        return filteredItems;
    }

    async function init(){
        const map = L.map('map').setView([52.8586, -2.2524], 13); // Eccleshall center
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { 
            attribution: '&copy; OpenStreetMap contributors' 
        }).addTo(map);
        
        const items = await loadData();
        
        if(items.length){
            const cluster = L.markerClusterGroup({ 
                showCoverageOnHover: false, 
                spiderfyOnEveryZoom: true 
            });
            
            items.forEach(item => {
                const marker = L.marker([item.latitude, item.longitude], { 
                    icon: dotIcon(item.color),
                    title: `${item.bin_type} @ ${item.address}` 
                });
                
                marker.bindPopup(`
                    <strong>${item.bin_type}</strong><br>
                    <strong>Customer:</strong> ${item.customer_name}<br>
                    <strong>Phone:</strong> ${item.phone}<br>
                    <strong>Address:</strong> ${item.address}<br>
                    <strong>Date:</strong> ${item.collection_date}<br>
                    <strong>Time:</strong> ${item.collection_time}<br>
                    <strong>Status:</strong> <span style="color: ${item.status === 'Completed' ? 'green' : item.status === 'Pending' ? 'orange' : 'blue'}">${item.status}</span><br>
                    ${item.notes ? `<strong>Notes:</strong> ${item.notes}` : ''}
                `);
                
                cluster.addLayer(marker);
            });
            
            map.addLayer(cluster);
            map.fitBounds(cluster.getBounds().pad(0.1));
            
            // Update page info
            const dateInfoDiv = document.querySelector('.date-info');
            if (dateInfoDiv) {
                const binTypeCounts = {};
                items.forEach(item => {
                    binTypeCounts[item.bin_type] = (binTypeCounts[item.bin_type] || 0) + 1;
                });
                
                const countsText = Object.entries(binTypeCounts)
                    .map(([type, count]) => `${type}: ${count}`)
                    .join(', ');
                    
                const existingStrong = dateInfoDiv.querySelector('strong');
                existingStrong.innerHTML = `📅 Showing ${items.length} collections for: {{ request('date') ? \Carbon\Carbon::parse(request('date'))->format('l, F j, Y') : '' }}<br><small>${countsText}</small>`;
            }
        } else {
            if (date) {
                map.setView([52.8586, -2.2524], 13); // Eccleshall center
                
                // Show "no collections" message
                const popup = L.popup()
                    .setLatLng([52.8586, -2.2524])
                    .setContent(`<strong>No collections scheduled for ${date}</strong><br><small>Try selecting a different date</small>`)
                    .openOn(map);
            }
        }
    }
    
    // Initialize map when page loads
    init();
</script>

</body>
</html>


