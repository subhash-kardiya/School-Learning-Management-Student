<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app-toast.css') }}">
    <!-- fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">
</head>

<body
    data-flash-success="{{ session('success') }}"
    data-flash-error="{{ session('error') }}"
    data-flash-warning="{{ session('warning') }}"
    data-flash-info="{{ session('info') }}"
    data-flash-duration="4000">

    <div class="container">
        <div class="row">
            <div
                class="col-lg-6 vh-responsive-50 d-flex flex-column justify-content-center align-items-center left-side">
                <h2 class=" fw-bold text-center text-white w-100 fs-1">School LMS</h2>
                <p class="text-center text-white mb-lg-5">Bridging the Gap Between Classroom and Home.</p>
                <img src="{{ asset('assets/undraw_education_3vwh.svg') }}" alt="Login Illustration" class="img-fluid"
                    style="max-height: 250px;">
            </div>
            <div
                class="col-lg-6 vh-responsive-50 bg-light d-flex flex-column justify-content-center align-items-center">

                <h2 class="text-center mb-4 forgot-txt">Welcome to LMS</h2>

                <form class="w-75" method="POST" action="{{ route('login.post') }}">
                    @csrf

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 rounded-3">

                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                                <strong>Validation Error!</strong>
                            </div>

                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li class="small">{{ $error }}</li>
                                @endforeach
                            </ul>

                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif



                    <div class="mb-3">
                        <label for="loginInput" class="form-label forgot-txt">Username / Mobile / Email</label>
                        <input type="text" name="login" class="form-control @error('login') is-invalid @enderror"
                            id="loginInput" placeholder="Enter username, mobile number, or email"
                            value="{{ old('login') }}">
                        @error('login')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label for="exampleInputPassword1" class="form-label forgot-txt">Password</label>
                        <input type="password" name="password"
                            class="form-control @error('password') is-invalid @enderror" id="exampleInputPassword1"
                            placeholder="Password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <a href="{{ route('forgot.password') }}"
                        class="forgot-link link-underline-light d-block mb-3 forgot-txt">Forgot password?</a>
                    <button type="submit" class="btn w-100 fs-6">Submit</button>
                </form>

            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous">
    </script>
    <script src="{{ asset('js/app-toast.js') }}"></script>
</body>

</html>
