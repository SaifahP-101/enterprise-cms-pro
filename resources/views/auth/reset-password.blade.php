<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตั้งรหัสผ่านใหม่ | Enterprise CMS Pro</title>
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
            <h1 class="h5 fw-bold text-dark">กำหนดรหัสผ่านใหม่</h1>
            <p class="text-muted small">โปรดระบุรหัสผ่านใหม่ระดับปลอดภัยสูงสำหรับบัญชีของคุณ</p>
        </div>

        <form action="{{ route('password.update') }}" method="POST">
            @csrf
            <!-- ป้อน Security Token แฝงส่งกลับไปยืนยัน -->
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="mb-3">
                <label for="email" class="form-label text-secondary small fw-semibold">อีเมลยืนยัน</label>
                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ $email ?? old('email') }}" required>
                @error('email')
                    <div class="invalid-feedback small">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label text-secondary small fw-semibold">รหัสผ่านใหม่</label>
                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required>
                @error('password')
                    <div class="invalid-feedback small">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label text-secondary small fw-semibold">ยืนยันรหัสผ่านใหม่อีกครั้ง</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-success w-100 fw-semibold py-2 mt-2">อัปเดตรหัสผ่านใหม่</button>
        </form>
    </div>
</div>

</body>
</html>