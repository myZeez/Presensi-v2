<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Karyawan - Presensi</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 0; font-family: 'Roboto', sans-serif; background: #e8eaed; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .login-card { background: white; padding: 40px 30px; border-radius: 16px; box-shadow: 0 8px 24px rgba(0,0,0,0.08); width: 100%; max-width: 360px; text-align: center; }
        .icon { font-size: 3rem; color: #1a73e8; margin-bottom: 20px; }
        h1 { color: #3c4043; margin-bottom: 10px; font-size: 1.5rem; font-weight: 700; }
        p { color: #5f6368; font-size: 0.95rem; margin-bottom: 30px; line-height: 1.4; }
        .form-group { text-align: left; margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #3c4043; font-size: 0.9rem;}
        .form-group input { width: 100%; padding: 12px 14px; border: 2px solid #e1e4e8; border-radius: 8px; font-size: 1rem; transition: 0.2s; outline: none; }
        .form-group input:focus { border-color: #1a73e8; }
        .btn { width: 100%; padding: 14px; background: #1a73e8; color: white; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: 0.2s; box-shadow: 0 4px 6px rgba(26, 115, 232, 0.2); }
        .btn:hover { background: #1557b0; box-shadow: 0 6px 10px rgba(26, 115, 232, 0.3); }
        .alert { background: #fce8e6; color: #d93025; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; text-align: left; border: 1px solid #fad2cf; }
    </style>
</head>
<body>
    <div class="login-card">
        <!-- Optional: Use phosphor icons if connected, or purely text -->
        <div style="width: 60px; height: 60px; background: #e8f0fe; color: #1a73e8; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 24px; font-weight: bold;">
            🧑‍💼
        </div>
        <h1>Aplikasi Presensi</h1>
        <p>Silakan login untuk mencatat presensi harian Anda. Login Anda akan diingat selamanya oleh perangkat ini.</p>

        @if($errors->any())
            <div class="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('employee.login.post') }}">
            @csrf
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Masukkan username Anda">
            </div>
            <div class="form-group">
                <label>Password (PIN)</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>
            <button type="submit" class="btn">Masuk Aplikasi</button>
        </form>
    </div>
</body>
</html>
