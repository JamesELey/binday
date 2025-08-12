<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Area Enquiry</title>
    <link href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css" rel="stylesheet">
    <style> body{max-width:800px;margin:2rem auto;} </style>
</head>
<body>
<header>
    <h2>Service Area Enquiry</h2>
    <p><a href="{{ route('bins.index') }}">Back to home</a></p>
</header>

<main>
    @if (session('status'))
        <article role="alert">{{ session('status') }}</article>
    @endif
    @if ($errors->any())
        <article role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </article>
    @endif

    <form method="POST" action="{{ route('enquiry.store') }}">
        @csrf
        <div class="grid">
            <label>Name
                <input type="text" name="name" value="{{ old('name') }}" required>
            </label>
            <label>Email
                <input type="email" name="email" value="{{ old('email') }}" required>
            </label>
        </div>
        <div class="grid">
            <label>Phone
                <input type="text" name="phone" value="{{ old('phone') }}">
            </label>
            <label>Address
                <input type="text" name="address" value="{{ old('address') }}" required>
            </label>
        </div>
        <label>Message (optional)
            <textarea name="message" rows="4">{{ old('message') }}</textarea>
        </label>
        <button type="submit">Send enquiry</button>
    </form>
</main>

</body>
</html>


