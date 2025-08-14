<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Edit Area #{{ $area->id }} - Bin Collection Admin</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css" />
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .nav-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .nav-links a {
            background: #007cba;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
            font-size: 14px;
        }
        .nav-links a:hover {
            background: #005a87;
        }
        .content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .form-group textarea {
            height: 80px;
            resize: vertical;
        }
        .form-row {
            display: flex;
            gap: 20px;
        }
        .form-row .form-group {
            flex: 1;
        }
        .submit-btn {
            background: #007bff;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
        }
        .submit-btn:hover {
            background: #0056b3;
        }
        .required {
            color: #dc3545;
        }
        .current-value {
            background-color: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        /* Map Editor Styles */
        .editor-tabs {
            display: flex;
            margin-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }
        .tab-button {
            background: none;
            border: none;
            padding: 15px 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.2s;
        }
        .tab-button.active {
            color: #007bff;
            border-bottom-color: #007bff;
            background: #f8f9fa;
        }
        .tab-button:hover {
            background: #e9ecef;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        
        #map {
            height: 500px;
            border-radius: 8px;
            border: 2px solid #dee2e6;
            margin-bottom: 20px;
        }
        
        .map-info {
            background: #e8f4fd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        
        .coordinates-display {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            border: 1px solid #dee2e6;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary { background: #007bff; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        
        .btn:hover { opacity: 0.9; }
        
        @media (max-width: 768px) {
            .form-row, .action-buttons, .editor-tabs {
                flex-direction: column;
            }
            .tab-button {
                border-bottom: 1px solid #dee2e6;
                border-radius: 4px;
                margin-bottom: 5px;
            }
            .tab-button.active {
                border: 2px solid #007bff;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>✏️ Edit Area #{{ $area->id }}</h1>
        <p>Modify the area coverage using the map editor or postcode settings.</p>
        
        <div class="nav-links">
            <a href="{{ route('bins.index') }}">🏠 Home</a>
            <a href="{{ route('areas.index') }}">🗺️ Manage Areas</a>
            <a href="{{ route('collections.index') }}">📋 Collections</a>
            <a href="{{ route('api.areas') }}" target="_blank">📊 API Data</a>
        </div>
    </div>

    <div class="content">
        <div class="current-value">
            <h3>📋 Current Area Details</h3>
            <div class="form-row">
                <div>
                    <p><strong>Name:</strong> {{ $area->name }}</p>
                    <p><strong>Status:</strong> {{ $area->active ? '✅ Active' : '❌ Inactive' }}</p>
                </div>
                <div>
                    <p><strong>Type:</strong> 
                        @if($area->type === 'polygon')
                            🗺️ Map Polygon ({{ count($area->coordinates ?? []) }} points)
                        @else
                            📮 Postcode Areas
                        @endif
                    </p>
                    <p><strong>Coverage:</strong> 
                        @if($area->type === 'polygon')
                            Geographic boundaries
                        @else
                            {{ $area->postcodes ?? 'No postcodes' }}
                        @endif
                    </p>
                </div>
            </div>
            @if($area->description)
                <p><strong>Description:</strong> {{ $area->description }}</p>
            @endif
        </div>

        @if(session('success'))
            <div style="padding: 15px; background-color: #d4edda; color: #155724; border-radius: 5px; margin-bottom: 20px;">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div style="padding: 15px; background-color: #f8d7da; color: #721c24; border-radius: 5px; margin-bottom: 20px;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Editor Tabs -->
        <div class="editor-tabs">
            <button class="tab-button active" onclick="switchTab('basic')">📝 Basic Info</button>
            @if($area->type === 'polygon')
                <button class="tab-button" onclick="switchTab('map')">🗺️ Edit Polygon</button>
            @endif
            <button class="tab-button" onclick="switchTab('postcodes')">📮 Postcodes</button>
        </div>

        <!-- Basic Information Tab -->
        <div id="basic-tab" class="tab-content active">
            <h2>Update Basic Information</h2>
            <form action="{{ route('areas.update', $area->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Area Name <span class="required">*</span></label>
                        <input type="text" id="name" name="name" value="{{ old('name', $area->name) }}" required>
                    </div>
                    <div class="form-group">
                        <label for="active">Status <span class="required">*</span></label>
                        <select id="active" name="active" required>
                            <option value="1" {{ old('active', $area->active) == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('active', $area->active) == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Brief description of this area coverage">{{ old('description', $area->description) }}</textarea>
                </div>

                <button type="submit" class="submit-btn">💾 Update Basic Info</button>
            </form>
        </div>

        <!-- Map Editor Tab -->
        @if($area->type === 'polygon')
        <div id="map-tab" class="tab-content">
            <h2>🗺️ Edit Area Polygon</h2>
            <div class="map-info">
                <strong>📍 Interactive Map Editor</strong><br>
                Current polygon has {{ count($area->coordinates ?? []) }} coordinate points. 
                Draw a new polygon to replace the current boundaries.
            </div>
            
            <div id="map"></div>
            
            <div class="coordinates-display">
                <strong>📊 Polygon Coordinates:</strong>
                <div id="coordinates">{{ $area->coordinates ? json_encode($area->coordinates) : 'No coordinates defined' }}</div>
            </div>

            <div class="action-buttons">
                <button class="btn btn-primary" onclick="startDrawing()">✏️ Draw New Polygon</button>
                <button class="btn btn-warning" onclick="loadCurrentPolygon()">🔄 Reset to Current</button>
                <button class="btn btn-success" onclick="savePolygon()" id="save-polygon-btn" disabled>💾 Save New Polygon</button>
                <button class="btn btn-danger" onclick="clearMap()">🗑️ Clear Map</button>
            </div>
        </div>
        @endif

        <!-- Postcodes Tab -->
        <div id="postcodes-tab" class="tab-content">
            <h2>📮 Postcode Management</h2>
            @if($area->type === 'polygon')
                <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #ffc107;">
                    <strong>⚠️ Note:</strong> This area is currently map-based. Converting to postcode-based will replace the drawn polygon with postcode areas.
                </div>
            @endif
            
            <form action="{{ route('areas.update', $area->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label for="postcodes">Postcodes (comma-separated)</label>
                    <input type="text" id="postcodes" name="postcodes" 
                           value="{{ old('postcodes', $area->postcodes ?? '') }}" 
                           placeholder="e.g., EC1, EC2, EC3, WC1, WC2">
                    <small style="color: #6c757d;">Enter postcode areas separated by commas (e.g., EC1, EC2, N1, N2)</small>
                </div>

                <input type="hidden" name="update_type" value="postcodes">
                <button type="submit" class="submit-btn">💾 Update Postcodes</button>
            </form>
            
            @if($area->type === 'polygon' && isset($area->postcodes) && $area->postcodes)
                <div style="margin-top: 20px;">
                    <button class="btn btn-warning" onclick="convertToPostcodeArea()">
                        🔄 Convert to Postcode-Based Area
                    </button>
                </div>
            @endif
        </div>

        <div style="margin-top: 30px; padding: 20px; background: #fff3cd; border-radius: 5px;">
            <h3>💡 Area Management Guidelines</h3>
            <ul>
                <li><strong>Map Polygons:</strong> Draw accurate boundaries for precise geographic coverage</li>
                <li><strong>Postcode Areas:</strong> Use main postcode areas (e.g., EC1, N1, SW1) not full postcodes</li>
                <li><strong>Active Status:</strong> Only active areas will be used for service validation</li>
                <li><strong>Customer Impact:</strong> Changes will affect new bookings immediately</li>
                <li><strong>Testing:</strong> Test coverage using the API endpoint after updates</li>
            </ul>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
    <script>
        let map, drawnItems, drawControl, currentPolygon;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function switchTab(tabName) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active');
            });

            // Show selected tab
            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.classList.add('active');

            // Initialize map if switching to map tab
            if (tabName === 'map' && !map) {
                setTimeout(initMap, 100);
            }
        }

        function initMap() {
            if (map) return;

            // Initialize map centered on UK
            map = L.map('map').setView([52.8586, -2.2524], 10);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Create layer for drawings
            drawnItems = new L.FeatureGroup();
            map.addLayer(drawnItems);

            // Load current polygon if it exists
            loadCurrentPolygon();

            // Setup draw control
            drawControl = new L.Control.Draw({
                position: 'topright',
                edit: {
                    featureGroup: drawnItems,
                    remove: true
                },
                draw: {
                    polygon: {
                        allowIntersection: false,
                        showArea: true,
                        drawError: {
                            color: '#e1e100',
                            message: '<strong>Error:</strong> Shape edges cannot cross!'
                        }
                    },
                    polyline: false,
                    rectangle: false,
                    circle: false,
                    marker: false,
                    circlemarker: false
                }
            });
            map.addControl(drawControl);

            // Handle drawing events
            map.on(L.Draw.Event.CREATED, function (e) {
                const layer = e.layer;
                drawnItems.clearLayers(); // Clear existing polygons
                drawnItems.addLayer(layer);
                currentPolygon = layer;
                updateCoordinatesDisplay();
                document.getElementById('save-polygon-btn').disabled = false;
            });

            map.on(L.Draw.Event.EDITED, function (e) {
                updateCoordinatesDisplay();
                document.getElementById('save-polygon-btn').disabled = false;
            });
        }

        function loadCurrentPolygon() {
            const coordinates = @json($area->coordinates ?? []);
            
            if (coordinates && coordinates.length > 0) {
                drawnItems.clearLayers();
                currentPolygon = L.polygon(coordinates, {
                    color: '#007bff',
                    fillColor: '#007bff',
                    fillOpacity: 0.2
                }).addTo(drawnItems);
                
                map.fitBounds(currentPolygon.getBounds().pad(0.1));
                updateCoordinatesDisplay();
            }
        }

        function startDrawing() {
            // Clear existing drawings
            drawnItems.clearLayers();
            currentPolygon = null;
            document.getElementById('coordinates').textContent = 'Draw a polygon to see coordinates...';
            document.getElementById('save-polygon-btn').disabled = true;
        }

        function clearMap() {
            drawnItems.clearLayers();
            currentPolygon = null;
            document.getElementById('coordinates').textContent = 'No polygon drawn';
            document.getElementById('save-polygon-btn').disabled = true;
        }

        function updateCoordinatesDisplay() {
            if (currentPolygon) {
                const coords = currentPolygon.getLatLngs()[0].map(latlng => [latlng.lat, latlng.lng]);
                document.getElementById('coordinates').textContent = JSON.stringify(coords, null, 2);
            }
        }

        async function savePolygon() {
            if (!currentPolygon) {
                alert('Please draw a polygon first');
                return;
            }

            const coordinates = currentPolygon.getLatLngs()[0].map(latlng => [latlng.lat, latlng.lng]);
            
            try {
                const response = await fetch(`/areas/{{ $area->id }}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        coordinates: coordinates,
                        update_type: 'coordinates'
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    alert('Polygon updated successfully!');
                    document.getElementById('save-polygon-btn').disabled = true;
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to save polygon'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error saving polygon');
            }
        }

        async function convertToPostcodeArea() {
            if (!confirm('Convert this map-based area to postcode-based?\n\nThis will:\n• Remove the current polygon\n• Switch to postcode validation\n• Use the postcodes specified above\n\nContinue?')) {
                return;
            }

            try {
                const postcodes = document.getElementById('postcodes').value;
                if (!postcodes.trim()) {
                    alert('Please enter postcodes first');
                    return;
                }

                const response = await fetch(`/areas/{{ $area->id }}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        type: 'postcode',
                        postcodes: postcodes,
                        coordinates: null,
                        update_type: 'convert_to_postcode'
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    alert('Area converted to postcode-based successfully!');
                    window.location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Failed to convert area'));
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error converting area');
            }
        }
    </script>
</body>
</html>