<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bin Collection Schedules</title>
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
            gap: 20px;
            margin-top: 20px;
        }
        .nav-links a {
            background: #007cba;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
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
    </style>
</head>
<body>
    @include('components.auth-nav')
    <div class="header">
        <h1>🗑️ Bin Collection Schedules</h1>
        <p>Manage your waste collection schedules and view the interactive map.</p>
        
        <div class="nav-links">
            <a href="{{ route('bins.map') }}">🗺️ View Map</a>
            <a href="{{ route('bins.mapByDate') }}">📅 Map by Date</a>
            <a href="{{ route('api.bins') }}">📊 API Data</a>
        </div>
        
        <div class="nav-links" style="margin-top: 15px;">
            <a href="{{ route('collections.index') }}" style="background: #28a745;">📋 View All Collections</a>
            <a href="{{ route('collections.create') }}" style="background: #17a2b8;">➕ Book New Collection</a>
            <a href="{{ route('collections.manage') }}" style="background: #ffc107; color: #212529;">✏️ Edit Collections</a>
            <a href="{{ route('routes.index') }}" style="background: #dc3545;">🚛 Route Planner</a>
        </div>
        
        <div class="nav-links" style="margin-top: 15px;">
            <a href="{{ route('areas.index') }}" style="background: #6c757d;">🗺️ Manage Allowed Areas</a>
            <a href="{{ route('areas.createMap') }}" style="background: #17a2b8;">🖊️ Draw Area on Map</a>
            <a href="{{ route('api.areas') }}" style="background: #17a2b8;" target="_blank">📊 Areas API</a>
        </div>
        
        <div class="nav-links" style="margin-top: 15px;">
            <a href="{{ route('seed.index') }}" style="background: #28a745;">🌱 Seed Demo Data</a>
        </div>
    </div>

    <div class="content">
        <h2>Welcome to the Bin Collection System</h2>
        <p>This Laravel application helps you manage waste collection schedules. Use the navigation above to:</p>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;">
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #007cba;">
                <h3>🗺️ Map Features</h3>
                <ul>
                    <li><strong>View Map:</strong> See all bin collection points on an interactive map</li>
                    <li><strong>Map by Date:</strong> Filter collections by specific dates</li>
                    <li><strong>API Data:</strong> Access the raw JSON data for developers</li>
                </ul>
            </div>
            
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #28a745;">
                <h3>📋 Collection Management</h3>
                <ul>
                    <li><strong>View All Collections:</strong> See complete list of all bookings</li>
                    <li><strong>Book New Collection:</strong> Schedule waste pickup appointments</li>
                    <li><strong>Edit Collections:</strong> Modify existing bookings and update status</li>
                </ul>
            </div>
            
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #dc3545;">
                <h3>🚛 Route Planning</h3>
                <ul>
                    <li><strong>Optimize Routes:</strong> Plan efficient collection routes using AI algorithms</li>
                    <li><strong>Worker Dashboard:</strong> View daily collections for assigned areas</li>
                    <li><strong>Real-time Updates:</strong> Update collection status during route execution</li>
                    <li><strong>Distance Calculation:</strong> Minimize travel time with smart routing</li>
                    <li><strong>Interactive Map:</strong> Visual route planning with drag-and-drop optimization</li>
                </ul>
            </div>
            
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #6c757d;">
                <h3>🗺️ Area Management & Geofencing</h3>
                <ul>
                    <li><strong>Map-Based Areas:</strong> Draw precise service boundaries on interactive maps</li>
                    <li><strong>Postcode Geocoding:</strong> Automatic coordinate lookup for address validation</li>
                    <li><strong>Geofencing:</strong> Point-in-polygon validation for accurate coverage checking</li>
                    <li><strong>Fallback System:</strong> Traditional postcode area validation when geocoding fails</li>
                    <li><strong>Contact Redirection:</strong> Guide customers outside areas to enquiries@thebinday.co.uk</li>
                </ul>
            </div>
        </div>

        <div style="margin-top: 30px; padding: 20px; background: #e8f5e8; border-radius: 5px;">
            <h3>✅ System Status: Online</h3>
            <p>The Laravel application is running successfully with all core components functioning:</p>
            <ul>
                <li>✅ Routes configured</li>
                <li>✅ Controllers loaded</li>
                <li>✅ Views rendering</li>
                <li>✅ API endpoints active</li>
                <li>✅ Map functionality ready</li>
            </ul>
        </div>
    </div>
</body>
</html>