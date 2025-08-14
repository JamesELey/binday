<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚛 Route Planner - BinDay</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8f9fa;
            color: #333;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header .subtitle {
            opacity: 0.9;
            font-size: 14px;
        }

        .nav-links {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 20px;
            background: rgba(255,255,255,0.2);
            transition: background 0.3s;
            font-size: 14px;
        }

        .nav-links a:hover, .nav-links a.active {
            background: rgba(255,255,255,0.3);
        }

        .main-container {
            display: grid;
            grid-template-columns: 350px 1fr;
            height: calc(100vh - 100px);
            gap: 0;
        }

        .sidebar {
            background: white;
            border-right: 1px solid #e0e0e0;
            overflow-y: auto;
            padding: 20px;
        }

        .map-container {
            position: relative;
        }

        #map {
            height: 100%;
            width: 100%;
        }

        .control-panel {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .control-panel h3 {
            margin-bottom: 15px;
            color: #333;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            font-size: 14px;
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }

        .btn {
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-warning {
            background: #ffc107;
            color: #212529;
        }

        .btn-warning:hover {
            background: #e0a800;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        .btn-block {
            width: 100%;
            justify-content: center;
        }

        .collections-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .collection-item {
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .collection-item:hover {
            background: #e9ecef;
            border-color: #007bff;
        }

        .collection-item.selected {
            background: #e3f2fd;
            border-color: #007bff;
        }

        .collection-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .customer-name {
            font-weight: 600;
            color: #333;
        }

        .collection-time {
            font-size: 12px;
            color: #666;
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 4px;
        }

        .collection-details {
            font-size: 13px;
            color: #666;
        }

        .bin-type {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            margin-top: 4px;
        }

        .bin-food { background: #28a745; color: white; }
        .bin-recycling { background: #007bff; color: white; }
        .bin-garden { background: #8b4513; color: white; }
        .bin-residual { background: #6c757d; color: white; }

        .status-badge {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
        }

        .status-pending { background: #ffc107; color: #212529; }
        .status-confirmed { background: #17a2b8; color: white; }
        .status-collected { background: #28a745; color: white; }
        .status-cancelled { background: #dc3545; color: white; }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 6px;
            text-align: center;
        }

        .stat-number {
            font-size: 20px;
            font-weight: 600;
            color: #007bff;
        }

        .stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
        }

        .route-panel {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .route-item {
            display: flex;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .route-order {
            background: #007bff;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            margin-right: 12px;
        }

        .route-details {
            flex: 1;
        }

        .route-name {
            font-weight: 600;
            margin-bottom: 2px;
        }

        .route-address {
            font-size: 12px;
            color: #666;
        }

        .route-distance {
            font-size: 12px;
            color: #007bff;
            margin-left: 12px;
        }

        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }

        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        @media (max-width: 768px) {
            .main-container {
                grid-template-columns: 1fr;
                grid-template-rows: auto 1fr;
            }
            
            .sidebar {
                max-height: 40vh;
                border-right: none;
                border-bottom: 1px solid #e0e0e0;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚛 Route Planner</h1>
        <p class="subtitle">Plan and optimize collection routes for {{ $selectedDate }}</p>
        
        <div class="nav-links">
            <a href="{{ route('bins.index') }}">🏠 Home</a>
            <a href="{{ route('collections.index') }}">📋 Collections</a>
            <a href="{{ route('bins.map') }}">🗺️ Map</a>
            <a href="{{ route('routes.index') }}" class="active">🚛 Routes</a>
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('areas.index') }}">📍 Areas</a>
            @endif
        </div>
    </div>

    <div class="main-container">
        <div class="sidebar">
            <!-- Date and Area Selection -->
            <div class="control-panel">
                <h3>📅 Select Date & Areas</h3>
                
                <div class="form-group">
                    <label for="route-date">Collection Date</label>
                    <input type="date" id="route-date" value="{{ $selectedDate }}">
                </div>

                <div class="form-group">
                    <label for="area-filter">Filter by Areas</label>
                    <select id="area-filter" multiple>
                        @foreach($workerAreas as $area)
                            <option value="{{ $area->id }}">{{ $area->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="button" class="btn btn-primary btn-block" onclick="loadCollections()">
                    🔍 Load Collections
                </button>
            </div>

            <!-- Statistics -->
            <div class="control-panel">
                <h3>📊 Statistics</h3>
                <div class="stats-grid" id="stats-container">
                    <div class="stat-card">
                        <div class="stat-number" id="total-collections">0</div>
                        <div class="stat-label">Total Collections</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number" id="selected-collections">0</div>
                        <div class="stat-label">Selected</div>
                    </div>
                </div>
            </div>

            <!-- Collections List -->
            <div class="control-panel">
                <h3>📦 Collections</h3>
                <div class="collections-list" id="collections-container">
                    <div class="loading">Select date and click "Load Collections" to start</div>
                </div>
                
                <button type="button" class="btn btn-success btn-block" onclick="optimizeRoute()" id="optimize-btn" style="display: none;">
                    🎯 Optimize Route
                </button>
            </div>

            <!-- Optimized Route -->
            <div class="route-panel" id="route-container" style="display: none;">
                <h3>🚛 Optimized Route</h3>
                <div id="route-list"></div>
                <div style="margin-top: 15px; text-align: center;">
                    <button type="button" class="btn btn-warning btn-sm" onclick="clearRoute()">
                        🔄 Clear Route
                    </button>
                </div>
            </div>
        </div>

        <div class="map-container">
            <div id="map"></div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Global variables
        let map;
        let collectionsData = [];
        let selectedCollections = [];
        let routeLayer;
        let markersLayer;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Initialize map
        function initMap() {
            map = L.map('map').setView([52.8586, -2.2524], 13); // Eccleshall center
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            markersLayer = L.layerGroup().addTo(map);
        }

        // Load collections for selected date and areas
        async function loadCollections() {
            const date = document.getElementById('route-date').value;
            const areaSelect = document.getElementById('area-filter');
            const selectedAreas = Array.from(areaSelect.selectedOptions).map(option => option.value);
            
            const container = document.getElementById('collections-container');
            container.innerHTML = '<div class="loading">Loading collections...</div>';
            
            try {
                const response = await fetch(`{{ route('api.routes.collections') }}?date=${date}&areas=${selectedAreas.join(',')}`);
                const data = await response.json();
                
                if (data.success) {
                    collectionsData = data.collections;
                    displayCollections(data.collections);
                    updateStats();
                    displayCollectionsOnMap(data.collections);
                } else {
                    container.innerHTML = '<div class="alert alert-warning">No collections found for selected criteria</div>';
                }
            } catch (error) {
                console.error('Error loading collections:', error);
                container.innerHTML = '<div class="alert alert-warning">Error loading collections</div>';
            }
        }

        // Display collections in sidebar
        function displayCollections(collections) {
            const container = document.getElementById('collections-container');
            
            if (collections.length === 0) {
                container.innerHTML = '<div class="alert alert-info">No collections found for this date</div>';
                return;
            }
            
            const html = collections.map(collection => `
                <div class="collection-item" onclick="toggleCollection(${collection.id})" data-id="${collection.id}">
                    <div class="collection-header">
                        <span class="customer-name">${collection.customer_name}</span>
                        ${collection.collection_time ? `<span class="collection-time">${collection.collection_time}</span>` : ''}
                    </div>
                    <div class="collection-details">
                        ${collection.address}
                    </div>
                    <div style="margin-top: 8px;">
                        <span class="bin-type bin-${collection.bin_type.toLowerCase().replace(' ', '')}">${collection.bin_type}</span>
                        <span class="status-badge status-${collection.status}">${collection.status}</span>
                    </div>
                </div>
            `).join('');
            
            container.innerHTML = html;
        }

        // Display collections on map
        function displayCollectionsOnMap(collections) {
            markersLayer.clearLayers();
            
            collections.forEach(collection => {
                const isSelected = selectedCollections.includes(collection.id);
                const icon = L.divIcon({
                    className: 'collection-marker',
                    html: `<div style="background: ${isSelected ? '#28a745' : '#007bff'}; color: white; width: 25px; height: 25px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">${collection.id}</div>`,
                    iconSize: [25, 25],
                    iconAnchor: [12.5, 12.5]
                });
                
                const marker = L.marker([collection.latitude, collection.longitude], { icon })
                    .bindPopup(`
                        <strong>${collection.customer_name}</strong><br>
                        ${collection.address}<br>
                        <span style="background: #f0f0f0; padding: 2px 6px; border-radius: 4px; font-size: 12px;">${collection.bin_type}</span>
                        <span style="background: #007bff; color: white; padding: 2px 6px; border-radius: 4px; font-size: 12px; margin-left: 4px;">${collection.status}</span>
                    `)
                    .on('click', () => toggleCollection(collection.id));
                
                markersLayer.addLayer(marker);
            });
        }

        // Toggle collection selection
        function toggleCollection(collectionId) {
            const index = selectedCollections.indexOf(collectionId);
            const item = document.querySelector(`[data-id="${collectionId}"]`);
            
            if (index > -1) {
                selectedCollections.splice(index, 1);
                item.classList.remove('selected');
            } else {
                selectedCollections.push(collectionId);
                item.classList.add('selected');
            }
            
            updateStats();
            displayCollectionsOnMap(collectionsData);
            
            // Show/hide optimize button
            const optimizeBtn = document.getElementById('optimize-btn');
            optimizeBtn.style.display = selectedCollections.length > 0 ? 'block' : 'none';
        }

        // Update statistics
        function updateStats() {
            document.getElementById('total-collections').textContent = collectionsData.length;
            document.getElementById('selected-collections').textContent = selectedCollections.length;
        }

        // Optimize route
        async function optimizeRoute() {
            if (selectedCollections.length === 0) {
                alert('Please select collections to optimize route');
                return;
            }
            
            try {
                const response = await fetch('{{ route('api.routes.optimize') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        collection_ids: selectedCollections,
                        start_lat: 52.8586,
                        start_lng: -2.2524
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    displayOptimizedRoute(data.route);
                    displayRouteOnMap(data.route);
                } else {
                    alert('Error optimizing route: ' + data.message);
                }
            } catch (error) {
                console.error('Error optimizing route:', error);
                alert('Error optimizing route');
            }
        }

        // Display optimized route in sidebar
        function displayOptimizedRoute(route) {
            const container = document.getElementById('route-container');
            const listContainer = document.getElementById('route-list');
            
            const html = route.map(item => `
                <div class="route-item">
                    <div class="route-order">${item.order}</div>
                    <div class="route-details">
                        <div class="route-name">${item.type === 'start' ? item.name : item.customer_name}</div>
                        <div class="route-address">${item.type === 'start' ? 'Depot/Starting Point' : item.address}</div>
                    </div>
                    ${item.distance_from_previous ? `<div class="route-distance">${item.distance_from_previous.toFixed(2)}km</div>` : ''}
                </div>
            `).join('');
            
            listContainer.innerHTML = html;
            container.style.display = 'block';
        }

        // Display route on map
        function displayRouteOnMap(route) {
            if (routeLayer) {
                map.removeLayer(routeLayer);
            }
            
            const coordinates = route.map(item => [item.latitude, item.longitude]);
            
            routeLayer = L.polyline(coordinates, {
                color: '#dc3545',
                weight: 4,
                opacity: 0.8
            }).addTo(map);
            
            // Fit map to route
            map.fitBounds(routeLayer.getBounds(), { padding: [20, 20] });
        }

        // Clear route
        function clearRoute() {
            selectedCollections = [];
            document.querySelectorAll('.collection-item').forEach(item => {
                item.classList.remove('selected');
            });
            
            if (routeLayer) {
                map.removeLayer(routeLayer);
            }
            
            document.getElementById('route-container').style.display = 'none';
            document.getElementById('optimize-btn').style.display = 'none';
            
            updateStats();
            displayCollectionsOnMap(collectionsData);
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            
            // Auto-load collections for today
            loadCollections();
            
            // Handle date change
            document.getElementById('route-date').addEventListener('change', loadCollections);
        });
    </script>
</body>
</html>
