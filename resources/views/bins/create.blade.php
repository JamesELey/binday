<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Bin Schedule</title>
    <link href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css" rel="stylesheet">
    <style>
        body { max-width: 800px; margin: 2rem auto; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        #addressResults { max-height: 200px; overflow-y: auto; }
        .muted { color: #666; }
    </style>
    <script>
        const provider = '{{ $provider ?? 'getaddress' }}';
        let lookupTimer = null;

        function scheduleLookup(){
            if (lookupTimer) clearTimeout(lookupTimer);
            lookupTimer = setTimeout(() => lookupAddresses(), 400);
        }

        function isLikelyUkPostcode(value){
            const v = value.toUpperCase().trim();
            // Simple UK postcode heuristic (not exhaustive), avoids obvious noise
            return /^[A-Z]{1,2}\d[A-Z\d]?\s?\d[A-Z]{2}$/.test(v);
        }

        async function lookupAddresses(){
            const postcode = document.getElementById('postcode').value.trim();
            const q = document.getElementById('addressQuery').value.trim();
            if(!postcode){
                document.getElementById('addressResults').innerHTML = '<em class="muted">Enter a postcode</em>';
                return;
            }
            if(!isLikelyUkPostcode(postcode)){
                document.getElementById('addressResults').innerHTML = '<em class="muted">Type a valid UK postcode (e.g. SW1A 1AA)</em>';
                return;
            }
            const url = new URL('{{ route('api.lookup') }}', window.location.origin);
            url.searchParams.set('postcode', postcode.replace(/\s+/g,'').toUpperCase());
            if(q) url.searchParams.set('q', q);
            document.getElementById('addressResults').innerHTML = '<em class="muted">Searching…</em>';
            try {
                const res = await fetch(url.toString());
                const data = await res.json();
                if (!Array.isArray(data) || data.length === 0) {
                    document.getElementById('addressResults').innerHTML = '<em class="muted">No addresses found. Check postcode and API key.</em>';
                    return;
                }
                // In postcodes.io mode, data is postcode; combine with house text if provided
                if (provider === 'postcodes_io') {
                    const pc = postcode.replace(/\s+/g,'').toUpperCase();
                    const combined = (q ? (q + ' ') : '') + pc;
                    selectAddress(combined);
                    document.getElementById('addressResults').innerHTML = `<ul><li>${combined} <small class=\"muted\">(postcode validated)</small></li></ul>`;
                    return;
                }
                if (data.length === 1) {
                    selectAddress(data[0]);
                    document.getElementById('addressResults').innerHTML = `<em class=\"muted\">1 address selected.</em>`;
                    return;
                }
                const list = data.map(a => `<li><button type=\"button\" class=\"secondary\" data-address=\"${encodeURIComponent(a)}\">${a}</button></li>`).join('');
                document.getElementById('addressResults').innerHTML = `<ul>${list}</ul>`;
            } catch (e) {
                document.getElementById('addressResults').innerHTML = '<em class="muted">Lookup failed. Please try again.</em>';
            }
        }
        function selectAddress(addr){
            document.getElementById('address').value = addr;
        }
        window.addEventListener('DOMContentLoaded', () => {
            const postcodeEl = document.getElementById('postcode');
            const queryEl = document.getElementById('addressQuery');
            const resultsEl = document.getElementById('addressResults');
            // Debounced auto lookup as users type
            postcodeEl.addEventListener('input', scheduleLookup);
            queryEl.addEventListener('input', scheduleLookup);
            // Trigger lookup when leaving the postcode field
            postcodeEl.addEventListener('blur', lookupAddresses);
            // Prevent Enter from submitting prematurely inside lookup fields
            [postcodeEl, queryEl].forEach(el => el.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') { e.preventDefault(); lookupAddresses(); }
            }));

            // Delegate clicks on result buttons to populate address
            resultsEl.addEventListener('click', (e) => {
                const btn = e.target.closest('button[data-address]');
                if (!btn) return;
                e.preventDefault();
                const encoded = btn.getAttribute('data-address') || '';
                const addr = decodeURIComponent(encoded);
                selectAddress(addr);
            });
        });
    </script>
</head>
<body>
<header>
    <h3>Add Bin Schedule</h3>
    <p><a href="{{ route('bins.index') }}">Back to list</a></p>
</header>

<main>
    @if ($errors->any())
        <article role="alert">
            <strong>There were some problems with your input:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </article>
    @endif
    @if (session('status'))
        <article role="alert">{{ session('status') }}</article>
    @endif

    <form method="POST" action="{{ route('bins.store') }}">
        @csrf
        <fieldset>
            <legend>Address</legend>
            <div class="grid">
                <label>
                    Postcode
                    <input id="postcode" name="postcode" value="{{ old('postcode') }}" placeholder="e.g. SW1A 1AA" pattern="[A-Za-z]{1,2}[0-9][A-Za-z0-9]? ?[0-9][A-Za-z]{2}" required>
                </label>
                <label>
                    Search address
                    <input id="addressQuery" name="address_search" placeholder="house number / name">
                </label>
            </div>
            <div>
                <small class="muted">Find your address using postcode + optional text, then click a result to populate address.</small>
                <div id="addressResults" class="muted" style="margin-top:.5rem;"><em>Enter a postcode</em></div>
            </div>
            <label>
                Selected address
                <input id="address" name="address" value="{{ old('address') }}" required>
            </label>
        </fieldset>

        <fieldset>
            <legend>Bin details</legend>
            <div class="grid">
                <label>
                    Bin type
                    <select name="bin_type" required>
                        <option value="">Select</option>
                        <option value="Residual Waste" @selected(old('bin_type')==='Residual Waste')>Residual Waste</option>
                        <option value="Recycling + Cardboard" @selected(old('bin_type')==='Recycling + Cardboard')>Recycling + Cardboard</option>
                        <option value="Garden Waste" @selected(old('bin_type')==='Garden Waste')>Garden Waste</option>
                    </select>
                </label>
            </div>
            <div class="grid">
                <label>
                    Select a date (next 14 days)
                    <select name="start_date">
                        <option value="">-- Choose a date --</option>
                        @for ($i = 0; $i < 14; $i++)
                            @php $d = now()->startOfDay()->addDays($i); @endphp
                            <option value="{{ $d->toDateString() }}" @selected(old('start_date')===$d->toDateString())>
                                {{ $d->format('D j M Y') }}
                            </option>
                        @endfor
                    </select>
                </label>
                <label>
                    <input type="checkbox" name="recurs_biweekly" value="1" @checked(old('recurs_biweekly'))>
                    Repeat every 2 weeks on the same weekday
                </label>
            </div>
            <label>
                <input type="checkbox" name="put_out" value="1" @checked(old('put_out'))>
                Put bin out reminder
            </label>
            <label>
                <input type="checkbox" name="bring_back" value="1" @checked(old('bring_back'))>
                Bring bin back reminder
            </label>
        </fieldset>

        <button type="submit">Save</button>
        <a href="{{ route('bins.index') }}" role="button" class="secondary">Cancel</a>
        <a href="{{ route('areas.index') }}" role="button" class="secondary">Manage allowed areas</a>
        <a href="{{ route('enquiry.create') }}" role="button" class="secondary">Service area enquiry</a>
    </form>
</main>

</body>
</html>


