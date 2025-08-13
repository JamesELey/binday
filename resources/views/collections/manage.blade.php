<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Collections - Bin Collection Schedules</title>
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
        @media (max-width: 768px) {
            .collections-table {
                font-size: 12px;
            }
            .collections-table th, .collections-table td {
                padding: 8px 4px;
            }
            .action-buttons {
                flex-direction: column;
            }
            .btn {
                font-size: 11px;
                padding: 4px 8px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>✏️ Manage Collections</h1>
        <p>Edit, update, or delete existing waste collection bookings.</p>
        
        <div class="nav-links">
            <a href="{{ route('bins.index') }}">🏠 Home</a>
            <a href="{{ route('collections.index') }}">📋 View All Collections</a>
            <a href="{{ route('collections.create') }}" style="background: #28a745;">➕ Book New Collection</a>
            <a href="{{ route('bins.map') }}">🗺️ View Map</a>
        </div>
    </div>

    <div class="content">
        <h2>Collection Management</h2>
        <p>Click the edit button to modify a collection or the delete button to cancel it:</p>

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
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($collections as $collection)
                <tr>
                    <td>#{{ $collection['id'] }}</td>
                    <td><strong>{{ $collection['customer_name'] }}</strong></td>
                    <td>{{ Str::limit($collection['address'], 40) }}</td>
                    <td>
                        <span class="bin-type bin-{{ strtolower(str_replace(' ', '', $collection['bin_type'])) }}">
                            {{ $collection['bin_type'] }}
                        </span>
                    </td>
                    <td>{{ date('M j, Y', strtotime($collection['collection_date'])) }}</td>
                    <td>{{ $collection['collection_time'] }}</td>
                    <td>
                        <span class="status-badge status-{{ strtolower($collection['status']) }}">
                            {{ $collection['status'] }}
                        </span>
                    </td>
                    <td>{{ $collection['phone'] }}</td>
                    <td>
                        <div class="action-buttons">
                            <a href="{{ route('collections.edit', $collection['id']) }}" class="btn btn-edit">✏️ Edit</a>
                            <form action="{{ route('collections.destroy', $collection['id']) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this collection?');">
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

        <div style="margin-top: 30px; padding: 20px; background: #fff3cd; border-radius: 5px;">
            <h3>⚠️ Management Guidelines</h3>
            <ul>
                <li><strong>Edit Collections:</strong> Use the edit button to modify collection details, times, or addresses</li>
                <li><strong>Delete Collections:</strong> Permanently removes the collection booking - this cannot be undone</li>
                <li><strong>Status Updates:</strong> Change status from Pending → Scheduled → Completed as appropriate</li>
                <li><strong>Customer Contact:</strong> Always notify customers of any changes to their bookings</li>
                <li><strong>Rescheduling:</strong> For date changes, consider creating a new booking and deleting the old one</li>
            </ul>
        </div>

        <div style="margin-top: 20px; padding: 20px; background: #e8f5e8; border-radius: 5px;">
            <h3>📊 Quick Stats</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div>
                    <strong>Total Collections:</strong> {{ count($collections) }}
                </div>
                <div>
                    <strong>Scheduled:</strong> {{ count(array_filter($collections, fn($c) => $c['status'] === 'Scheduled')) }}
                </div>
                <div>
                    <strong>Completed:</strong> {{ count(array_filter($collections, fn($c) => $c['status'] === 'Completed')) }}
                </div>
                <div>
                    <strong>Pending:</strong> {{ count(array_filter($collections, fn($c) => $c['status'] === 'Pending')) }}
                </div>
            </div>
        </div>
    </div>
</body>
</html>
