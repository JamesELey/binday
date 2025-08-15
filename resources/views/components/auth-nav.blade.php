<div class="auth-nav" style="background: #f8f9fa; border-bottom: 1px solid #dee2e6; padding: 10px 0; margin-bottom: 20px;">
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center;">
        
        <!-- Left side - App branding -->
        <div style="display: flex; align-items: center;">
            <a href="{{ route('bins.index') }}" style="text-decoration: none; color: #007bff; font-weight: bold; font-size: 18px; margin-right: 30px;">
                🗑️ BinDay
            </a>
            
            <!-- Main navigation links -->
            <div style="display: flex; gap: 20px;">
                <a href="{{ route('bins.index') }}" style="text-decoration: none; color: #6c757d; padding: 5px 10px; border-radius: 4px; transition: all 0.3s;">
                    🏠 Home
                </a>
                
                @auth
                    @if(auth()->user()->isAdmin() || auth()->user()->isWorker())
                        <a href="{{ url('/bins/map') }}" style="text-decoration: none; color: #6c757d; padding: 5px 10px; border-radius: 4px; transition: all 0.3s;">
                            🗺️ Map
                        </a>
                        <a href="{{ url('/collections') }}" style="text-decoration: none; color: #6c757d; padding: 5px 10px; border-radius: 4px; transition: all 0.3s;">
                            📋 All Collections
                        </a>
                        <a href="{{ url('/routes') }}" style="text-decoration: none; color: #fd7e14; padding: 5px 10px; border-radius: 4px; transition: all 0.3s;">
                            🚛 Routes
                        </a>
                    @endif
                    
                    @if(auth()->user()->isCustomer())
                        <a href="{{ route('collections.create') }}" style="text-decoration: none; color: #28a745; padding: 5px 10px; border-radius: 4px; transition: all 0.3s;">
                            ➕ New Collection
                        </a>
                    @endif
                    
                    @if(auth()->user()->isAdmin())
                        <a href="{{ url('/admin/seed') }}" style="text-decoration: none; color: #dc3545; padding: 5px 10px; border-radius: 4px; transition: all 0.3s;">
                            ⚙️ Admin
                        </a>
                    @endif
                    
                    @if(auth()->user()->isAdmin() || auth()->user()->isWorker())
                        <a href="{{ url('/areas') }}" style="text-decoration: none; color: #17a2b8; padding: 5px 10px; border-radius: 4px; transition: all 0.3s;">
                            🏢 Areas
                        </a>
                    @endif
                    
                    <a href="{{ route('collections.manage') }}" style="text-decoration: none; color: #28a745; padding: 5px 10px; border-radius: 4px; transition: all 0.3s;">
                        @if(auth()->user()->isCustomer())
                            📊 My Collections
                        @else
                            📊 Manage Collections
                        @endif
                    </a>
                @else
                    <span style="color: #6c757d; font-style: italic; padding: 5px 10px;">
                        🔒 Login to access Collections
                    </span>
                @endauth
            </div>
        </div>
        
        <!-- Right side - Authentication -->
        <div style="display: flex; align-items: center; gap: 15px;">
            @auth
                <!-- Logged in user info -->
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="color: #495057; font-size: 14px;">
                        👋 Hello, <strong>{{ auth()->user()->name }}</strong>
                    </span>
                    
                    <!-- Role badge -->
                    @php
                        $roleColors = [
                            'admin' => '#dc3545',
                            'worker' => '#fd7e14', 
                            'customer' => '#28a745'
                        ];
                        $roleColor = $roleColors[auth()->user()->role] ?? '#6c757d';
                    @endphp
                    <span style="background: {{ $roleColor }}; color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; text-transform: uppercase; font-weight: bold;">
                        {{ auth()->user()->role }}
                    </span>
                    
                    <!-- Dashboard link -->
                    <a href="{{ route('dashboard') }}" style="text-decoration: none; color: #007bff; padding: 5px 10px; border-radius: 4px; border: 1px solid #007bff; transition: all 0.3s;">
                        📊 Dashboard
                    </a>
                    
                    <!-- Logout form -->
                    <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                        @csrf
                        <button type="submit" style="background: #dc3545; color: white; border: none; padding: 5px 12px; border-radius: 4px; cursor: pointer; font-size: 14px; transition: all 0.3s;">
                            🚪 Logout
                        </button>
                    </form>
                </div>
            @else
                <!-- Guest user - login/register options -->
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="color: #6c757d; font-size: 14px;">
                        👤 Guest
                    </span>
                    
                    <a href="{{ route('login') }}" style="text-decoration: none; color: #007bff; padding: 5px 12px; border-radius: 4px; border: 1px solid #007bff; transition: all 0.3s;">
                        🔑 Login
                    </a>
                    
                    <a href="{{ route('register') }}" style="text-decoration: none; background: #28a745; color: white; padding: 5px 12px; border-radius: 4px; transition: all 0.3s;">
                        📝 Register
                    </a>
                </div>
            @endauth
        </div>
    </div>
</div>

<style>
/* Hover effects for navigation links */
.auth-nav a:hover {
    background-color: #e9ecef !important;
    color: #495057 !important;
}

.auth-nav form button:hover {
    background-color: #c82333 !important;
}

.auth-nav a[href*="login"]:hover {
    background-color: #007bff !important;
    color: white !important;
}

.auth-nav a[href*="register"]:hover {
    background-color: #218838 !important;
}

.auth-nav a[href*="dashboard"]:hover {
    background-color: #007bff !important;
    color: white !important;
}

/* Responsive design */
@media (max-width: 768px) {
    .auth-nav > div {
        flex-direction: column !important;
        gap: 15px !important;
    }
    
    .auth-nav > div > div {
        justify-content: center !important;
    }
}
</style>
