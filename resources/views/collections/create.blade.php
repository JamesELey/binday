<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book New Collection - Bin Collection Schedules</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .header {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .nav-links {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .nav-links a {
            background: #007cba;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
            font-size: 14px;
        }
        .nav-links a:hover {
            background: #005a87;
        }
        .content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .form-group textarea {
            height: 80px;
            resize: vertical;
        }
        .form-row {
            display: flex;
            gap: 20px;
        }
        .form-row .form-group {
            flex: 1;
        }
        .submit-btn {
            background: #28a745;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
        }
        .submit-btn:hover {
            background: #218838;
        }
        .required {
            color: #dc3545;
        }
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>➕ Book New Collection</h1>
        <p>Schedule a new waste collection appointment.</p>
        
        <div class="nav-links">
            <a href="{{ route('bins.index') }}">🏠 Home</a>
            <a href="{{ route('collections.index') }}">📋 View All Collections</a>
            <a href="{{ route('collections.manage') }}" style="background: #ffc107; color: #212529;">✏️ Edit Collections</a>
            <a href="{{ route('bins.map') }}">🗺️ View Map</a>
        </div>
    </div>

    <div class="content">
        <h2>Collection Details</h2>
        <p>Please fill in all the required information to book your collection:</p>

        @if(session('success'))
            <div style="padding: 15px; background-color: #d4edda; color: #155724; border-radius: 5px; margin-bottom: 20px;">
                {{ session('success') }}
            </div>
        @endif

        @if(session('area_error'))
            <div style="padding: 20px; background-color: #fff3cd; color: #856404; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #ffc107;">
                <h4>⚠️ Area Not Covered</h4>
                <p>{{ session('area_error') }}</p>
                <div style="margin-top: 15px; padding: 15px; background-color: #f8f9fa; border-radius: 5px;">
                    <h5>📞 Contact Information</h5>
                    <p><strong>Email:</strong> <a href="mailto:enquiries@thebinday.co.uk">enquiries@thebinday.co.uk</a></p>
                    <p><strong>What to include:</strong> Your full address, postcode, and preferred service dates</p>
                    <p><strong>Response time:</strong> We typically respond within 24-48 hours</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div style="padding: 15px; background-color: #f8d7da; color: #721c24; border-radius: 5px; margin-bottom: 20px;">
                <h4>❌ Validation Errors</h4>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('collections.store') }}" method="POST">
            @csrf
            
            <div class="form-row">
                <div class="form-group">
                    <label for="customer_name">Customer Name <span class="required">*</span></label>
                    <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name') }}" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number <span class="required">*</span></label>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder="e.g., 07123456789" required>
                </div>
            </div>

            <div class="form-group">
                <label for="address">Collection Address <span class="required">*</span></label>
                <textarea id="address" name="address" placeholder="Enter the full collection address including postcode" required>{{ old('address') }}</textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="bin_type">Bin Type <span class="required">*</span></label>
                    <select id="bin_type" name="bin_type" required>
                        <option value="">Select bin type...</option>
                        @if(isset($allBinTypes) && count($allBinTypes) > 0)
                            @foreach($allBinTypes as $binType)
                                <option value="{{ $binType }}" {{ old('bin_type') === $binType ? 'selected' : '' }}>
                                    @if($binType === 'Food' || $binType === 'Food Waste')
                                        🥬 {{ $binType }}
                                    @elseif($binType === 'Recycling')
                                        ♻️ {{ $binType }}
                                    @elseif($binType === 'Garden' || $binType === 'Garden Waste')
                                        🌿 {{ $binType }}
                                    @elseif($binType === 'General Waste' || $binType === 'Residual Waste')
                                        🗑️ {{ $binType }}
                                    @elseif($binType === 'Glass')
                                        🍾 {{ $binType }}
                                    @elseif($binType === 'Paper')
                                        📄 {{ $binType }}
                                    @elseif($binType === 'Plastic')
                                        ♻️ {{ $binType }}
                                    @elseif($binType === 'Textiles')
                                        👕 {{ $binType }}
                                    @elseif($binType === 'Electronics')
                                        📱 {{ $binType }}
                                    @elseif($binType === 'Hazardous')
                                        ⚠️ {{ $binType }}
                                    @elseif($binType === 'Bulky Items')
                                        📦 {{ $binType }}
                                    @else
                                        📋 {{ $binType }}
                                    @endif
                                </option>
                            @endforeach
                        @else
                            <option value="Food" {{ old('bin_type') === 'Food' ? 'selected' : '' }}>🥬 Food</option>
                            <option value="Recycling" {{ old('bin_type') === 'Recycling' ? 'selected' : '' }}>♻️ Recycling</option>
                            <option value="Garden" {{ old('bin_type') === 'Garden' ? 'selected' : '' }}>🌿 Garden</option>
                        @endif
                    </select>
                    @if(isset($areas) && count($areas) > 0)
                        <small style="color: #666; font-size: 14px; margin-top: 5px; display: block;">
                            Available bin types depend on your collection area
                        </small>
                    @endif
                </div>
                <div class="form-group">
                    <label for="collection_date">Collection Date <span class="required">*</span></label>
                    <input type="date" id="collection_date" name="collection_date" value="{{ old('collection_date') }}" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="collection_time">Preferred Time</label>
                    <select id="collection_time" name="collection_time">
                        <option value="08:00">08:00 - Early Morning</option>
                        <option value="09:00">09:00 - Morning</option>
                        <option value="10:00">10:00 - Mid Morning</option>
                        <option value="11:00">11:00 - Late Morning</option>
                        <option value="12:00">12:00 - Noon</option>
                        <option value="13:00">13:00 - Early Afternoon</option>
                        <option value="14:00">14:00 - Afternoon</option>
                        <option value="15:00">15:00 - Mid Afternoon</option>
                        <option value="16:00">16:00 - Late Afternoon</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="Scheduled">Scheduled</option>
                        <option value="Pending">Pending</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="notes">Special Instructions</label>
                <textarea id="notes" name="notes" placeholder="Any special instructions for the collection team (e.g., bin location, access requirements, etc.)"></textarea>
            </div>

            <button type="submit" class="submit-btn">📋 Book Collection</button>
        </form>

        <div style="margin-top: 30px; padding: 20px; background: #fff3cd; border-radius: 5px;">
            <h3>📝 Important Notes</h3>
            <ul>
                <li><strong>Collection Times:</strong> These are preferred times - actual collection may vary by ±2 hours</li>
                <li><strong>Bin Placement:</strong> Please ensure bins are accessible and visible from the road</li>
                <li><strong>Weather:</strong> Collections may be delayed in severe weather conditions</li>
                <li><strong>Contact:</strong> You will receive a confirmation call within 24 hours</li>
            </ul>
        </div>
    </div>

    <script>
        // Set minimum date to today
        document.getElementById('collection_date').min = new Date().toISOString().split('T')[0];
    </script>
</body>
</html>
