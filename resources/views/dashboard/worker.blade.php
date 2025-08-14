<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Dashboard - BinDay</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 0;
            background: #f8fafc;
        }
        
        .header {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        
        .user-info {
            margin-top: 5px;
            opacity: 0.9;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        
        .card:hover {
            transform: translateY(-2px);
        }
        
        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .card-icon {
            font-size: 24px;
            margin-right: 12px;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin: 0;
        }
        
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            color: white;
            font-weight: 500;
            text-align: center;
            transition: background-color 0.2s;
            cursor: pointer;
        }
        
        .btn-primary {
            background: #48bb78;
        }
        
        .btn-primary:hover {
            background: #38a169;
        }
        
        .btn-success {
            background: #4299e1;
        }
        
        .btn-success:hover {
            background: #3182ce;
        }
        
        .btn-info {
            background: #ed8936;
        }
        
        .btn-info:hover {
            background: #dd6b20;
        }
        
        .navbar {
            background: white;
            padding: 15px 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .nav-links {
            display: flex;
            gap: 20px;
        }
        
        .nav-links a {
            color: #48bb78;
            text-decoration: none;
            font-weight: 500;
        }
        
        .nav-links a:hover {
            text-decoration: underline;
        }
        
        .logout-form {
            margin: 0;
        }
        
        .btn-logout {
            background: #e53e3e;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
        }
        
        .btn-logout:hover {
            background: #c53030;
        }
        
        .assigned-areas {
            background: #f0fff4;
            border: 1px solid #9ae6b4;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .area-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 10px;
        }
        
        .area-tag {
            background: #48bb78;
            color: white;
            padding: 4px 12px;
            border-radius: 16px;
            font-size: 14px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>👷 Worker Dashboard</h1>
        <div class="user-info">Welcome back, {{ $user->name }}!</div>
    </div>
    
    <nav class="navbar">
        <div class="nav-links">
            <a href="{{ route('bins.index') }}">🏠 Home</a>
            <a href="{{ route('bins.map') }}">🗺️ Map</a>
            <a href="{{ route('collections.index') }}">📅 Collections</a>
        </div>
        
        <form method="POST" action="{{ route('logout') }}" class="logout-form">
            @csrf
            <button type="submit" class="btn-logout">🚪 Logout</button>
        </form>
    </nav>
    
    <div class="container">
        <div class="assigned-areas">
            <h4>📍 Your Assigned Areas</h4>
            @if(!empty($user->assigned_area_ids))
                <div class="area-list">
                    @foreach($user->assigned_area_ids as $areaId)
                        <span class="area-tag">Area #{{ $areaId }}</span>
                    @endforeach
                </div>
                <p>You can create and manage collections for these areas only.</p>
            @else
                <p>⚠️ <strong>No areas assigned yet.</strong> Contact an administrator to assign work areas to your account.</p>
            @endif
        </div>
        
        <div class="dashboard-grid">
            <div class="card">
                <div class="card-header">
                    <span class="card-icon">📅</span>
                    <h3 class="card-title">Collection Management</h3>
                </div>
                <div class="action-buttons">
                    <a href="{{ route('collections.index') }}" class="btn btn-primary">📋 View Collections</a>
                    <a href="{{ route('collections.create') }}" class="btn btn-success">➕ Create Collection</a>
                    <a href="{{ route('collections.manage') }}" class="btn btn-info">⚙️ Manage Collections</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <span class="card-icon">🗺️</span>
                    <h3 class="card-title">Area Mapping</h3>
                </div>
                <div class="action-buttons">
                    <a href="{{ route('bins.map') }}" class="btn btn-primary">🗺️ View Map</a>
                    <a href="{{ route('areas.index') }}" class="btn btn-success">🏘️ View Areas</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <span class="card-icon">📊</span>
                    <h3 class="card-title">My Work</h3>
                </div>
                <div class="action-buttons">
                    <a href="#" class="btn btn-info">📈 My Performance</a>
                    <a href="#" class="btn btn-primary">📋 Today's Schedule</a>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <span class="card-icon">ℹ️</span>
                <h3 class="card-title">Worker Capabilities</h3>
            </div>
            <p><strong>As a Worker, you can:</strong></p>
            <ul>
                <li>✅ <strong>Assigned Areas Only:</strong> Create and edit collections in your assigned areas</li>
                <li>✅ <strong>Collection Management:</strong> Schedule, update, and manage collection requests</li>
                <li>✅ <strong>View Areas:</strong> See all area information and boundaries</li>
                <li>✅ <strong>Performance Tracking:</strong> Monitor your work statistics</li>
                <li>❌ <strong>Cannot:</strong> Create areas, manage other workers, or access admin tools</li>
            </ul>
            
            @if(empty($user->assigned_area_ids))
                <div style="background: #fed7d7; border: 1px solid #fc8181; border-radius: 8px; padding: 15px; margin-top: 15px;">
                    <p><strong>⚠️ Account Setup Required</strong></p>
                    <p>Your worker account needs area assignments. Please contact an administrator to:</p>
                    <ul>
                        <li>Assign specific collection areas to your account</li>
                        <li>Set up your work schedule and permissions</li>
                        <li>Provide any necessary training materials</li>
                    </ul>
                </div>
            @endif
        </div>
    </div>
</body>
</html>
