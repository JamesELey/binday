<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BinDay</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 8px;
        }
        
        .logo p {
            color: #666;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .remember-me input {
            margin-right: 8px;
        }
        
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-1px);
        }
        
        .link-section {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e1e5e9;
        }
        
        .link-section a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .link-section a:hover {
            text-decoration: underline;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .role-info {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
        }
        
        .role-info h4 {
            color: #1976d2;
            margin-bottom: 8px;
        }
        
        .role-list {
            font-size: 14px;
            color: #424242;
        }
        
        .role-list strong {
            color: #1976d2;
        }
        
        .demo-credentials {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
        }
        
        .demo-credentials h4 {
            color: #856404;
            margin-bottom: 12px;
            font-size: 16px;
        }
        
        .demo-account {
            background: #f8f9fa;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 8px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
        
        .demo-account:last-child {
            margin-bottom: 0;
        }
        
        .demo-account .role {
            font-weight: bold;
            color: #495057;
            margin-bottom: 4px;
        }
        
        .demo-account .credentials {
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>🗑️ BinDay</h1>
            <p>Bin Collection Management System</p>
        </div>
        
        <div class="demo-credentials">
            <h4>🔑 Demo Login Credentials</h4>
            <div class="demo-account">
                <div class="role">👑 Admin Access:</div>
                <div class="credentials">admin@binday.com / password123</div>
            </div>
            <div class="demo-account">
                <div class="role">👷 Worker Access:</div>
                <div class="credentials">worker@binday.com / password123</div>
            </div>
            <div class="demo-account">
                <div class="role">👤 Customer Access:</div>
                <div class="credentials">customer@binday.com / password123</div>
            </div>
        </div>
        
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif
        
        @if ($errors->any())
            <div class="alert alert-error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        
        <div class="role-info">
            <h4>🔐 User Roles</h4>
            <div class="role-list">
                <strong>👑 Admin:</strong> Full system access<br>
                <strong>👷 Worker:</strong> Manage assigned areas<br>
                <strong>👤 Customer:</strong> Manage own collections
            </div>
        </div>
        
        <form method="POST" action="{{ route('login') }}">
            @csrf
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="remember-me">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Remember me</label>
            </div>
            
            <button type="submit" class="btn">🚀 Sign In</button>
        </form>
        
        <div class="link-section">
            <p>Don't have an account? <a href="{{ route('register') }}">Sign up here</a></p>
        </div>
    </div>
</body>
</html>