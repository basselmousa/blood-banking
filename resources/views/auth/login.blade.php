<!DOCTYPE html>
<html lang="en">
<head>
    <title>SignUp/Login</title>
    <meta charset="UTF-8">
    <!--===============================================================================================-->
    <link rel="stylesheet" type="text/css" href="{{asset('css/Styles/login_signup.css')}}">
    <link rel="shortcut icon" href="{{asset('img/icons/favicon1.png')}}">

    <!--===============================================================================================-->
</head>
<body>
<div class="container" id="container">
    <div class="form-container sign-up-container">
        <form method="POST" action="{{ route('register') }}">
            @csrf
            <h1>Create Account</h1>
            <input type="text" name="full_name" value="{{ old('full_name') }}"
                   class="@error('full_name') is-invalid @enderror" placeholder="Full Name" />
            @error('full_name')
            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
            @enderror
            <input type="text" name="username" value="{{ old('username') }}"
                   class="@error('username') is-invalid @enderror" placeholder="Username" />
            @error('username')
            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
            @enderror
            <input type="email" name="signup_email" value="{{ old('signup_email') }}"
                   class="@error('signup_email') is-invalid @enderror" placeholder="Email" />
            @error('signup_email')
            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
            @enderror
            <input type="text" name="phone_number" value="{{ old('phone_number') }}"
                   class="@error('phone_number') is-invalid @enderror" placeholder="Phone Number" />
            @error('phone_number')
            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
            @enderror
            <input type="password" name="signup_password"
                   class="@error('signup_password') is-invalid @enderror" placeholder="Password" />
            @error('signup_password')
            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
            @enderror
            <input type="password" name="signup_password_confirmation" placeholder="Confirm Password" />

            <button type="submit">Sign Up</button>
        </form>
    </div>
    <div class="form-container sign-in-container">
        <form method="post" action="{{ route('login') }}">
            @csrf
            <h1>Log In</h1>
            <input type="text" name="email" value="{{ old('email') }}" class="@error('email') is-invalid @enderror" placeholder="Username" />
            @error('email')
            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
            @enderror
            <input type="password" name="password" class="@error('password') is-invalid @enderror" placeholder="Password" />
            @error('password')
            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
            @enderror
            <a href="{{ route('password.request') }}">Forgot your password?</a>
            <br>
            <a href="{{ route('admin.login') }}">Are you admin?</a>
            <button type="submit">Log In</button>
        </form>
    </div>
    <div class="overlay-container">
        <div class="overlay">
            <div class="overlay-panel overlay-left">
                <h1>Welcome Back!</h1>
                <p>To keep connected with us please login with your personal info</p>
                <button class="ghost" id="signIn">Log In</button>
            </div>
            <div class="overlay-panel overlay-right">
                <h1>Hello, Friend!</h1>
                <p>Enter your personal details and start journey with us</p>
                <button class="ghost" id="signUp">Sign Up</button>
            </div>
        </div>
    </div>
</div>

<!--===============================================================================================-->

<script>
    const signUpButton = document.getElementById('signUp');
    const signInButton = document.getElementById('signIn');
    const container = document.getElementById('container');

    signUpButton.addEventListener('click', () => {
        container.classList.add("right-panel-active");
    });

    signInButton.addEventListener('click', () => {
        container.classList.remove("right-panel-active");
    });
</script>
</body>
</html>
