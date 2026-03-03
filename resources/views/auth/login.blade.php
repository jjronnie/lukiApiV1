<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Luki Admin Login</title>
    <style>
        body { font-family: sans-serif; background: #f3f4f6; display: grid; place-items: center; min-height: 100vh; }
        .box { background: #fff; padding: 24px; border-radius: 8px; width: 360px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        input { width: 100%; padding: 10px; margin: 6px 0 12px; border: 1px solid #cbd5e1; border-radius: 6px; }
        button { width: 100%; padding: 10px; border: none; background: #111827; color: #fff; border-radius: 6px; }
    </style>
</head>
<body>
<div class="box">
    <h2>Superadmin Login</h2>
    @if($errors->any())
        <div style="color:#dc2626; margin-bottom:10px;">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('login.store') }}">
        @csrf
        <label>Email</label>
        <input type="email" name="email" required value="{{ old('email') }}">
        <label>Password</label>
        <input type="password" name="password" required>
        <label><input type="checkbox" name="remember" value="1" style="width:auto;"> Remember me</label>
        <button type="submit">Login</button>
    </form>
</div>
</body>
</html>
