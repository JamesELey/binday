<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seed Data Management - Bin Collection Admin</title>
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
            margin-bottom: 20px;
        }
        .data-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .data-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #007cba;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            margin-right: 10px;
            margin-bottom: 10px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-success:hover {
            background: #218838;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .btn-info {
            background: #17a2b8;
            color: white;
        }
        .btn-info:hover {
            background: #138496;
        }
        .status-card {
            background: #e8f5e8;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .data-preview {
            background: #f1f3f4;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
            font-family: monospace;
            font-size: 12px;
            max-height: 200px;
            overflow-y: auto;
        }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        @media (max-width: 768px) {
            .data-section {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🌱 Seed Data Management</h1>
        <p>Manage demo data for testing and development. Generate Eccleshall area with sample collections or clear all data.</p>
        
        <div class="nav-links">
            <a href="{{ route('bins.index') }}">🏠 Home</a>
            <a href="{{ route('areas.index') }}">🗺️ Manage Areas</a>
            <a href="{{ route('collections.index') }}">📋 Collections</a>
            <a href="{{ route('areas.createMap') }}">🖊️ Draw Area</a>
        </div>
    </div>

    <div class="content">
        <div class="status-card">
            <h3>📊 Current Data Status</h3>
            <div id="dataStatus">
                <p>Loading current data status...</p>
            </div>
        </div>

        <div class="data-section">
            <div class="data-card">
                <h3>🌱 Seed Demo Data</h3>
                <p>Generate comprehensive demo data including:</p>
                <ul>
                    <li><strong>Eccleshall Area:</strong> Map-based polygon coverage</li>
                    <li><strong>20 Collections:</strong> Random addresses over 2 weeks</li>
                    <li><strong>Varied Bin Types:</strong> All waste categories</li>
                    <li><strong>Realistic Data:</strong> Real street names and times</li>
                </ul>
                
                <button onclick="seedAllData()" class="btn btn-success">🌱 Seed All Demo Data</button>
                
                <div class="data-preview">
                    <strong>Eccleshall Area Preview:</strong><br/>
                    • Historic market town in Staffordshire<br/>
                    • Accurate polygon boundary coordinates<br/>
                    • 9-point polygon covering town center<br/>
                    <br/>
                    <strong>Collections Preview:</strong><br/>
                    • 20 random collections<br/>
                    • Spread over 14 days<br/>
                    • Real Eccleshall street addresses<br/>
                    • Mixed bin types and times<br/>
                    • Random customer names and notes
                </div>
            </div>

            <div class="data-card">
                <h3>🗑️ Clear All Data</h3>
                <p>Remove all data from the system:</p>
                <ul>
                    <li><strong>All Areas:</strong> Including drawn polygons</li>
                    <li><strong>All Collections:</strong> Customer bookings</li>
                    <li><strong>Storage Files:</strong> Complete data reset</li>
                    <li><strong>Fresh Start:</strong> Clean development state</li>
                </ul>
                
                <div class="warning-box">
                    <strong>⚠️ Warning:</strong> This action cannot be undone! All areas and collections will be permanently deleted.
                </div>
                
                <button onclick="deleteAllData()" class="btn btn-danger">🗑️ Delete All Data</button>
            </div>

            <div class="data-card">
                <h3>🔄 Quick Actions</h3>
                <p>Individual data management options:</p>
                
                <button onclick="seedEccleshallOnly()" class="btn btn-info">🗺️ Seed Eccleshall Area Only</button>
                <button onclick="seedCollectionsOnly()" class="btn btn-info">📋 Seed Collections Only</button>
                <button onclick="refreshStatus()" class="btn btn-info">🔄 Refresh Status</button>
                
                <div style="margin-top: 15px;">
                    <a href="{{ route('areas.createMap') }}" class="btn btn-info">🖊️ Draw Custom Area</a>
                    <a href="{{ route('collections.create') }}" class="btn btn-info">➕ Add Collection</a>
                </div>
            </div>
        </div>

        <div id="alertContainer"></div>

        <div style="margin-top: 30px; padding: 20px; background: #d1ecf1; border-radius: 5px;">
            <h3>📚 Seeding Information</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                <div>
                    <strong>Eccleshall Location:</strong><br/>
                    Staffordshire, England<br/>
                    Market town near Stafford<br/>
                    Coordinates: 52.85°N, 2.25°W
                </div>
                <div>
                    <strong>Demo Collections:</strong><br/>
                    20 randomly generated bookings<br/>
                    Spread over 2-week period<br/>
                    Real street addresses
                </div>
                <div>
                    <strong>Bin Types Included:</strong><br/>
                    • Residual Waste<br/>
                    • Recycling<br/>
                    • Garden Waste<br/>
                    • Food Waste
                </div>
                <div>
                    <strong>Collection Statuses:</strong><br/>
                    • Scheduled<br/>
                    • Pending<br/>
                    • Completed<br/>
                    Random distribution
                </div>
            </div>
        </div>
    </div>

    <script>
        // Load data status on page load
        document.addEventListener('DOMContentLoaded', function() {
            refreshStatus();
        });

        async function refreshStatus() {
            try {
                const response = await fetch('{{ route("seed.status") }}');
                const data = await response.json();
                
                document.getElementById('dataStatus').innerHTML = `
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                        <div>
                            <strong>Areas:</strong> ${data.areas}<br/>
                            <small>Active: ${data.active_areas}</small>
                        </div>
                        <div>
                            <strong>Collections:</strong> ${data.collections}<br/>
                            <small>Recent: ${data.recent_collections}</small>
                        </div>
                    </div>
                `;
            } catch (error) {
                document.getElementById('dataStatus').innerHTML = '<p style="color: #dc3545;">Error loading status</p>';
            }
        }

        async function seedAllData() {
            if (!confirm('This will create demo data including Eccleshall area and 20 collections. Continue?')) {
                return;
            }

            showAlert('Seeding data...', 'info');
            
            try {
                const response = await fetch('{{ route("seed.all") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert(result.message, 'success');
                    refreshStatus();
                } else {
                    showAlert(result.error, 'danger');
                }
            } catch (error) {
                showAlert('Error seeding data: ' + error.message, 'danger');
            }
        }

        async function deleteAllData() {
            if (!confirm('⚠️ WARNING: This will permanently delete ALL areas and collections. This cannot be undone. Are you sure?')) {
                return;
            }

            if (!confirm('Last chance! This will delete everything. Proceed?')) {
                return;
            }

            showAlert('Deleting all data...', 'info');
            
            try {
                const response = await fetch('{{ route("seed.delete") }}', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert(result.message, 'success');
                    refreshStatus();
                } else {
                    showAlert(result.error, 'danger');
                }
            } catch (error) {
                showAlert('Error deleting data: ' + error.message, 'danger');
            }
        }

        async function seedEccleshallOnly() {
            if (!confirm('Seed only the Eccleshall area polygon?')) {
                return;
            }

            showAlert('Seeding Eccleshall area...', 'info');
            
            try {
                const response = await fetch('{{ route("seed.eccleshall") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert('Eccleshall area seeded successfully!', 'success');
                    refreshStatus();
                } else {
                    showAlert(result.error, 'danger');
                }
            } catch (error) {
                showAlert('Error seeding Eccleshall area: ' + error.message, 'danger');
            }
        }

        async function seedCollectionsOnly() {
            if (!confirm('Seed 20 random collections in existing areas?')) {
                return;
            }

            showAlert('Seeding collections...', 'info');
            
            try {
                const response = await fetch('{{ route("seed.collections") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAlert('Collections seeded successfully!', 'success');
                    refreshStatus();
                } else {
                    showAlert(result.error, 'danger');
                }
            } catch (error) {
                showAlert('Error seeding collections: ' + error.message, 'danger');
            }
        }

        function showAlert(message, type) {
            const alertContainer = document.getElementById('alertContainer');
            const alertClass = type === 'success' ? 'alert-success' : 
                             type === 'danger' ? 'alert-danger' : 'alert-info';
            
            alertContainer.innerHTML = `
                <div class="alert ${alertClass}">
                    ${message}
                </div>
            `;
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                alertContainer.innerHTML = '';
            }, 5000);
        }
    </script>
</body>
</html>
