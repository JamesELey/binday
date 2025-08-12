<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Bin Schedule</title>
    <link href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css" rel="stylesheet">
    <style> body{max-width:800px;margin:2rem auto;} </style>
</head>
<body>
<header>
    <h3>Edit Bin Schedule</h3>
    <p><a href="{{ route('bins.index') }}">Back to list</a></p>
</header>

<main>
    @if ($errors->any())
        <article role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </article>
    @endif

    <form method="POST" action="{{ route('bins.update', $bin) }}">
        @csrf
        @method('PUT')
        <fieldset>
            <legend>Address</legend>
            <label>Postcode
                <input name="postcode" value="{{ old('postcode', $bin->postcode) }}" required>
            </label>
            <label>Address
                <input name="address" value="{{ old('address', $bin->address) }}" required>
            </label>
        </fieldset>

        <fieldset>
            <legend>Details</legend>
            <label>Bin type
                <select name="bin_type" required>
                    @foreach(['Residual Waste','Recycling + Cardboard','Garden Waste'] as $bt)
                        <option value="{{ $bt }}" @selected(old('bin_type', $bin->bin_type)===$bt)>{{ $bt }}</option>
                    @endforeach
                </select>
            </label>
            <div class="grid">
                <label>Start date
                    <input type="date" name="start_date" value="{{ old('start_date', $bin->start_date) }}">
                </label>
                <label>
                    <input type="checkbox" name="recurs_biweekly" value="1" @checked(old('recurs_biweekly', $bin->recurs_biweekly))>
                    Repeat every 2 weeks
                </label>
            </div>
            <label>
                <input type="checkbox" name="put_out" value="1" @checked(old('put_out', $bin->put_out))> Put out
            </label>
            <label>
                <input type="checkbox" name="bring_back" value="1" @checked(old('bring_back', $bin->bring_back))> Bring back
            </label>
        </fieldset>

        <button type="submit">Save changes</button>
    </form>
</main>

</body>
</html>


