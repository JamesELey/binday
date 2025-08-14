<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Collections - Bin Collection Schedules</title>
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
        .collections-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .collections-table th, .collections-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .collections-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        .status-scheduled {
            background-color: #d4edda;
            color: #155724;
        }
        .status-completed {
            background-color: #cce7ff;
            color: #004085;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .bin-type {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 500;
        }
        .bin-residual { background-color: #6c757d; color: white; }
        .bin-recycling { background-color: #28a745; color: white; }
        .bin-garden { background-color: #17a2b8; color: white; }
        .bin-food { background-color: #fd7e14; color: white; }
        @media (max-width: 768px) {
            .collections-table {
                font-size: 12px;
            }
            .collections-table th, .collections-table td {
                padding: 8px 4px;
            }
        }
    </style>
</head>
<body>
    @include('components.auth-nav')
    <div class="header">
        <h1>📋 All Collections</h1>
        <p>View all booked waste collection schedules and their current status.</p>
        
        <div class="nav-links">
            <a href="{{ route('bins.index') }}">🏠 Home</a>
            <a href="{{ route('collections.create') }}" style="background: #28a745;">➕ Book New Collection</a>
            <a href="{{ route('collections.manage') }}" style="background: #ffc107; color: #212529;">✏️ Edit Collections</a>
            <a href="{{ route('bins.map') }}">🗺️ View Map</a>
        </div>
    </div>

    <div class="content">
        <h2>Collection Schedule</h2>
        <p>Here are all the currently booked waste collections:</p>

        @if(session('success'))
            <div style="padding: 15px; background-color: #d4edda; color: #155724; border-radius: 5px; margin-bottom: 20px;">
                {{ session('success') }}
            </div>
        @endif

        <table class="collections-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Address</th>
                    <th>Bin Type</th>
                    <th>Collection Date</th>
                    <th>Time</th>
                    <th>Status</th>
                    <th>Phone</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($collections as $collection)
                <tr>
                    <td>#{{ $collection->id }}</td>
                    <td><strong>{{ $collection->customer_name }}</strong></td>
                    <td>{{ $collection->address }}</td>
                    <td>
                        <span class="bin-type bin-{{ strtolower(str_replace(' ', '', $collection->bin_type)) }}">
                            {{ $collection->bin_type }}
                        </span>
                    </td>
                    <td>{{ $collection->collection_date->format('M j, Y') }}</td>
                    <td>{{ $collection->collection_time }}</td>
                    <td>
                        <span class="status-badge status-{{ strtolower($collection->status) }}">
                            {{ $collection->status }}
                        </span>
                    </td>
                    <td>{{ $collection->phone }}</td>
                    <td>{{ $collection->notes ?: 'No notes' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top: 30px; padding: 20px; background: #e8f5e8; border-radius: 5px;">
            <h3>📊 Collection Summary</h3>
            <ul>
                <li><strong>Total Collections:</strong> {{ $collections->count() }}</li>
                <li><strong>Scheduled:</strong> {{ $collections->where('status', 'scheduled')->count() }}</li>
                <li><strong>Completed:</strong> {{ $collections->where('status', 'completed')->count() }}</li>
                <li><strong>Pending:</strong> {{ $collections->where('status', 'pending')->count() }}</li>
            </ul>
        </div>
    </div>
</body>
</html>
