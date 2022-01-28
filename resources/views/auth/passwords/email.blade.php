<!doctype html>
<html lang="en" style="scroll-behavior: smooth;">

<head>
    <meta charset="utf-8">
    <title>Forget Password</title>
    <link rel="shortcut icon" href="{{asset('img/wrong-password (1).png')}}">


    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Nunito+Sans:300i,400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">

    <link rel="stylesheet" type="text/css" href="{{asset('css/Styles/new.css')}}"/>
</head>

<body>

    @if (session('status'))

        <div id="popup1" class="overlay">
            <div class="popup">
                <img src="{{asset('img/tick.png')}}" style="align-items: center;" width="30%" height="30%">
                <a class="close" href="#">&times;</a>
                <div class="content">
                    Check Your Email Please.
                </div>
            </div>
        </div>
    @endif

    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="bg-white text-center p-5 mt-3 center">
            <img src="{{asset('img/wrong-password (1).png')}}" width="40%" height="40%">
            <h3>Forgot Password </h3>
            <p>Enter your registered email below to receive password rest instruction.</p>
            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="form-group">
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                           name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                    @error('email')
                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                    @enderror
                </div>


                <div class="row mb-0">
                    <div class="col-md-6 offset-md-4">
                        <button type="submit" class="btn btn-primary">
                            {{ __('Send Password Reset Link') }}
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</body>
</html>
