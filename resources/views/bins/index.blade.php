<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bin Day App</title>
    <link href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css" rel="stylesheet">
    <style>
        body { max-width: 1000px; margin: 2rem auto; }
        header { display:flex; align-items:center; justify-content:space-between; gap: 1rem; }
        nav ul { display:flex; gap: .5rem; list-style: none; padding: 0; margin: 0; }
        .actions { display: flex; gap: .5rem; }
        table { width: 100%; }
        .muted { color: #666; }
    </style>
    <script>
        function confirmDelete(e){ if(!confirm('Delete this schedule?')) e.preventDefault(); }
    </script>
    </head>
<body>
<header>
    <div>
        <h2>Bin Day App</h2>
        <p class="muted">Track which bin to put out and when, and whether to bring it back.</p>
    </div>
    <nav>
        <ul>
            <li><a href="{{ route('bins.index') }}">Home</a></li>
            <li><a href="{{ route('bins.create') }}">Add Schedule</a></li>
            <li><a href="{{ route('bins.map') }}">Map</a></li>
            <li><a href="{{ route('bins.mapByDate') }}">Map by Date</a></li>
            <li><a href="{{ route('areas.index') }}">Allowed Areas</a></li>
            @if(session('is_admin'))
                <li><a href="{{ route('admin.settings') }}">Admin</a></li>
                <li>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button type="submit" class="secondary">Logout</button>
                    </form>
                </li>
            @else
                <li><a href="{{ route('admin.loginForm') }}">Admin Login</a></li>
            @endif
        </ul>
    </nav>
    
</header>

<main>
    <script>
        // Ensure dev SW cleanup runs (if a previous SW existed)
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(()=>{});
        }
    </script>
    @if (session('status'))
        <article role="alert">{{ session('status') }}</article>
    @endif

    <p style="display:flex; gap:.5rem; flex-wrap:wrap; align-items:center;">
        <a href="{{ route('bins.create') }}" role="button">Add schedule</a>
        <a href="{{ route('bins.map') }}" role="button" class="secondary">View map</a>
        <form method="POST" action="{{ route('bins.geocodeAll') }}" onsubmit="return confirm('Geocode all missing coordinates? This makes external requests.');">
            @csrf
            <input type="hidden" name="limit" value="200">
            <button type="submit" class="contrast">Geocode all missing</button>
        </form>
    </p>

    <table>
        <thead>
        <tr>
            <th>Address</th>
            <th>Postcode</th>
            <th>Bin</th>
            <th>Start date</th>
            <th>Lat/Lng</th>
            <th>Put out</th>
            <th>Bring back</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @forelse($schedules as $schedule)
            <tr>
                <td>{{ $schedule->address }}</td>
                <td>{{ $schedule->postcode }}</td>
                <td>{{ $schedule->bin_type }}</td>
                <td>{{ $schedule->start_date ?? '-' }}</td>
                <td class="muted" style="white-space:nowrap">{{ $schedule->lat ?? '-' }}, {{ $schedule->lng ?? '-' }}</td>
                <td>{{ $schedule->put_out ? 'Yes' : 'No' }}</td>
                <td>{{ $schedule->bring_back ? 'Yes' : 'No' }}</td>
                <td class="actions">
                    @if(session('is_admin'))
                        <a href="{{ route('bins.edit', $schedule) }}" role="button" class="secondary">Edit</a>
                    @endif
                    <form method="POST" action="{{ route('bins.destroy', $schedule) }}" onsubmit="confirmDelete(event)">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="contrast">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="muted">No schedules yet.</td></tr>
        @endforelse
        </tbody>
    </table>

    <nav>
        {{ $schedules->links() }}
    </nav>
</main>

</body>
</html>


