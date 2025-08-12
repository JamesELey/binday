<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css" rel="stylesheet">
    <style> body{max-width:520px;margin:3rem auto;} </style>
</head>
<body>
<h3>Register</h3>
@if ($errors->any())
    <article role="alert">
        <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
        </ul>
    </article>
@endif
<form method="POST" action="{{ route('register.post') }}">
    @csrf
    <label>Name
        <input type="text" name="name" value="{{ old('name') }}" required>
    </label>
    <label>Email
        <input type="email" name="email" value="{{ old('email') }}" required>
    </label>
    <label>Password
        <input type="password" name="password" required>
    </label>
    <label>Confirm Password
        <input type="password" name="password_confirmation" required>
    </label>
    <button type="submit">Create account</button>
    <p><a href="{{ route('login') }}">Back to login</a></p>
</form>
</body>
</html>


