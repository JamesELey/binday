<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard - BinDay</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 0;
            background: #f8fafc;
        }
        
        .header {
            background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
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
            background: #4299e1;
        }
        
        .btn-primary:hover {
            background: #3182ce;
        }
        
        .btn-success {
            background: #48bb78;
        }
        
        .btn-success:hover {
            background: #38a169;
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
            color: #4299e1;
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
        
        .quick-start {
            background: #ebf8ff;
            border: 1px solid #90cdf4;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .quick-start h3 {
            color: #2b6cb0;
            margin-top: 0;
        }
    </style>
</head>
<body>
    @include('components.auth-nav')
    <div class="header">
        <h1>👤 Customer Dashboard</h1>
        <div class="user-info">Welcome, {{ $user->name }}!</div>
    </div>
    
    <nav class="navbar">
        <div class="nav-links">
            <a href="{{ route('bins.index') }}">🏠 Home</a>
            <a href="{{ route('collections.create') }}">➕ Book Collection</a>
            <a href="{{ route('collections.manage') }}">📅 My Collections</a>
        </div>
        
        <form method="POST" action="{{ route('logout') }}" class="logout-form">
            @csrf
            <button type="submit" class="btn-logout">🚪 Logout</button>
        </form>
    </nav>
    
    <div class="container">
        <div class="quick-start">
            <h3>🚀 Quick Start Guide</h3>
            <p><strong>Ready to book your first collection?</strong></p>
            <ol>
                <li>📍 <strong>Check Coverage:</strong> Use our interactive map to see if we service your area</li>
                <li>📅 <strong>Book Collection:</strong> Schedule a bin collection for your address</li>
                <li>📋 <strong>Track Status:</strong> Monitor your collection requests and schedules</li>
            </ol>
        </div>
        
        <div class="dashboard-grid">
            <div class="card">
                <div class="card-header">
                    <span class="card-icon">📅</span>
                    <h3 class="card-title">My Collections</h3>
                </div>
                <div class="action-buttons">
                    <a href="{{ route('collections.create') }}" class="btn btn-success">➕ Book New Collection</a>
                    <a href="{{ route('collections.manage') }}" class="btn btn-primary">📋 My Collections</a>
                    <a href="{{ route('enquiry.create') }}" class="btn btn-info">💬 Contact Support</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <span class="card-icon">❓</span>
                    <h3 class="card-title">Help & Support</h3>
                </div>
                <div class="action-buttons">
                    <a href="{{ route('enquiry.create') }}" class="btn btn-primary">❓ Ask a Question</a>
                    <a href="mailto:enquiries@thebinday.co.uk" class="btn btn-info">📧 Email Support</a>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <span class="card-icon">ℹ️</span>
                    <h3 class="card-title">Account & Support</h3>
                </div>
                <div class="action-buttons">
                    <a href="#" class="btn btn-info">👤 My Profile</a>
                    <a href="#" class="btn btn-primary">📞 Contact Support</a>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <span class="card-icon">💡</span>
                <h3 class="card-title">Customer Information</h3>
            </div>
            <p><strong>As a Customer, you can:</strong></p>
            <ul>
                <li>✅ <strong>Book Collections:</strong> Schedule bin collections for your address</li>
                <li>✅ <strong>Manage Bookings:</strong> Edit, cancel, or reschedule your own collections</li>
                <li>✅ <strong>View Service Areas:</strong> Check which areas we service</li>
                <li>✅ <strong>Track Collections:</strong> Monitor the status of your requests</li>
                <li>✅ <strong>Account Management:</strong> Update your profile and contact information</li>
            </ul>
            
            <div style="background: #f7fafc; border: 1px solid #cbd5e0; border-radius: 8px; padding: 15px; margin-top: 15px;">
                <p><strong>📧 Your Account Details:</strong></p>
                <ul style="margin: 10px 0;">
                    <li><strong>Name:</strong> {{ $user->name }}</li>
                    <li><strong>Email:</strong> {{ $user->email }}</li>
                    @if($user->phone)
                        <li><strong>Phone:</strong> {{ $user->phone }}</li>
                    @endif
                    @if($user->address)
                        <li><strong>Address:</strong> {{ $user->address }}</li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</body>
</html>
