<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Area #{{ $area['id'] }} - Bin Collection Admin</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
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
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>✏️ Edit Area #{{ $area['id'] }}</h1>
        <p>Modify the coverage area and postcode settings.</p>
        
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
            <p><strong>Name:</strong> {{ $area['name'] }} | <strong>Status:</strong> {{ $area['active'] ? 'Active' : 'Inactive' }}</p>
            <p><strong>Postcodes:</strong> {{ $area['postcodes'] }}</p>
            <p><strong>Description:</strong> {{ $area['description'] ?: 'No description' }}</p>
        </div>

        <h2>Update Area Information</h2>
        <p>Modify any of the fields below to update this coverage area:</p>

        @if(session('success'))
            <div style="padding: 15px; background-color: #d4edda; color: #155724; border-radius: 5px; margin-bottom: 20px;">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('areas.update', $area['id']) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Area Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" value="{{ $area['name'] }}" required>
                </div>
                <div class="form-group">
                    <label for="active">Status <span class="required">*</span></label>
                    <select id="active" name="active" required>
                        <option value="1" {{ $area['active'] ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ !$area['active'] ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="postcodes">Postcodes (comma-separated) <span class="required">*</span></label>
                <input type="text" id="postcodes" name="postcodes" value="{{ $area['postcodes'] }}" placeholder="e.g., EC1, EC2, EC3, WC1, WC2" required>
                <small style="color: #6c757d;">Enter postcode areas separated by commas (e.g., EC1, EC2, N1, N2)</small>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" placeholder="Brief description of this area coverage">{{ $area['description'] }}</textarea>
            </div>

            <button type="submit" class="submit-btn">💾 Update Area</button>
        </form>

        <div style="margin-top: 30px; padding: 20px; background: #fff3cd; border-radius: 5px;">
            <h3>💡 Area Management Guidelines</h3>
            <ul>
                <li><strong>Postcode Areas:</strong> Use the main postcode area (e.g., EC1, N1, SW1) not full postcodes</li>
                <li><strong>Active Status:</strong> Only active areas will be used for service validation</li>
                <li><strong>Customer Impact:</strong> Changes will affect new bookings immediately</li>
                <li><strong>Testing:</strong> Test postcode validation using the API endpoint after updates</li>
                <li><strong>Coverage Gaps:</strong> Ensure no service gaps when modifying existing areas</li>
            </ul>
        </div>

        <div style="margin-top: 20px; padding: 20px; background: #f8d7da; border-radius: 5px;">
            <h3>⚠️ Important Notes</h3>
            <ul>
                <li><strong>Existing Collections:</strong> Changes won't affect already booked collections</li>
                <li><strong>Customer Communication:</strong> Notify customers of coverage area changes</li>
                <li><strong>Backup:</strong> Consider keeping a record of previous settings before major changes</li>
            </ul>
        </div>
    </div>
</body>
</html>
