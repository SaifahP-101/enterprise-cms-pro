<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ | Enterprise CMS Pro</title>
    <!--  Local Assets Only -->
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .auth-card { width: 100%; max-width: 420px; border: none; border-radius: 12px; }
    </style>
</head>
<body>

<div class="card auth-card shadow-sm">
    <div class="card-body p-4">
        <div class="text-center mb-4">
            <h1 class="h4 fw-bold text-dark">Enterprise CMS Pro</h1>
            <p class="text-muted small">กรุณาลงชื่อเข้าใช้งานเพื่อการบริหารระบบ</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 small py-2">{{ session('success') }}</div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            <!--  Anti-CSRF Guard Token -->
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label text-secondary small fw-semibold">อีเมลผู้ใช้งาน</label>
                <input type="email" name="email" id="email" class="form-url form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus>
                @error('email')
                    <div class="invalid-feedback small">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="password" class="form-label text-secondary small fw-semibold mb-0">รหัสผ่าน</label>
                    <a href="{{ route('password.request') }}" class="text-success small text-decoration-none">ลืมรหัสผ่าน?</a>
                </div>
                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                @error('password')
                    <div class="invalid-feedback small">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 form-check">
                <input type="checkbox" name="remember" id="remember" class="form-check-input">
                <label class="form-check-label text-muted small" for="remember">จดจำฉันในระบบ</label>
            </div>

            <button type="submit" class="btn btn-success w-100 fw-semibold py-2">ลงชื่อเข้าใช้งาน</button>
        </form>
    </div>
</div>

<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>