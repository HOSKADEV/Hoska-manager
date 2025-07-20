<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Login - Project Management</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />

    <style>
        body {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-card {
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            max-width: 450px;
            width: 100%;
            padding: 2rem 2.5rem;
            text-align: center;
        }

        .login-card h1.title {
            font-weight: 700;
            color: #224abe;
            margin-bottom: 0.25rem;
            font-size: 2rem;
            letter-spacing: 1.5px;
        }

        .login-card p.subtitle {
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 2rem;
            font-size: 1rem;
            letter-spacing: 1px;
        }

        .login-card .form-control {
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            font-size: 1rem;
        }

        .login-card .btn-primary {
            border-radius: 0.5rem;
            padding: 0.75rem;
            font-weight: 600;
            font-size: 1.1rem;
            letter-spacing: 0.03em;
        }

        .form-check-label {
            user-select: none;
        }

        a.small {
            color: #224abe;
            text-decoration: none;
            font-weight: 600;
        }

        a.small:hover {
            text-decoration: underline;
        }
    </style>

</head>

<body>

    <div class="login-card shadow-sm">

        <!-- Title & Subtitle -->
        <h1 class="title">Project Management</h1>
        <p class="subtitle">Hoska Dev Company</p>

        <form action="{{ route('signin') }}" method="post" novalidate>
            @csrf
            <div class="mb-3 text-start">
                <label for="email" class="form-label fw-semibold">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                    class="form-control @error('email') is-invalid @enderror" placeholder="Enter your email" required
                    autofocus />
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3 text-start">
                <label for="password" class="form-label fw-semibold">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password"
                    class="form-control @error('password') is-invalid @enderror" required />
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input type="checkbox" id="remember" name="remember" class="form-check-input" />
                    <label for="remember" class="form-check-label">Remember Me</label>
                </div>
                <a href="forgot-password.html" class="small">Forgot Password?</a>
            </div>

            <button type="submit" class="btn btn-primary w-100">Sign In</button>

        </form>

        <div class="text-center mt-4">
            <a href="{{ route('register') }}" class="small">Create an Account!</a>
        </div>

    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>

{{-- <!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Login</title>

    <!-- Custom fonts for this template-->
    <link href="{{ asset('assets/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">

    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ asset('assets/css/sb-admin-2.min.css') }}" rel="stylesheet">

</head>

<body class="bg-gradient-primary">

    @if (session('verify'))
    <div class="info">{{ session('verify') }}</div>
    @endif

    @if (session('success'))
    <div class="sucsess">{{ session('success') }}</div>
    @endif

    <div class="container d-flex justify-content-center align-items-center min-vh-100">

        <!-- Outer Row -->
        <div class="row justify-content-center w-100">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <div class="card o-hidden border-0 shadow-lg">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-login-image"
                                style="background-image: url({{ asset('assets/img/bg.jpg')}})"></div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                                    </div>
                                    <form action="{{ route('signin') }}" method="post">
                                        @csrf
                                        <div class="input-group mb-3">
                                            <input type="email" class="form-control  @error('email') is-invalid @enderror"
                                                placeholder="Email" name="email" value="{{ old('email') }}">
                                            @error('email')
                                            <small class="invalid-feedback">{{ $message }}</small>
                                            @enderror
                                            <div class="input-group-append">
                                                <div class="input-group-text">
                                                    <span class="fas fa-envelope"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="input-group mb-3">
                                            <input type="password" class="form-control  @error('Password') is-invalid @enderror"
                                                placeholder="password" name="password" value="{{ old('password') }}">
                                            @error('password')
                                            <small class="invalid-feedback">{{ $message }}</small>
                                            @enderror
                                            <div class="input-group-append">
                                                <div class="input-group-text">
                                                    <span class="fas fa-lock"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-8">
                                                <div class="icheck-primary">
                                                    <input type="checkbox" id="remember" name="remember">
                                                    <label for="remember">
                                                        Remember Me
                                                    </label>
                                                </div>
                                            </div>
                                            <!-- /.col -->
                                            <div class="col-4">
                                                <button type="submit" class="btn btn-primary btn-block">Sign In</button>
                                            </div>
                                            <!-- /.col -->
                                        </div>
                                    </form>
                                    <hr>
                                    <div class="text-center">
                                        <a class="small" href="forgot-password.html">Forgot Password?</a>
                                    </div>
                                    <div class="text-center">
                                        <a class="small" href="{{ route('register') }}">Create an Account!</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Bootstrap core JavaScript-->
    <script src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Core plugin JavaScript-->
    <script src="{{ asset('assets/vendor/jquery-easing/jquery.easing.min.js') }}"></script>

    <!-- Custom scripts for all pages-->
    <script src="{{ asset('assets/js/sb-admin-2.min.js') }}"></script>

</body>

</html> --}}

