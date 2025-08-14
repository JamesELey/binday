<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bin Types - {{ $area->name }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { margin-bottom: 30px; }
        .title { color: #333; margin: 0; font-size: 28px; }
        .subtitle { color: #666; margin: 5px 0 0 0; font-size: 16px; }
        .nav-links { margin: 20px 0; }
        .nav-links a { display: inline-block; margin-right: 15px; padding: 8px 16px; background: #e5e7eb; color: #374151; text-decoration: none; border-radius: 4px; font-size: 14px; }
        .nav-links a:hover { background: #d1d5db; }
        .nav-links a.active { background: #3b82f6; color: white; }
        
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb; }
        
        .section { margin-bottom: 30px; }
        .section-title { font-size: 20px; color: #333; margin-bottom: 15px; border-bottom: 2px solid #3b82f6; padding-bottom: 5px; }
        
        .area-info { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
        .area-info h3 { margin-top: 0; color: #495057; }
        .area-details { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
        .detail-item { }
        .detail-label { font-weight: bold; color: #6c757d; display: block; }
        .detail-value { color: #333; }
        
        .bin-types-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .bin-type-card { border: 2px solid #e5e7eb; border-radius: 8px; padding: 15px; background: #f9fafb; }
        .bin-type-card.active { border-color: #3b82f6; background: #eff6ff; }
        .bin-type-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
        .bin-type-name { font-weight: bold; font-size: 16px; color: #374151; }
        .bin-type-color { width: 20px; height: 20px; border-radius: 50%; border: 2px solid #d1d5db; }
        .bin-type-checkbox { margin-right: 10px; }
        .remove-btn { background: #ef4444; color: white; border: none; padding: 4px 8px; border-radius: 4px; font-size: 12px; cursor: pointer; }
        .remove-btn:hover { background: #dc2626; }
        
        .custom-bin-types { margin-top: 20px; }
        .add-bin-type { display: flex; gap: 10px; align-items: center; margin-bottom: 15px; }
        .add-bin-type input { flex: 1; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; }
        .add-btn { background: #10b981; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; }
        .add-btn:hover { background: #059669; }
        
        .form-actions { display: flex; gap: 10px; margin-top: 30px; }
        .btn { padding: 12px 24px; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; text-decoration: none; display: inline-block; }
        .btn-primary { background: #3b82f6; color: white; }
        .btn-primary:hover { background: #2563eb; }
        .btn-secondary { background: #6b7280; color: white; }
        .btn-secondary:hover { background: #4b5563; }
        
        .current-bin-types { background: #f0f9ff; padding: 15px; border-radius: 8px; border-left: 4px solid #3b82f6; }
        .bin-type-list { list-style: none; padding: 0; margin: 10px 0 0 0; }
        .bin-type-list li { display: inline-block; background: #3b82f6; color: white; padding: 4px 8px; margin: 2px; border-radius: 12px; font-size: 14px; }
        
        .dynamic-inputs { margin-top: 15px; }
        .dynamic-input { display: flex; gap: 10px; margin-bottom: 10px; align-items: center; }
        .dynamic-input input { flex: 1; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; }
        .remove-input { background: #ef4444; color: white; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; }
        .add-input { background: #10b981; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; margin-top: 10px; }
        
        .status-message { padding: 10px; border-radius: 4px; margin: 10px 0; display: none; }
        .status-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .status-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="title">Manage Bin Types</h1>
            <p class="subtitle">Configure allowed bin types for {{ $area->name }}</p>
        </div>

        <div class="nav-links">
            <a href="{{ route('areas.index') }}">← Back to Areas</a>
            <a href="{{ route('areas.edit', $area->id) }}">Edit Area Details</a>
            <a href="#" class="active">Manage Bin Types</a>
        </div>

        @if(session('success'))
            <div class="success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="error">{{ session('error') }}</div>
        @endif

        <div class="area-info">
            <h3>Area Information</h3>
            <div class="area-details">
                <div class="detail-item">
                    <span class="detail-label">Name:</span>
                    <span class="detail-value">{{ $area->name }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Type:</span>
                    <span class="detail-value">{{ ucfirst($area->type) }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value">{{ $area->active ? 'Active' : 'Inactive' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Current Bin Types:</span>
                    <div class="detail-value">
                        @if(!empty($area->bin_types))
                            <ul class="bin-type-list">
                                @foreach($area->bin_types as $type)
                                    <li>{{ $type }}</li>
                                @endforeach
                            </ul>
                        @else
                            <span>No bin types configured</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('areas.updateBinTypes', $area->id) }}" method="POST" id="binTypesForm">
            @csrf
            @method('PUT')

            <div class="section">
                <h2 class="section-title">Available Bin Types</h2>
                <p>Select which bin types should be available for collections in this area:</p>
                
                <div class="bin-types-grid">
                    @foreach($allBinTypes as $binType => $color)
                        <div class="bin-type-card {{ in_array($binType, $area->bin_types ?? []) ? 'active' : '' }}">
                            <div class="bin-type-header">
                                <label class="bin-type-name">
                                    <input type="checkbox" 
                                           name="active_bin_types[]" 
                                           value="{{ $binType }}" 
                                           class="bin-type-checkbox"
                                           {{ in_array($binType, $area->bin_types ?? []) ? 'checked' : '' }}
                                           onchange="toggleBinTypeCard(this)">
                                    {{ $binType }}
                                </label>
                                <div class="bin-type-color" style="background-color: {{ $color }};"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="section">
                <h2 class="section-title">Add Custom Bin Types</h2>
                <p>Add new bin types specific to this area:</p>
                
                <div class="dynamic-inputs" id="customBinTypes">
                    <div class="dynamic-input">
                        <input type="text" 
                               name="new_bin_types[]" 
                               placeholder="Enter custom bin type name"
                               maxlength="50">
                        <button type="button" class="remove-input" onclick="removeInput(this)">Remove</button>
                    </div>
                </div>
                
                <button type="button" class="add-input" onclick="addBinTypeInput()">+ Add Another</button>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Update Bin Types</button>
                <a href="{{ route('areas.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>

        <div id="statusMessage" class="status-message"></div>
    </div>

    <script>
        // CSRF token for AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function toggleBinTypeCard(checkbox) {
            const card = checkbox.closest('.bin-type-card');
            if (checkbox.checked) {
                card.classList.add('active');
            } else {
                card.classList.remove('active');
            }
        }

        function addBinTypeInput() {
            const container = document.getElementById('customBinTypes');
            const newInput = document.createElement('div');
            newInput.className = 'dynamic-input';
            newInput.innerHTML = `
                <input type="text" 
                       name="new_bin_types[]" 
                       placeholder="Enter custom bin type name"
                       maxlength="50">
                <button type="button" class="remove-input" onclick="removeInput(this)">Remove</button>
            `;
            container.appendChild(newInput);
        }

        function removeInput(button) {
            const container = document.getElementById('customBinTypes');
            if (container.children.length > 1) {
                button.parentElement.remove();
            }
        }

        function showStatus(message, isSuccess) {
            const statusDiv = document.getElementById('statusMessage');
            statusDiv.textContent = message;
            statusDiv.className = `status-message ${isSuccess ? 'status-success' : 'status-error'}`;
            statusDiv.style.display = 'block';
            
            setTimeout(() => {
                statusDiv.style.display = 'none';
            }, 5000);
        }

        // Quick add bin type functionality (for future enhancement)
        function quickAddBinType(binType) {
            fetch(`{{ route('areas.addBinType', $area->id) }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    bin_type: binType
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showStatus(data.message, true);
                    // Refresh page to show new bin type
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    showStatus(data.error || 'Failed to add bin type', false);
                }
            })
            .catch(error => {
                showStatus('Error adding bin type', false);
            });
        }

        // Form validation
        document.getElementById('binTypesForm').addEventListener('submit', function(e) {
            const checkboxes = document.querySelectorAll('input[name="active_bin_types[]"]:checked');
            const customInputs = document.querySelectorAll('input[name="new_bin_types[]"]');
            const hasCustomTypes = Array.from(customInputs).some(input => input.value.trim() !== '');
            
            if (checkboxes.length === 0 && !hasCustomTypes) {
                e.preventDefault();
                showStatus('Please select at least one bin type or add a custom type', false);
                return false;
            }
        });
    </script>
</body>
</html>
