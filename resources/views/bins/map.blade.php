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
        
        /* Date filtering panel */
        .filter-panel {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid #28a745;
        }
        
        .filter-tabs {
            display: flex;
            margin-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
            gap: 5px;
        }
        
        .filter-tab {
            background: none;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.2s;
            border-radius: 4px 4px 0 0;
        }
        
        .filter-tab.active {
            color: #28a745;
            border-bottom-color: #28a745;
            background: #f8f9fa;
        }
        
        .filter-tab:hover {
            background: #e9ecef;
        }
        
        .filter-content {
            display: none;
        }
        
        .filter-content.active {
            display: block;
        }
        
        .filter-controls {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .filter-group label {
            font-weight: 600;
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }
        
        .filter-group input, .filter-group select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .apply-filter-btn {
            background: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .apply-filter-btn:hover {
            background: #218838;
        }
        
        .clear-filter-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .clear-filter-btn:hover {
            background: #545b62;
        }
        
        .filter-summary {
            background: #e8f4fd;
            padding: 10px 15px;
            border-radius: 4px;
            margin-top: 15px;
            font-size: 14px;
            color: #0c5460;
            border-left: 3px solid #17a2b8;
        }
        
        @media (max-width: 768px) {
            .filter-tabs {
                flex-direction: column;
            }
            .filter-tab {
                border-bottom: 1px solid #dee2e6;
                border-radius: 4px;
                margin-bottom: 2px;
            }
            .filter-tab.active {
                border: 2px solid #28a745;
            }
            .filter-controls {
                flex-direction: column;
                align-items: stretch;
            }
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
    
    <!-- Date Filtering Panel -->
    <div class="filter-panel">
        <h3 style="margin-top: 0;">📅 Filter Collections by Date</h3>
        
        <div class="filter-tabs">
            <button class="filter-tab active" onclick="switchFilterTab('current-week')">📅 Current Week</button>
            <button class="filter-tab" onclick="switchFilterTab('next-week')">📅 Next Week</button>
            <button class="filter-tab" onclick="switchFilterTab('two-weeks')">📅 Next 2 Weeks</button>
            <button class="filter-tab" onclick="switchFilterTab('specific-day')">📅 Specific Day</button>
            <button class="filter-tab" onclick="switchFilterTab('date-range')">📅 Date Range</button>
            <button class="filter-tab" onclick="switchFilterTab('all-data')">📅 All Data</button>
        </div>

        <!-- Current Week Filter -->
        <div id="current-week-filter" class="filter-content active">
            <div class="filter-controls">
                <span style="color: #666;">Show collections for the current week (Monday to Sunday)</span>
                <button class="apply-filter-btn" onclick="applyFilter('current-week')">🔍 Show Current Week</button>
            </div>
        </div>

        <!-- Next Week Filter -->
        <div id="next-week-filter" class="filter-content">
            <div class="filter-controls">
                <span style="color: #666;">Show collections for next week (Monday to Sunday)</span>
                <button class="apply-filter-btn" onclick="applyFilter('next-week')">🔍 Show Next Week</button>
            </div>
        </div>

        <!-- Two Weeks Filter -->
        <div id="two-weeks-filter" class="filter-content">
            <div class="filter-controls">
                <span style="color: #666;">Show collections for the next 2 weeks from today</span>
                <button class="apply-filter-btn" onclick="applyFilter('two-weeks')">🔍 Show Next 2 Weeks</button>
            </div>
        </div>

        <!-- Specific Day Filter -->
        <div id="specific-day-filter" class="filter-content">
            <div class="filter-controls">
                <div class="filter-group">
                    <label>Select Date</label>
                    <input type="date" id="specific-date" value="">
                </div>
                <button class="apply-filter-btn" onclick="applyFilter('specific-day')">🔍 Show Day</button>
            </div>
        </div>

        <!-- Date Range Filter -->
        <div id="date-range-filter" class="filter-content">
            <div class="filter-controls">
                <div class="filter-group">
                    <label>From Date</label>
                    <input type="date" id="from-date" value="">
                </div>
                <div class="filter-group">
                    <label>To Date</label>
                    <input type="date" id="to-date" value="">
                </div>
                <button class="apply-filter-btn" onclick="applyFilter('date-range')">🔍 Show Range</button>
            </div>
        </div>

        <!-- All Data Filter -->
        <div id="all-data-filter" class="filter-content">
            <div class="filter-controls">
                <span style="color: #666;">Show all collections (no date filtering)</span>
                <button class="apply-filter-btn" onclick="applyFilter('all-data')">🔍 Show All Data</button>
            </div>
        </div>

        <div class="filter-summary" id="filter-summary" style="display: none;">
            <!-- Filter results will be shown here -->
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

    let allBinsData = []; // Store all bins data for filtering
    let currentFilter = null; // Track current filter

    async function loadBinsData(){
        const res = await fetch('{{ route('api.bins') }}');
        const items = await res.json();
        allBinsData = items.filter(i => i.latitude && i.longitude);
        return allBinsData;
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
        
        // Overlay layers (data layers only - no base layer selector needed)
        const overlayLayers = {
            ...binTypeLayers,
            [`📍 Allowed Areas (${areasData.length})`]: areasLayer
        };
        
        // Add layer control (no base layers, only overlays)
        L.control.layers(null, overlayLayers, {
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

    // Initialize default date values
    function initializeDateInputs() {
        const today = new Date();
        const todayString = today.toISOString().split('T')[0];
        
        document.getElementById('specific-date').value = todayString;
        document.getElementById('from-date').value = todayString;
        
        const twoWeeksLater = new Date(today);
        twoWeeksLater.setDate(today.getDate() + 14);
        document.getElementById('to-date').value = twoWeeksLater.toISOString().split('T')[0];
    }

    // Switch between filter tabs
    function switchFilterTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.filter-content').forEach(content => {
            content.classList.remove('active');
        });
        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.classList.remove('active');
        });

        // Show selected tab
        document.getElementById(tabName + '-filter').classList.add('active');
        event.target.classList.add('active');
    }

    // Get date range for different filter types
    function getDateRange(filterType) {
        const today = new Date();
        
        switch (filterType) {
            case 'current-week':
                const currentMonday = new Date(today);
                currentMonday.setDate(today.getDate() - ((today.getDay() + 6) % 7));
                const currentSunday = new Date(currentMonday);
                currentSunday.setDate(currentMonday.getDate() + 6);
                return { from: currentMonday, to: currentSunday };
                
            case 'next-week':
                const nextMonday = new Date(today);
                nextMonday.setDate(today.getDate() - ((today.getDay() + 6) % 7) + 7);
                const nextSunday = new Date(nextMonday);
                nextSunday.setDate(nextMonday.getDate() + 6);
                return { from: nextMonday, to: nextSunday };
                
            case 'two-weeks':
                const twoWeeksLater = new Date(today);
                twoWeeksLater.setDate(today.getDate() + 14);
                return { from: today, to: twoWeeksLater };
                
            case 'specific-day':
                const selectedDate = new Date(document.getElementById('specific-date').value);
                return { from: selectedDate, to: selectedDate };
                
            case 'date-range':
                const fromDate = new Date(document.getElementById('from-date').value);
                const toDate = new Date(document.getElementById('to-date').value);
                return { from: fromDate, to: toDate };
                
            case 'all-data':
            default:
                return null; // No filtering
        }
    }

    // Filter data by date range
    function filterDataByDateRange(data, dateRange) {
        if (!dateRange) {
            return data; // No filtering
        }

        return data.filter(item => {
            const itemDate = new Date(item.collection_date);
            return itemDate >= dateRange.from && itemDate <= dateRange.to;
        });
    }

    // Apply date filter
    function applyFilter(filterType) {
        currentFilter = filterType;
        const dateRange = getDateRange(filterType);
        const filteredData = filterDataByDateRange(allBinsData, dateRange);
        
        // Update map with filtered data
        updateMapWithFilteredData(filteredData);
        
        // Show filter summary
        showFilterSummary(filterType, filteredData, dateRange);
    }

    // Update map with filtered data
    function updateMapWithFilteredData(filteredData) {
        // Clear existing layers
        Object.values(binTypeLayers).forEach(layer => {
            map.removeLayer(layer);
        });

        // Recreate layers with filtered data
        binTypeLayers = createBinTypeLayers(filteredData);
        
        // Add filtered layers to map
        Object.values(binTypeLayers).forEach(layer => {
            map.addLayer(layer);
        });

        // Update layer control
        if (layerControl) {
            map.removeControl(layerControl);
        }
        
        const overlayLayers = {
            ...binTypeLayers,
            [`📍 Allowed Areas (${areasData.length})`]: areasLayer
        };
        
        layerControl = L.control.layers(null, overlayLayers, {
            position: 'topright',
            collapsed: false
        });
        layerControl.addTo(map);

        // Fit map to filtered data if there is any
        if (filteredData.length > 0) {
            const allMarkers = [];
            Object.values(binTypeLayers).forEach(cluster => {
                cluster.eachLayer(marker => allMarkers.push(marker));
            });
            
            if (allMarkers.length > 0) {
                const group = new L.featureGroup(allMarkers);
                map.fitBounds(group.getBounds().pad(0.1));
            }
        }
    }

    // Show filter summary
    function showFilterSummary(filterType, filteredData, dateRange) {
        const summaryDiv = document.getElementById('filter-summary');
        let summaryText = '';
        
        switch (filterType) {
            case 'current-week':
                summaryText = `Showing ${filteredData.length} collections for current week (${dateRange.from.toLocaleDateString()} - ${dateRange.to.toLocaleDateString()})`;
                break;
            case 'next-week':
                summaryText = `Showing ${filteredData.length} collections for next week (${dateRange.from.toLocaleDateString()} - ${dateRange.to.toLocaleDateString()})`;
                break;
            case 'two-weeks':
                summaryText = `Showing ${filteredData.length} collections for next 2 weeks (${dateRange.from.toLocaleDateString()} - ${dateRange.to.toLocaleDateString()})`;
                break;
            case 'specific-day':
                summaryText = `Showing ${filteredData.length} collections for ${dateRange.from.toLocaleDateString()}`;
                break;
            case 'date-range':
                summaryText = `Showing ${filteredData.length} collections from ${dateRange.from.toLocaleDateString()} to ${dateRange.to.toLocaleDateString()}`;
                break;
            case 'all-data':
                summaryText = `Showing all ${filteredData.length} collections (no date filter)`;
                break;
        }
        
        // Add bin type breakdown
        const binTypeCounts = {};
        filteredData.forEach(item => {
            binTypeCounts[item.bin_type] = (binTypeCounts[item.bin_type] || 0) + 1;
        });
        
        const breakdownText = Object.entries(binTypeCounts)
            .map(([type, count]) => `${type}: ${count}`)
            .join(', ');
        
        if (breakdownText) {
            summaryText += `<br><strong>Breakdown:</strong> ${breakdownText}`;
        }
        
        summaryDiv.innerHTML = summaryText;
        summaryDiv.style.display = 'block';
    }

    // Store global variables for updates
    let binTypeLayers = {};
    let areasLayer;
    let areasData = [];
    let layerControl;

    // Modified init function
    async function init(){
        const [binsData, areasDataResult] = await Promise.all([loadBinsData(), loadAreasData()]);
        areasData = areasDataResult;

        // Initialize map
        const map = L.map('map');
        const tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Create layers
        binTypeLayers = createBinTypeLayers(binsData);
        areasLayer = createAreasLayer(areasData);

        // Overlay layers (data layers only - no base layer selector needed)
        const overlayLayers = {
            ...binTypeLayers,
            [`📍 Allowed Areas (${areasData.length})`]: areasLayer
        };

        // Add layer control
        layerControl = L.control.layers(null, overlayLayers, {
            position: 'topright',
            collapsed: false
        });
        layerControl.addTo(map);

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

        // Initialize date inputs
        initializeDateInputs();

        // Make map globally accessible
        window.map = map;
        window.binTypeLayers = binTypeLayers;
        window.areasLayer = areasLayer;
        window.areasData = areasData;
        window.layerControl = layerControl;
    }

    init();
</script>
</body>
</html>


