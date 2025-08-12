<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css" rel="stylesheet">
    <style> body{max-width:480px;margin:3rem auto;} </style>
</head>
<body>
<h3>Login</h3>
@if ($errors->any())
    <article role="alert">
        <ul>
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
        </ul>
    </article>
@endif
<form method="POST" action="{{ route('login.post') }}">
    @csrf
    <label>Email
        <input type="email" name="email" value="{{ old('email') }}" required>
    </label>
    <label>Password
        <input type="password" name="password" required>
    </label>
    <button type="submit">Login</button>
    <p><a href="{{ route('register') }}">Create an account</a></p>
</form>
</body>
</html>


