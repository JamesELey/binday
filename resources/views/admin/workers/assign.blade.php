<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Workers - BinDay Admin</title>
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
        
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .workers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
        }
        
        .worker-card {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
            background: #f7fafc;
        }
        
        .worker-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        
        .worker-info h3 {
            color: #2d3748;
            margin-bottom: 0.25rem;
        }
        
        .worker-info p {
            color: #4a5568;
            font-size: 0.875rem;
        }
        
        .worker-status {
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
        
        .areas-section {
            margin-bottom: 1.5rem;
        }
        
        .areas-section h4 {
            color: #2d3748;
            margin-bottom: 0.75rem;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .current-areas {
            margin-bottom: 1rem;
        }
        
        .area-tag {
            display: inline-block;
            background: #bee3f8;
            color: #2a4365;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            margin: 0.125rem;
        }
        
        .no-areas {
            color: #a0aec0;
            font-style: italic;
            font-size: 0.875rem;
        }
        
        .area-selection {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.75rem;
            background: white;
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
        
        .area-checkbox label {
            font-size: 0.875rem;
            color: #2d3748;
            cursor: pointer;
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
            font-size: 0.875rem;
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
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.75rem;
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
        
        .quick-actions {
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .no-workers {
            text-align: center;
            color: #a0aec0;
            padding: 3rem;
            font-style: italic;
        }
        
        @media (max-width: 768px) {
            .workers-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-actions {
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
            <h1>👷 Assign Workers to Areas</h1>
        </div>
    </div>
    
    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif
        
        <div class="quick-actions">
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">➕ Add New Worker</a>
            <a href="{{ route('areas.index') }}" class="btn btn-secondary">🏘️ Manage Areas</a>
        </div>
        
        @if($workers->count() > 0)
            <div class="workers-grid">
                @foreach($workers as $worker)
                    <div class="worker-card">
                        <div class="worker-header">
                            <div class="worker-info">
                                <h3>{{ $worker->name }}</h3>
                                <p>{{ $worker->email }}</p>
                                @if($worker->phone)
                                    <p>📞 {{ $worker->phone }}</p>
                                @endif
                            </div>
                            <span class="worker-status status-{{ $worker->active ? 'active' : 'inactive' }}">
                                {{ $worker->active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        
                        <div class="areas-section">
                            <h4>Current Assignments</h4>
                            <div class="current-areas">
                                @if(!empty($worker->assigned_area_ids) && count($worker->assigned_area_ids) > 0)
                                    @foreach($worker->assignedAreas() as $area)
                                        <span class="area-tag">{{ $area->name }}</span>
                                    @endforeach
                                @else
                                    <div class="no-areas">No areas assigned</div>
                                @endif
                            </div>
                        </div>
                        
                        <form method="POST" action="{{ route('admin.workers.update') }}">
                            @csrf
                            <input type="hidden" name="worker_id" value="{{ $worker->id }}">
                            
                            <div class="areas-section">
                                <h4>Available Areas</h4>
                                <div class="area-selection">
                                    @forelse($areas as $area)
                                        <div class="area-checkbox">
                                            <input type="checkbox" 
                                                   id="worker_{{ $worker->id }}_area_{{ $area->id }}" 
                                                   name="assigned_area_ids[]" 
                                                   value="{{ $area->id }}"
                                                   {{ in_array($area->id, $worker->assigned_area_ids ?? []) ? 'checked' : '' }}>
                                            <label for="worker_{{ $worker->id }}_area_{{ $area->id }}">
                                                {{ $area->name }}
                                                @if($area->description)
                                                    <br><small style="color: #4a5568;">{{ $area->description }}</small>
                                                @endif
                                            </label>
                                        </div>
                                    @empty
                                        <div class="no-areas">No areas available. Create areas first.</div>
                                    @endforelse
                                </div>
                            </div>
                            
                            @if($areas->count() > 0)
                                <button type="submit" class="btn btn-primary btn-sm">Update Assignments</button>
                            @endif
                        </form>
                    </div>
                @endforeach
            </div>
        @else
            <div class="card">
                <div class="no-workers">
                    <h3>No Workers Found</h3>
                    <p>Create worker accounts first to assign them to areas.</p>
                    <div style="margin-top: 1rem;">
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">➕ Create Worker Account</a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</body>
</html>
