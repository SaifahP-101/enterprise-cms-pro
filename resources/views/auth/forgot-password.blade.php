<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ลืมรหัสผ่าน | Enterprise CMS Pro</title>
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .auth-card { width: 100%; max-width: 420px; border: none; border-radius: 12px; }
    </style>
</head>
<body>

<div class="card auth-card shadow-sm">
    <div class="card-body p-4">
        <div class="mb-4">
            <h1 class="h5 fw-bold text-dark">กู้คืนรหัสผ่าน</h1>
            <p class="text-muted small">ระบุอีเมลของคุณที่ใช้ในระบบ เพื่อให้ระบบส่งลิงก์การตั้งค่ารหัสผ่านใหม่ไปให้ทางกล่องข้อความ</p>
        </div>

        @if (session('status'))
            <div class="alert alert-success border-0 small py-2 mb-3">
                {{ session('status') }}
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="email" class="form-label text-secondary small fw-semibold">อีเมลระบุบัญชี</label>
                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus>
                @error('email')
                    <div class="invalid-feedback small">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4">
                <a href="{{ route('login') }}" class="text-secondary small text-decoration-none">← กลับไปหน้าล็อกอิน</a>
                <button type="submit" class="btn btn-success btn-sm px-4 fw-semibold py-2">ส่งลิงก์กู้คืนรหัส</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>