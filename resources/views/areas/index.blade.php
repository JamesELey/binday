<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Allowed Areas - Bin Collection Admin</title>
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
        .areas-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .areas-table th, .areas-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .areas-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
        }
        .btn-edit {
            background: #007bff;
            color: white;
        }
        .btn-edit:hover {
            background: #0056b3;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        .btn-delete:hover {
            background: #c82333;
        }
        .add-area-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
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
        .form-row {
            display: flex;
            gap: 15px;
        }
        .form-row .form-group {
            flex: 1;
        }
        .submit-btn {
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }
        .submit-btn:hover {
            background: #218838;
        }
        @media (max-width: 768px) {
            .areas-table {
                font-size: 12px;
            }
            .areas-table th, .areas-table td {
                padding: 8px 4px;
            }
            .form-row {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🗺️ Manage Allowed Areas</h1>
        <p>Configure which postcodes and areas are eligible for bin collection services.</p>
        
        <div class="nav-links">
            <a href="{{ route('bins.index') }}">🏠 Home</a>
            <a href="{{ route('collections.index') }}">📋 Collections</a>
            <a href="{{ route('api.areas') }}" target="_blank">📊 API Data</a>
            <a href="{{ route('admin.settings') }}" style="background: #6c757d;">⚙️ Admin Settings</a>
        </div>
    </div>

    <div class="content">
        <div class="add-area-form">
            <h3>➕ Add New Allowed Area</h3>
            <div style="margin-bottom: 20px;">
                <a href="{{ route('areas.createMap') }}" class="btn btn-primary" style="background: #17a2b8; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; font-weight: 600;">
                    🗺️ Draw Area on Map
                </a>
                <span style="margin: 0 10px; color: #6c757d;">or</span>
                <span style="color: #6c757d;">Use the form below for postcode-based areas:</span>
            </div>
            <form action="{{ route('areas.store') }}" method="POST">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Area Name</label>
                        <input type="text" id="name" name="name" placeholder="e.g., Central London" required>
                    </div>
                    <div class="form-group">
                        <label for="active">Status</label>
                        <select id="active" name="active">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="postcodes">Postcodes (comma-separated)</label>
                    <input type="text" id="postcodes" name="postcodes" placeholder="e.g., EC1, EC2, EC3, WC1, WC2" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Brief description of this area coverage"></textarea>
                </div>
                
                <button type="submit" class="submit-btn">Add Area</button>
            </form>
        </div>

        <h2>Current Allowed Areas</h2>
        <p>Manage the areas where bin collection services are available:</p>

        @if(session('success'))
            <div style="padding: 15px; background-color: #d4edda; color: #155724; border-radius: 5px; margin-bottom: 20px;">
                {{ session('success') }}
            </div>
        @endif

        <table class="areas-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Area Name</th>
                    <th>Type</th>
                    <th>Coverage</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($areas as $area)
                <tr>
                    <td>#{{ $area['id'] }}</td>
                    <td><strong>{{ $area['name'] }}</strong></td>
                    <td>
                        @if($area['type'] === 'map')
                            <span style="background: #17a2b8; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px;">🗺️ Map</span>
                        @else
                            <span style="background: #6c757d; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px;">📮 Postcode</span>
                        @endif
                    </td>
                    <td>
                        @if($area['type'] === 'map')
                            <small style="color: #6c757d;">{{ count($area['coordinates'] ?? []) }} coordinate points</small>
                        @else
                            <code style="font-size: 11px;">{{ $area['postcodes'] ?? 'No postcodes' }}</code>
                        @endif
                    </td>
                    <td>{{ $area['description'] ?: 'No description' }}</td>
                    <td>
                        <span class="status-badge status-{{ $area['active'] ? 'active' : 'inactive' }}">
                            {{ $area['active'] ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td>{{ date('M j, Y', strtotime($area['created_at'])) }}</td>
                    <td>
                        <div class="action-buttons">
                            <a href="{{ route('areas.edit', $area['id']) }}" class="btn btn-edit">✏️ Edit</a>
                            <form action="{{ route('areas.destroy', $area['id']) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this area?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-delete">🗑️ Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top: 30px; padding: 20px; background: #e8f5e8; border-radius: 5px;">
            <h3>📊 Area Coverage Summary</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div>
                    <strong>Total Areas:</strong> {{ count($areas) }}
                </div>
                <div>
                    <strong>Active Areas:</strong> {{ count(array_filter($areas, fn($a) => $a['active'])) }}
                </div>
                <div>
                    <strong>Inactive Areas:</strong> {{ count(array_filter($areas, fn($a) => !$a['active'])) }}
                </div>
                <div>
                    <strong>Map-Based Areas:</strong> {{ count(array_filter($areas, fn($a) => $a['type'] === 'map')) }}
                </div>
                <div>
                    <strong>Postcode Areas:</strong> {{ count(array_filter($areas, fn($a) => $a['type'] === 'postcode')) }}
                </div>
                <div>
                    <strong>Total Postcodes:</strong> {{ collect($areas)->filter(fn($a) => $a['type'] === 'postcode')->sum(fn($a) => count(explode(',', $a['postcodes'] ?? ''))) }}
                </div>
            </div>
        </div>

        <div style="margin-top: 20px; padding: 20px; background: #fff3cd; border-radius: 5px;">
            <h3>💡 Area Management Tips</h3>
            <ul>
                <li><strong>Postcode Format:</strong> Use comma-separated postcode areas (e.g., EC1, EC2, N1, N2)</li>
                <li><strong>Active/Inactive:</strong> Only active areas will be used for service validation</li>
                <li><strong>Coverage Testing:</strong> Use the API endpoint to test postcode validation</li>
                <li><strong>Customer Impact:</strong> Customers in non-covered areas will see contact information</li>
            </ul>
        </div>
    </div>
</body>
</html>
