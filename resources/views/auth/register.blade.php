<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - BinDay</title>
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
        
        .register-container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 500px;
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
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"],
        select,
        textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        textarea {
            resize: vertical;
            min-height: 80px;
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
        
        .role-selection {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
        }
        
        .role-selection h4 {
            color: #495057;
            margin-bottom: 12px;
        }
        
        .role-option {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            padding: 8px;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .role-option:hover {
            background: #e9ecef;
        }
        
        .role-option input {
            margin-right: 10px;
            width: auto;
        }
        
        .role-option label {
            margin: 0;
            cursor: pointer;
            font-weight: normal;
        }
        
        .role-description {
            color: #6c757d;
            font-size: 14px;
            margin-left: 24px;
        }
        
        .nav-links {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .nav-links a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            margin: 0 10px;
        }
        
        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <h1>🗑️ BinDay</h1>
            <p>Join our Bin Collection Management System</p>
        </div>
        
        <div class="nav-links">
            <a href="{{ route('bins.index') }}">← Back to Home</a>
            <a href="{{ route('bins.map') }}">View Map</a>
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
        
        <form method="POST" action="{{ route('register') }}">
            @csrf
            
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Phone (Optional)</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}">
                </div>
                
                <div class="form-group">
                    <label for="address">Address (Optional)</label>
                    <textarea id="address" name="address" placeholder="Your full address">{{ old('address') }}</textarea>
                </div>
            </div>
            
            <div class="role-selection">
                <h4>👤 Account Type</h4>
                
                <div class="role-option">
                    <input type="radio" id="role_customer" name="role" value="customer" 
                           {{ old('role', 'customer') === 'customer' ? 'checked' : '' }}>
                    <label for="role_customer">
                        <strong>🏠 Customer</strong>
                    </label>
                </div>
                <div class="role-description">
                    Book and manage your own bin collections
                </div>
                
                <div class="role-option">
                    <input type="radio" id="role_worker" name="role" value="worker" 
                           {{ old('role') === 'worker' ? 'checked' : '' }}>
                    <label for="role_worker">
                        <strong>👷 Worker</strong>
                    </label>
                </div>
                <div class="role-description">
                    Manage collections for assigned areas (requires admin approval)
                </div>
            </div>
            
            <button type="submit" class="btn">🎉 Create Account</button>
        </form>
        
        <div class="link-section">
            <p>Already have an account? <a href="{{ route('login') }}">Sign in here</a></p>
        </div>
    </div>
</body>
</html>