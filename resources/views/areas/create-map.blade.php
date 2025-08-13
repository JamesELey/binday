<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Draw Allowed Area - Bin Collection Admin</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css" />
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 1400px;
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
        .map-container {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 20px;
            margin-top: 20px;
        }
        #map {
            height: 600px;
            border-radius: 8px;
            border: 2px solid #ddd;
        }
        .controls {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            height: fit-content;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .coordinates-display {
            background: #e9ecef;
            padding: 10px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
            max-height: 150px;
            overflow-y: auto;
        }
        .postcode-test {
            background: #fff3cd;
            padding: 15px;
            border-radius: 5px;
            margin-top: 15px;
        }
        @media (max-width: 768px) {
            .map-container {
                grid-template-columns: 1fr;
            }
            #map {
                height: 400px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🗺️ Draw Allowed Area</h1>
        <p>Use the map tools to draw a polygon area where bin collection services are available.</p>
        
        <div class="nav-links">
            <a href="{{ route('bins.index') }}">🏠 Home</a>
            <a href="{{ route('areas.index') }}">🗺️ Manage Areas</a>
            <a href="{{ route('collections.index') }}">📋 Collections</a>
            <a href="{{ route('api.areas') }}" target="_blank">📊 API Data</a>
        </div>
    </div>

    <div class="content">
        <h2>Interactive Area Drawing</h2>
        <p>Use the drawing tools on the map to create a polygon area. Click points to create the boundary of your service area.</p>

        <div class="map-container">
            <div id="map"></div>
            
            <div class="controls">
                <h3>Area Details</h3>
                <form id="areaForm">
                    <div class="form-group">
                        <label for="area_name">Area Name</label>
                        <input type="text" id="area_name" name="area_name" placeholder="e.g., Central London" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" placeholder="Brief description of this area"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="active">Status</label>
                        <select id="active" name="active">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Map Controls</label>
                        <button type="button" class="btn btn-primary" onclick="startDrawing()">🖊️ Draw Area</button>
                        <button type="button" class="btn btn-warning" onclick="clearDrawing()">🗑️ Clear</button>
                    </div>
                    
                    <div class="form-group">
                        <label>Coordinates</label>
                        <div id="coordinates" class="coordinates-display">
                            Draw a polygon to see coordinates...
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-success" onclick="saveArea()" disabled id="saveBtn">💾 Save Area</button>
                </form>
                
                <div class="postcode-test">
                    <h4>🧪 Test Postcode</h4>
                    <div class="form-group">
                        <input type="text" id="test_postcode" placeholder="e.g., EC1A 1BB" style="margin-bottom: 10px;">
                        <button type="button" class="btn btn-primary" onclick="testPostcode()">Test</button>
                    </div>
                    <div id="test_result"></div>
                </div>
            </div>
        </div>

        <div style="margin-top: 30px; padding: 20px; background: #d1ecf1; border-radius: 5px;">
            <h3>📚 How to Use</h3>
            <ol>
                <li><strong>Start Drawing:</strong> Click "Draw Area" button to activate polygon drawing</li>
                <li><strong>Create Polygon:</strong> Click points on the map to create your area boundary</li>
                <li><strong>Complete Shape:</strong> Click the first point again or double-click to finish</li>
                <li><strong>Test Coverage:</strong> Enter a postcode to test if it's within the drawn area</li>
                <li><strong>Save Area:</strong> Fill in the area details and click "Save Area"</li>
            </ol>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
    <script>
        let map, drawnItems, drawControl, currentPolygon;

        // Initialize map
        function initMap() {
            map = L.map('map').setView([52.86, -2.25], 12); // Eccleshall center

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Initialize drawing features
            drawnItems = new L.FeatureGroup();
            map.addLayer(drawnItems);

            // Load existing areas
            loadExistingAreas();

            drawControl = new L.Control.Draw({
                position: 'topright',
                draw: {
                    polygon: {
                        allowIntersection: false,
                        showArea: true,
                        drawError: {
                            color: '#e1e100',
                            message: '<strong>Error:</strong> Shape edges cannot cross!'
                        },
                        shapeOptions: {
                            color: '#007bff',
                            fillColor: '#007bff',
                            fillOpacity: 0.3
                        }
                    },
                    rectangle: false,
                    circle: false,
                    circlemarker: false,
                    marker: false,
                    polyline: false
                },
                edit: {
                    featureGroup: drawnItems,
                    remove: true
                }
            });

            map.addControl(drawControl);

            // Handle drawing events
            map.on(L.Draw.Event.CREATED, function (e) {
                const layer = e.layer;
                currentPolygon = layer;
                drawnItems.addLayer(layer);
                updateCoordinatesDisplay(layer);
                document.getElementById('saveBtn').disabled = false;
            });

            map.on(L.Draw.Event.EDITED, function (e) {
                e.layers.eachLayer(function (layer) {
                    updateCoordinatesDisplay(layer);
                });
            });

            map.on(L.Draw.Event.DELETED, function (e) {
                currentPolygon = null;
                document.getElementById('coordinates').textContent = 'Draw a polygon to see coordinates...';
                document.getElementById('saveBtn').disabled = true;
            });
        }

        function startDrawing() {
            // Clear existing drawings
            drawnItems.clearLayers();
            currentPolygon = null;
            document.getElementById('coordinates').textContent = 'Draw a polygon to see coordinates...';
            document.getElementById('saveBtn').disabled = true;
            
            // Start polygon drawing
            new L.Draw.Polygon(map, drawControl.options.draw.polygon).enable();
        }

        function clearDrawing() {
            drawnItems.clearLayers();
            currentPolygon = null;
            document.getElementById('coordinates').textContent = 'Draw a polygon to see coordinates...';
            document.getElementById('saveBtn').disabled = true;
        }

        function updateCoordinatesDisplay(layer) {
            if (layer instanceof L.Polygon) {
                const coords = layer.getLatLngs()[0];
                let coordsText = 'Polygon Coordinates:\n';
                coords.forEach((coord, index) => {
                    coordsText += `${index + 1}: [${coord.lat.toFixed(6)}, ${coord.lng.toFixed(6)}]\n`;
                });
                document.getElementById('coordinates').textContent = coordsText;
            }
        }

        async function testPostcode() {
            const postcode = document.getElementById('test_postcode').value.trim();
            const resultDiv = document.getElementById('test_result');
            
            if (!postcode) {
                resultDiv.innerHTML = '<span style="color: #dc3545;">Please enter a postcode</span>';
                return;
            }

            if (!currentPolygon) {
                resultDiv.innerHTML = '<span style="color: #dc3545;">Please draw an area first</span>';
                return;
            }

            try {
                // Geocode postcode using Nominatim API
                resultDiv.innerHTML = '<span style="color: #6c757d;">Looking up postcode...</span>';
                
                const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&countrycodes=gb&q=${encodeURIComponent(postcode)}`);
                const data = await response.json();
                
                if (data.length === 0) {
                    resultDiv.innerHTML = '<span style="color: #dc3545;">Postcode not found</span>';
                    return;
                }

                const lat = parseFloat(data[0].lat);
                const lng = parseFloat(data[0].lon);
                
                // Check if point is inside polygon
                const point = L.latLng(lat, lng);
                const isInside = currentPolygon.getBounds().contains(point) && 
                                isPointInPolygon(point, currentPolygon.getLatLngs()[0]);
                
                // Add marker to map
                const marker = L.marker([lat, lng]).addTo(map);
                marker.bindPopup(`${postcode}<br/>Inside Area: ${isInside ? 'Yes' : 'No'}`).openPopup();
                
                // Update result
                if (isInside) {
                    resultDiv.innerHTML = `<span style="color: #28a745;">✅ ${postcode} is INSIDE the area</span><br/>
                                          <small>Coordinates: ${lat.toFixed(6)}, ${lng.toFixed(6)}</small>`;
                } else {
                    resultDiv.innerHTML = `<span style="color: #dc3545;">❌ ${postcode} is OUTSIDE the area</span><br/>
                                          <small>Coordinates: ${lat.toFixed(6)}, ${lng.toFixed(6)}</small>`;
                }
                
                // Pan to postcode location
                map.setView([lat, lng], 14);
                
            } catch (error) {
                resultDiv.innerHTML = '<span style="color: #dc3545;">Error looking up postcode</span>';
                console.error('Geocoding error:', error);
            }
        }

        function isPointInPolygon(point, polygon) {
            let inside = false;
            const x = point.lng, y = point.lat;
            
            for (let i = 0, j = polygon.length - 1; i < polygon.length; j = i++) {
                const xi = polygon[i].lng, yi = polygon[i].lat;
                const xj = polygon[j].lng, yj = polygon[j].lat;
                
                if (((yi > y) !== (yj > y)) && (x < (xj - xi) * (y - yi) / (yj - yi) + xi)) {
                    inside = !inside;
                }
            }
            return inside;
        }

        async function saveArea() {
            const name = document.getElementById('area_name').value.trim();
            const description = document.getElementById('description').value.trim();
            const active = document.getElementById('active').value;
            
            if (!name) {
                alert('Please enter an area name');
                return;
            }
            
            if (!currentPolygon) {
                alert('Please draw an area first');
                return;
            }
            
            const coordinates = currentPolygon.getLatLngs()[0].map(coord => [coord.lat, coord.lng]);
            
            const areaData = {
                name: name,
                description: description,
                active: active === '1',
                coordinates: coordinates,
                _token: '{{ csrf_token() }}'
            };
            
            try {
                const response = await fetch('{{ route("areas.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(areaData)
                });
                
                if (response.ok) {
                    alert('Area saved successfully!');
                    window.location.href = '{{ route("areas.index") }}';
                } else {
                    alert('Error saving area');
                }
            } catch (error) {
                alert('Error saving area');
                console.error('Save error:', error);
            }
        }

        // Load existing areas
        async function loadExistingAreas() {
            try {
                const response = await fetch('{{ route("api.areas") }}');
                const areas = await response.json();
                
                areas.forEach(area => {
                    if (area.type === 'map' && area.coordinates && area.coordinates.length > 0) {
                        // Create polygon for map-based areas
                        const polygon = L.polygon(area.coordinates, {
                            color: area.active ? '#28a745' : '#6c757d',
                            fillColor: area.active ? '#28a745' : '#6c757d',
                            fillOpacity: 0.2,
                            weight: 2
                        }).addTo(map);
                        
                        polygon.bindPopup(`
                            <strong>${area.name}</strong><br/>
                            ${area.description || ''}<br/>
                            <small>Status: ${area.active ? 'Active' : 'Inactive'}</small><br/>
                            <small>Type: Map Area</small>
                        `);
                    } else if (area.type === 'postcode' && area.postcodes) {
                        // Create markers for postcode-based areas
                        const marker = L.marker([51.5074, -0.1278], {
                            icon: L.divIcon({
                                className: 'postcode-marker',
                                html: `<div style="background: ${area.active ? '#007bff' : '#6c757d'}; color: white; padding: 5px; border-radius: 3px; font-size: 12px;">${area.name}</div>`,
                                iconSize: [100, 30]
                            })
                        }).addTo(map);
                        
                        marker.bindPopup(`
                            <strong>${area.name}</strong><br/>
                            ${area.description || ''}<br/>
                            <small>Postcodes: ${area.postcodes}</small><br/>
                            <small>Status: ${area.active ? 'Active' : 'Inactive'}</small><br/>
                            <small>Type: Postcode Area</small>
                        `);
                    }
                });
            } catch (error) {
                console.error('Error loading existing areas:', error);
            }
        }

        // Initialize map when page loads
        document.addEventListener('DOMContentLoaded', initMap);
    </script>
</body>
</html>
