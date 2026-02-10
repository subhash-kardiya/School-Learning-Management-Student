<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - School LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">
</head>

<body>
    <div class="container">
        <div class="row">

            <div
                class="col-lg-6 vh-responsive-50 d-flex flex-column justify-content-center align-items-center left-side">
                <h2 class="fw-bold text-center text-white w-100 fs-1">Forgot Password?</h2>
                <p class="text-center text-white mb-lg-5">Don't worry! Enter your email to reset it.</p>
                <img src="{{ asset('assets/undraw_education_3vwh.svg') }}" alt="Forgot Password" class="img-fluid"
                    style="max-height: 250px;">
            </div>

            <div
                class="col-lg-6 vh-responsive-50 bg-light d-flex flex-column justify-content-center align-items-center">

                <h2 class="text-center mb-4 forgot-txt">Reset Password</h2>
                @if (session('success'))
                    <div class="alert alert-success w-75">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger w-75">{{ session('error') }}</div>
                @endif
                <form method="POST" class="w-75" action="{{ route('forgot.password.post') }}">
                    @csrf


                    <div class="mb-4">
                        <label for="resetEmail" class="form-label forgot-txt">Email address</label>
                        <input type="email" name="email" class="form-control" id="resetEmail"
                            placeholder="Enter your registered email" required>
                    </div>

                    <button type="submit" class="btn w-100 fs-6 mb-3">Send OTP</button>
                    <div class="text-center">
                        <a href="{{ route('auth.login') }}" class="forgot-link forgot-txt text-decoration-none">
                            <i class="fas fa-arrow-left me-2"></i>Back to Login
                        </a>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>
</body>

</html>
