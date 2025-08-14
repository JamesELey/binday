<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - BinDay</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 0;
            background: #f8fafc;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            background: #667eea;
        }
        
        .btn-primary:hover {
            background: #5a6fd8;
        }
        
        .btn-success {
            background: #48bb78;
        }
        
        .btn-success:hover {
            background: #38a169;
        }
        
        .btn-info {
            background: #4299e1;
        }
        
        .btn-info:hover {
            background: #3182ce;
        }
        
        .btn-warning {
            background: #ed8936;
        }
        
        .btn-warning:hover {
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
            color: #667eea;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>👑 Admin Dashboard</h1>
        <div class="user-info">Welcome back, {{ $user->name }}!</div>
    </div>
    
    <nav class="navbar">
        <div class="nav-links">
            <a href="{{ route('bins.index') }}">🏠 Home</a>
            <a href="{{ route('bins.map') }}">🗺️ Map</a>
            <a href="{{ route('collections.index') }}">📅 Collections</a>
            <a href="{{ route('areas.index') }}">🏘️ Areas</a>
        </div>
        
        <form method="POST" action="{{ route('logout') }}" class="logout-form">
            @csrf
            <button type="submit" class="btn-logout">🚪 Logout</button>
        </form>
    </nav>
    
    <div class="container">
        <div class="dashboard-grid">
            <div class="card">
                <div class="card-header">
                    <span class="card-icon">📊</span>
                    <h3 class="card-title">System Management</h3>
                </div>
                <div class="action-buttons">
                    <a href="{{ route('seed.index') }}" class="btn btn-primary">🗂️ Data Seeding</a>
                    <a href="{{ route('areas.index') }}" class="btn btn-success">🏘️ Manage Areas</a>
                    <a href="#" class="btn btn-info">👥 Manage Users</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <span class="card-icon">📅</span>
                    <h3 class="card-title">Collection Management</h3>
                </div>
                <div class="action-buttons">
                    <a href="{{ route('collections.index') }}" class="btn btn-primary">📋 View All Collections</a>
                    <a href="{{ route('collections.create') }}" class="btn btn-success">➕ Create Collection</a>
                    <a href="{{ route('collections.manage') }}" class="btn btn-info">⚙️ Manage Collections</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <span class="card-icon">🗺️</span>
                    <h3 class="card-title">Mapping & Areas</h3>
                </div>
                <div class="action-buttons">
                    <a href="{{ route('bins.map') }}" class="btn btn-primary">🗺️ Interactive Map</a>
                    <a href="{{ route('areas.createMap') }}" class="btn btn-success">➕ Create Area</a>
                    <a href="#" class="btn btn-warning">👷 Assign Workers</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <span class="card-icon">⚙️</span>
                    <h3 class="card-title">System Tools</h3>
                </div>
                <div class="action-buttons">
                    <a href="#" class="btn btn-info">📊 Analytics</a>
                    <a href="#" class="btn btn-warning">🔧 Settings</a>
                    <a href="#" class="btn btn-primary">📋 Reports</a>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <span class="card-icon">ℹ️</span>
                <h3 class="card-title">Admin Capabilities</h3>
            </div>
            <p><strong>As an Administrator, you have full access to:</strong></p>
            <ul>
                <li>✅ <strong>All Areas:</strong> Create, edit, and delete collection areas</li>
                <li>✅ <strong>All Collections:</strong> View and manage all customer collections</li>
                <li>✅ <strong>User Management:</strong> Create workers, assign areas, manage accounts</li>
                <li>✅ <strong>System Data:</strong> Seed demo data, manage system settings</li>
                <li>✅ <strong>Reports:</strong> Generate analytics and performance reports</li>
            </ul>
        </div>
    </div>
</body>
</html>
