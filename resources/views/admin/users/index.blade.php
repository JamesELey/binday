<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - BinDay Admin</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .actions {
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        
        .btn-success {
            background: #38a169;
            color: white;
        }
        
        .btn-warning {
            background: #ed8936;
            color: white;
        }
        
        .btn-danger {
            background: #e53e3e;
            color: white;
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .table th {
            background: #f7fafc;
            font-weight: 600;
            color: #2d3748;
        }
        
        .table tbody tr:hover {
            background: #f7fafc;
        }
        
        .role-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .role-admin {
            background: #fed7d7;
            color: #742a2a;
        }
        
        .role-worker {
            background: #bee3f8;
            color: #2a4365;
        }
        
        .role-customer {
            background: #c6f6d5;
            color: #22543d;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-active {
            background: #c6f6d5;
            color: #22543d;
        }
        
        .status-inactive {
            background: #fed7d7;
            color: #742a2a;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background: #c6f6d5;
            color: #22543d;
            border: 1px solid #9ae6b4;
        }
        
        .alert-error {
            background: #fed7d7;
            color: #742a2a;
            border: 1px solid #fc8181;
        }
        
        .areas-list {
            font-size: 0.875rem;
            color: #4a5568;
        }
        
        .area-tag {
            display: inline-block;
            background: #edf2f7;
            color: #2d3748;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            margin: 0.125rem;
            font-size: 0.75rem;
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
    </style>
</head>
<body>
    @include('components.auth-nav')
    
    <div class="header">
        <div class="container">
            <div class="nav-back">
                <a href="{{ route('dashboard') }}">← Back to Dashboard</a>
            </div>
            <h1>👥 Manage Users</h1>
        </div>
    </div>
    
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif
        
        <div class="actions">
            <div>
                <span style="color: #4a5568;">Total Users: {{ $users->count() }}</span>
            </div>
            <div>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">➕ Add New User</a>
                <a href="{{ route('admin.workers.assign') }}" class="btn btn-secondary">👷 Assign Workers</a>
            </div>
        </div>
        
        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Assigned Areas</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <strong>{{ $user->name }}</strong>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="role-badge role-{{ $user->role }}">
                                    @if($user->role === 'admin') 👑 Admin
                                    @elseif($user->role === 'worker') 👷 Worker
                                    @else 👤 Customer
                                    @endif
                                </span>
                            </td>
                            <td>
                                <span class="status-badge status-{{ $user->active ? 'active' : 'inactive' }}">
                                    {{ $user->active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                @if($user->role === 'worker' && !empty($user->assigned_area_ids))
                                    <div class="areas-list">
                                        @foreach($user->assignedAreas() as $area)
                                            <span class="area-tag">{{ $area->name }}</span>
                                        @endforeach
                                    </div>
                                @elseif($user->role === 'admin')
                                    <span style="color: #4a5568; font-style: italic;">All Areas</span>
                                @else
                                    <span style="color: #a0aec0;">-</span>
                                @endif
                            </td>
                            <td>{{ $user->phone ?: '-' }}</td>
                            <td>
                                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-primary">Edit</a>
                                    
                                    <form method="POST" action="{{ route('admin.users.toggle', $user) }}" style="display: inline;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-warning">
                                            {{ $user->active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                    
                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" style="display: inline;" 
                                              onsubmit="return confirm('Are you sure you want to delete this user?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: #a0aec0; padding: 2rem;">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
