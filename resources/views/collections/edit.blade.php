<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Collection #{{ $collection->id }} - Bin Collection Schedules</title>
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
        <h1>✏️ Edit Collection #{{ $collection->id }}</h1>
        <p>Modify the details of this waste collection booking.</p>
        
        <div class="nav-links">
            <a href="{{ route('bins.index') }}">🏠 Home</a>
            <a href="{{ route('collections.index') }}">📋 View All Collections</a>
            <a href="{{ route('collections.manage') }}">✏️ Manage Collections</a>
            <a href="{{ route('collections.create') }}" style="background: #28a745;">➕ Book New Collection</a>
        </div>
    </div>

    <div class="content">
        <div class="current-value">
            <h3>📋 Current Collection Details</h3>
            <p><strong>Customer:</strong> {{ $collection->customer_name }} | <strong>Phone:</strong> {{ $collection->phone }}</p>
            <p><strong>Address:</strong> {{ $collection->address }}</p>
            <p><strong>Type:</strong> {{ $collection->bin_type }} | <strong>Date:</strong> {{ $collection->collection_date->format('M j, Y') }} at {{ $collection->collection_time ? $collection->collection_time->format('H:i') : 'Not set' }}</p>
            <p><strong>Status:</strong> {{ $collection->status }} | <strong>Notes:</strong> {{ $collection->notes ?: 'No notes' }}</p>
        </div>

        <h2>Update Collection Information</h2>
        <p>Modify any of the fields below to update this collection:</p>

        @if(session('success'))
            <div style="padding: 15px; background-color: #d4edda; color: #155724; border-radius: 5px; margin-bottom: 20px;">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('collections.update', $collection->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="form-row">
                <div class="form-group">
                    <label for="customer_name">Customer Name <span class="required">*</span></label>
                    <input type="text" id="customer_name" name="customer_name" value="{{ $collection->customer_name }}" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number <span class="required">*</span></label>
                    <input type="tel" id="phone" name="phone" value="{{ $collection->phone }}" required>
                </div>
            </div>

            <div class="form-group">
                <label for="address">Collection Address <span class="required">*</span></label>
                <textarea id="address" name="address" required>{{ $collection->address }}</textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="bin_type">Bin Type <span class="required">*</span></label>
                    <select id="bin_type" name="bin_type" required>
                        <option value="Residual Waste" {{ $collection->bin_type === 'Residual Waste' ? 'selected' : '' }}>🗑️ Residual Waste (General waste)</option>
                        <option value="Recycling" {{ $collection->bin_type === 'Recycling' ? 'selected' : '' }}>♻️ Recycling (Paper, plastic, cans)</option>
                        <option value="Garden Waste" {{ $collection->bin_type === 'Garden Waste' ? 'selected' : '' }}>🌿 Garden Waste (Grass, leaves, branches)</option>
                        <option value="Food Waste" {{ $collection->bin_type === 'Food Waste' ? 'selected' : '' }}>🥬 Food Waste (Kitchen scraps)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="collection_date">Collection Date <span class="required">*</span></label>
                    <input type="date" id="collection_date" name="collection_date" value="{{ $collection->collection_date->format('Y-m-d') }}" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="collection_time">Collection Time</label>
                    <select id="collection_time" name="collection_time">
                        <option value="08:00" {{ ($collection->collection_time ? $collection->collection_time->format('H:i') : '') === '08:00' ? 'selected' : '' }}>08:00 - Early Morning</option>
                        <option value="09:00" {{ ($collection->collection_time ? $collection->collection_time->format('H:i') : '') === '09:00' ? 'selected' : '' }}>09:00 - Morning</option>
                        <option value="10:00" {{ ($collection->collection_time ? $collection->collection_time->format('H:i') : '') === '10:00' ? 'selected' : '' }}>10:00 - Mid Morning</option>
                        <option value="11:00" {{ ($collection->collection_time ? $collection->collection_time->format('H:i') : '') === '11:00' ? 'selected' : '' }}>11:00 - Late Morning</option>
                        <option value="12:00" {{ ($collection->collection_time ? $collection->collection_time->format('H:i') : '') === '12:00' ? 'selected' : '' }}>12:00 - Noon</option>
                        <option value="13:00" {{ ($collection->collection_time ? $collection->collection_time->format('H:i') : '') === '13:00' ? 'selected' : '' }}>13:00 - Early Afternoon</option>
                        <option value="14:00" {{ ($collection->collection_time ? $collection->collection_time->format('H:i') : '') === '14:00' ? 'selected' : '' }}>14:00 - Afternoon</option>
                        <option value="15:00" {{ ($collection->collection_time ? $collection->collection_time->format('H:i') : '') === '15:00' ? 'selected' : '' }}>15:00 - Mid Afternoon</option>
                        <option value="16:00" {{ ($collection->collection_time ? $collection->collection_time->format('H:i') : '') === '16:00' ? 'selected' : '' }}>16:00 - Late Afternoon</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="Pending" {{ $collection->status === 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Scheduled" {{ $collection->status === 'Scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="Completed" {{ $collection->status === 'Completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="notes">Special Instructions</label>
                <textarea id="notes" name="notes" placeholder="Any special instructions for the collection team">{{ $collection->notes }}</textarea>
            </div>

            <button type="submit" class="submit-btn">💾 Update Collection</button>
        </form>

        <div style="margin-top: 30px; padding: 20px; background: #fff3cd; border-radius: 5px;">
            <h3>💡 Update Tips</h3>
            <ul>
                <li><strong>Date Changes:</strong> Make sure to notify the customer of any date changes</li>
                <li><strong>Status Updates:</strong> Update status to reflect actual collection progress</li>
                <li><strong>Contact Info:</strong> Double-check phone numbers if they have been updated</li>
                <li><strong>Special Instructions:</strong> Add any new access requirements or location details</li>
            </ul>
        </div>
    </div>

    <script>
        // Set minimum date to today
        document.getElementById('collection_date').min = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>
