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
        body { max-width: 1200px; margin: 1rem auto; }
        #map { height: 700px; border-radius: 8px; }
        .legend { display:flex; gap: .5rem; align-items:center; }
        .dot { width: 12px; height: 12px; border-radius: 50%; display:inline-block; }
        
        /* Layer control improvements */
        .leaflet-control-layers {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border: none;
            padding: 10px;
            min-width: 200px;
        }
        
        .leaflet-control-layers-expanded {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .leaflet-control-layers label {
            font-weight: 500;
            margin: 5px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .leaflet-control-layers-separator {
            border-top: 1px solid #ddd;
            margin: 8px 0;
        }
        
        /* Improve cluster icons */
        .marker-cluster {
            border-radius: 50%;
        }
        
        /* Info panel */
        .map-info {
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
    <h2>Bin Collections Map</h2>
    <p><a href="{{ route('bins.index') }}">Back to list</a></p>
</header>

<main>
    <div class="grid">
        <div>
            <div class="legend"><span class="dot" style="background:#28a745"></span> Food</div>
            <div class="legend"><span class="dot" style="background:#007bff"></span> Recycling</div>
            <div class="legend"><span class="dot" style="background:#8b4513"></span> Garden</div>
        </div>
        <div class="muted">
            Use the layer controls in the top-right to toggle different bin types and allowed areas. 
            Each area has its own supported bin types. Default types are Food (green), Recycling (blue), and Garden (brown).
        </div>
    </div>
    
    <div class="map-info">
        <strong>🎛️ Layer Controls:</strong> Use the controls in the top-right corner of the map to:
        <ul style="margin: 10px 0 0 20px;">
            <li><strong>Toggle bin types:</strong> Show/hide different waste collection types</li>
            <li><strong>Toggle areas:</strong> Show/hide allowed service areas and boundaries</li>
            <li><strong>Cluster view:</strong> Markers automatically cluster when zoomed out for better visibility</li>
        </ul>
    </div>

    <div id="map"></div>
</main>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<script>
    const colorFor = (type) => {
        const colors = {
            'Food': '#28a745',                // Green
            'Recycling': '#007bff',           // Blue  
            'Garden': '#8b4513',              // Brown
            // Legacy types (for backward compatibility)
            'Food Waste': '#28a745',          // Green
            'Garden Waste': '#8b4513',        // Brown
            'Residual Waste': '#6c757d',      // Gray
            'Glass': '#17a2b8',              // Teal
            'Paper': '#ffc107',              // Yellow
            'Plastic': '#e83e8c',            // Pink
        };
        return colors[type] || '#6c757d'; // Default to gray
    };

    function dotIcon(color){
        const html = `<span style="display:inline-block;width:14px;height:14px;border-radius:50%;background:${color};border:2px solid #fff;box-shadow:0 0 0 1px rgba(0,0,0,.25);"></span>`;
        return L.divIcon({ html, className: 'bin-dot', iconSize: [18, 18], iconAnchor: [9, 9], popupAnchor: [0, -9] });
    }

    async function loadBinsData(){
        const res = await fetch('{{ route('api.bins') }}');
        const items = await res.json();
        return items.filter(i => i.latitude && i.longitude);
    }

    async function loadAreasData(){
        const res = await fetch('{{ route('api.areas') }}');
        const data = await res.json();
        return data.areas || [];
    }

    function createBinTypeLayers(items) {
        const binTypes = [...new Set(items.map(i => i.bin_type))];
        const layers = {};
        
        binTypes.forEach(binType => {
            const binItems = items.filter(i => i.bin_type === binType);
            const cluster = L.markerClusterGroup({ 
                showCoverageOnHover: false, 
                spiderfyOnEveryZoom: true,
                iconCreateFunction: function(cluster) {
                    const color = colorFor(binType);
                    return new L.DivIcon({
                        html: `<div style="background-color: ${color}; color: white; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; font-weight: bold; border: 2px solid white; box-shadow: 0 0 0 1px rgba(0,0,0,0.3);">${cluster.getChildCount()}</div>`,
                        className: 'marker-cluster',
                        iconSize: new L.Point(40, 40)
                    });
                }
            });
            
            binItems.forEach(i => {
                const marker = L.marker([i.latitude, i.longitude], { 
                    icon: dotIcon(i.color),
                    title: `${i.bin_type} @ ${i.address}` 
                });
                marker.bindPopup(`
                    <strong>${i.bin_type}</strong><br>
                    <strong>Customer:</strong> ${i.customer_name}<br>
                    <strong>Phone:</strong> ${i.phone}<br>
                    <strong>Address:</strong> ${i.address}<br>
                    <strong>Date:</strong> ${i.collection_date}<br>
                    <strong>Time:</strong> ${i.collection_time}<br>
                    <strong>Status:</strong> <span style="color: ${i.status === 'Completed' ? 'green' : i.status === 'Pending' ? 'orange' : 'blue'}">${i.status}</span><br>
                    ${i.notes ? `<strong>Notes:</strong> ${i.notes}` : ''}
                `);
                cluster.addLayer(marker);
            });
            
            layers[`${binType} (${binItems.length})`] = cluster;
        });
        
        return layers;
    }

    function createAreasLayer(areas) {
        const areasGroup = L.layerGroup();
        
        areas.forEach(area => {
            if (area.type === 'map' && area.coordinates && area.coordinates.length > 0) {
                // Create polygon for map-based areas
                const polygon = L.polygon(area.coordinates, {
                    color: area.active ? '#28a745' : '#6c757d',
                    fillColor: area.active ? '#28a745' : '#6c757d',
                    fillOpacity: 0.2,
                    weight: 2
                }).addTo(areasGroup);
                
                polygon.bindPopup(`
                    <strong>${area.name}</strong><br/>
                    ${area.description || ''}<br/>
                    <small>Status: ${area.active ? 'Active' : 'Inactive'}</small><br/>
                    <small>Type: Map Area</small>
                `);
            } else if (area.type === 'postcode' && area.postcodes) {
                // Create markers for postcode-based areas (positioned around London)
                const marker = L.marker([51.5074, -0.1278], {
                    icon: L.divIcon({
                        className: 'postcode-marker',
                        html: `<div style="background: ${area.active ? '#007bff' : '#6c757d'}; color: white; padding: 5px; border-radius: 3px; font-size: 12px; white-space: nowrap;">${area.name}</div>`,
                        iconSize: [100, 30]
                    })
                }).addTo(areasGroup);
                
                marker.bindPopup(`
                    <strong>${area.name}</strong><br/>
                    ${area.description || ''}<br/>
                    <small>Postcodes: ${area.postcodes}</small><br/>
                    <small>Status: ${area.active ? 'Active' : 'Inactive'}</small><br/>
                    <small>Type: Postcode Area</small>
                `);
            }
        });
        
        return areasGroup;
    }

    async function init(){
        const [binsData, areasData] = await Promise.all([loadBinsData(), loadAreasData()]);
        
        // Initialize map
        const map = L.map('map');
        const tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Create layers
        const binTypeLayers = createBinTypeLayers(binsData);
        const areasLayer = createAreasLayer(areasData);
        
        // Base layers (map tiles)
        const baseLayers = {
            "OpenStreetMap": tiles
        };
        
        // Overlay layers (data layers)
        const overlayLayers = {
            ...binTypeLayers,
            [`📍 Allowed Areas (${areasData.length})`]: areasLayer
        };
        
        // Add layer control
        L.control.layers(baseLayers, overlayLayers, {
            position: 'topright',
            collapsed: false
        }).addTo(map);
        
        // Add default layers (show all bin types and areas by default)
        Object.values(binTypeLayers).forEach(layer => map.addLayer(layer));
        map.addLayer(areasLayer); // Add areas layer by default
        
        // Set map view
        if(binsData.length > 0){
            // Get bounds from all bin markers
            const allMarkers = [];
            Object.values(binTypeLayers).forEach(cluster => {
                cluster.eachLayer(marker => allMarkers.push(marker));
            });
            
            if (allMarkers.length > 0) {
                const group = new L.featureGroup(allMarkers);
                map.fitBounds(group.getBounds().pad(0.1));
            }
        } else {
            map.setView([52.8586, -2.2524], 13); // Eccleshall center fallback
        }
    }

    init();
</script>
</body>
</html>


