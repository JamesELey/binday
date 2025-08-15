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
        @if(auth()->user()->isCustomer())
            <h1>🗑️ Welcome to BinDay</h1>
            <p>Book and manage your waste collection services with ease.</p>
            
            <div class="nav-links">
                <a href="{{ route('collections.create') }}" style="background: #28a745;">➕ Book New Collection</a>
                <a href="{{ route('collections.manage') }}" style="background: #17a2b8;">📅 My Collections</a>
                <a href="{{ route('enquiry.create') }}" style="background: #ffc107; color: #212529;">💬 Contact Support</a>
            </div>
        @elseif(auth()->user()->isWorker())
            <h1>🗑️ Worker Dashboard</h1>
            <p>Manage collections and routes for your assigned areas.</p>
            
            <div class="nav-links">
                <a href="{{ route('bins.map') }}">🗺️ View Map</a>
                <a href="{{ route('collections.index') }}" style="background: #28a745;">📋 View All Collections</a>
                <a href="{{ route('routes.index') }}" style="background: #dc3545;">🚛 Route Planner</a>
            </div>
            
            <div class="nav-links" style="margin-top: 15px;">
                <a href="{{ route('collections.create') }}" style="background: #17a2b8;">➕ Book New Collection</a>
                <a href="{{ route('collections.manage') }}" style="background: #ffc107; color: #212529;">✏️ Manage Collections</a>
                <a href="{{ route('areas.index') }}" style="background: #6c757d;">🗺️ Service Areas</a>
            </div>
        @else
            <h1>🗑️ Admin Dashboard</h1>
            <p>Full system administration and management capabilities.</p>
            
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
        @endif
    </div>

    <div class="content">
        @if(auth()->user()->isCustomer())
            <h2>Welcome to BinDay!</h2>
            <p>Your simple and convenient waste collection service. Here's how to get started:</p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;">
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #28a745;">
                    <h3>📅 Book Collections</h3>
                    <ul>
                        <li><strong>Easy Booking:</strong> Schedule waste pickup appointments in just a few clicks</li>
                        <li><strong>Choose Your Date:</strong> Pick a convenient collection date and time</li>
                        <li><strong>Multiple Bin Types:</strong> Food waste, recycling, and garden waste collections</li>
                    </ul>
                </div>
                
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #17a2b8;">
                    <h3>📋 Manage Bookings</h3>
                    <ul>
                        <li><strong>View Your Collections:</strong> See all your scheduled pickups</li>
                        <li><strong>Edit Details:</strong> Change addresses, dates, or bin types</li>
                        <li><strong>Cancel Bookings:</strong> Remove collections you no longer need</li>
                    </ul>
                </div>
                
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #ffc107;">
                    <h3>💬 Get Support</h3>
                    <ul>
                        <li><strong>Contact Us:</strong> Get help with bookings or service questions</li>
                        <li><strong>Quick Response:</strong> We typically respond within 24 hours</li>
                        <li><strong>Email Support:</strong> enquiries@thebinday.co.uk</li>
                    </ul>
                </div>
            </div>
        @elseif(auth()->user()->isWorker())
            <h2>Worker Dashboard</h2>
            <p>Manage collections and routes for your assigned service areas:</p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;">
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #007cba;">
                    <h3>🗺️ Map & Routes</h3>
                    <ul>
                        <li><strong>Interactive Map:</strong> See all collection points with house numbers</li>
                        <li><strong>Route Planning:</strong> Optimize collection routes for efficiency</li>
                        <li><strong>Real-time Updates:</strong> Update collection status during routes</li>
                    </ul>
                </div>
                
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #28a745;">
                    <h3>📋 Collection Management</h3>
                    <ul>
                        <li><strong>View Collections:</strong> See all collections in your assigned areas</li>
                        <li><strong>Update Status:</strong> Mark collections as completed or pending</li>
                        <li><strong>Manage Bookings:</strong> Edit collection details as needed</li>
                    </ul>
                </div>
                
                <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; border-left: 4px solid #6c757d;">
                    <h3>🏢 Your Areas</h3>
                    <ul>
                        <li><strong>Assigned Areas:</strong> View your designated service areas</li>
                        <li><strong>Area Coverage:</strong> See which postcodes you service</li>
                        <li><strong>Customer Support:</strong> Help customers with their bookings</li>
                    </ul>
                </div>
            </div>
        @else
            <h2>Admin Dashboard</h2>
            <p>Full system administration and management capabilities:</p>
            
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
        @endif

        @if(auth()->user()->isAdmin())
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
        @elseif(auth()->user()->isCustomer())
        <div style="margin-top: 30px; padding: 20px; background: #e8f5e8; border-radius: 5px;">
            <h3>🚀 Ready to Get Started?</h3>
            <p>Follow these simple steps to book your first collection:</p>
            <ol>
                <li><strong>Click "Book New Collection"</strong> above</li>
                <li><strong>Enter your address</strong> and preferred collection date</li>
                <li><strong>Choose your bin type</strong> (food waste, recycling, or garden waste)</li>
                <li><strong>Submit your booking</strong> and receive confirmation</li>
            </ol>
            <p><strong>Questions?</strong> Contact us at <a href="mailto:enquiries@thebinday.co.uk">enquiries@thebinday.co.uk</a></p>
        </div>
        @endif
    </div>
</body>
</html>