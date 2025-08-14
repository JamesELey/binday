<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User - BinDay Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f5;
            color: #333;
        }
        
        .header {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
            margin-bottom: 2rem;
        }
        
        .header h1 {
            color: #2d3748;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #2d3748;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="tel"],
        textarea,
        select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        
        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #3182ce;
        }
        
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
        }
        
        .areas-selection {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 1rem;
            max-height: 200px;
            overflow-y: auto;
            background: #f7fafc;
        }
        
        .area-checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .area-checkbox:last-child {
            margin-bottom: 0;
        }
        
        .area-checkbox input[type="checkbox"] {
            width: auto;
        }
        
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .btn-primary {
            background: #3182ce;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2c5aa0;
        }
        
        .btn-secondary {
            background: #718096;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #4a5568;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e2e8f0;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .alert-error {
            background: #fed7d7;
            color: #742a2a;
            border: 1px solid #fc8181;
        }
        
        .nav-back {
            margin-bottom: 1rem;
        }
        
        .nav-back a {
            color: #3182ce;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .nav-back a:hover {
            text-decoration: underline;
        }
        
        .role-dependent {
            display: none;
        }
        
        .role-dependent.active {
            display: block;
        }
        
        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    @include('components.auth-nav')
    
    <div class="header">
        <div class="container">
            <div class="nav-back">
                <a href="{{ route('admin.users.index') }}">← Back to Users</a>
            </div>
            <h1>➕ Create New User</h1>
        </div>
    </div>
    
    <div class="container">
        @if ($errors->any())
            <div class="alert alert-error">
                <strong>Please fix the following errors:</strong>
                <ul style="margin-top: 0.5rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        <div class="card">
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password_confirmation">Confirm Password *</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="role">User Role *</label>
                        <select id="role" name="role" required onchange="toggleAreaSelection()">
                            <option value="">Select Role</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>👑 Administrator</option>
                            <option value="worker" {{ old('role') === 'worker' ? 'selected' : '' }}>👷 Worker</option>
                            <option value="customer" {{ old('role') === 'customer' ? 'selected' : '' }}>👤 Customer</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="{{ old('phone') }}">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" placeholder="Full address including postcode">{{ old('address') }}</textarea>
                </div>
                
                <div class="form-group">
                    <div class="checkbox-group">
                        <input type="checkbox" id="active" name="active" value="1" {{ old('active', '1') ? 'checked' : '' }}>
                        <label for="active">Active User</label>
                    </div>
                </div>
                
                <div id="area-selection" class="form-group role-dependent">
                    <label>Assigned Areas (for Workers only)</label>
                    <div class="areas-selection">
                        @forelse($areas as $area)
                            <div class="area-checkbox">
                                <input type="checkbox" 
                                       id="area_{{ $area->id }}" 
                                       name="assigned_area_ids[]" 
                                       value="{{ $area->id }}"
                                       {{ in_array($area->id, old('assigned_area_ids', [])) ? 'checked' : '' }}>
                                <label for="area_{{ $area->id }}">{{ $area->name }}</label>
                            </div>
                        @empty
                            <p style="color: #a0aec0; font-style: italic;">No areas available. Create areas first.</p>
                        @endforelse
                    </div>
                </div>
                
                <div class="form-actions">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function toggleAreaSelection() {
            const roleSelect = document.getElementById('role');
            const areaSelection = document.getElementById('area-selection');
            
            if (roleSelect.value === 'worker') {
                areaSelection.classList.add('active');
            } else {
                areaSelection.classList.remove('active');
                // Uncheck all area checkboxes when not a worker
                const checkboxes = areaSelection.querySelectorAll('input[type="checkbox"]');
                checkboxes.forEach(checkbox => checkbox.checked = false);
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleAreaSelection();
        });
    </script>
</body>
</html>
