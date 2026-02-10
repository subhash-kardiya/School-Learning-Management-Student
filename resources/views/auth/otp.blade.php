<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - School LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="row">
            <div
                class="col-lg-6 vh-responsive-50 d-flex flex-column justify-content-center align-items-center left-side">
                <h2 class="fw-bold text-center text-white w-100 fs-1">Verification</h2>
                <p class="text-center text-white mb-lg-5">We sent a code to your email.<br>Please check your inbox.</p>
                <img src="{{ asset('assets/undraw_education_3vwh.svg') }}" alt="OTP Illustration" class="img-fluid"
                    style="max-height: 250px;">
            </div>

            <div
                class="col-lg-6 vh-responsive-50 bg-light d-flex flex-column justify-content-center align-items-center">
                <h2 class="text-center mb-4 forgot-txt">Enter OTP</h2>

                @if (session('success'))
                    <div class="alert alert-success w-75">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger w-75">{{ session('error') }}</div>
                @endif
                <form class="w-75" method="POST" action="{{ route('verify.otp') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label forgot-txt">One Time Password</label>
                        <input type="text" name="otp" class="form-control text-center fs-4" maxlength="6"
                            style="letter-spacing: 10px;" required>
                        <div class="form-text text-end mt-2">
                            <a href="#" class="text-decoration-none small">Resend OTP?</a>
                        </div>
                    </div>
                    <button type="submit" class="btn w-100 fs-6">Verify</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>
</body>

</html>
