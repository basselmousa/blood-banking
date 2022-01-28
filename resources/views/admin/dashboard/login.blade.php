<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Login</title>
    <meta charset="UTF-8">
    <!--===============================================================================================-->
    <link rel="shortcut icon" href="Img/new.png">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/simple-line-icons/2.4.1/css/simple-line-icons.min.css" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="{{ asset('css/Styles/sstt.css') }}" />


    <!--===============================================================================================-->
</head>
<body>
<h1 style="font-size: 60px; text-align: center; font-weight:300; font-family:'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif">Admin Login</h1>


<div class="registration-form">
    <form method="post" action="{{ route('admin.login.post') }}">
        @csrf
        <img class="newww" src="{{asset('img/admin.png')}}" alt="">
        <br>
        <br>

        <div class="form-group">
            <input type="text" class="form-control item @error('email') is-invalid @enderror"  name="email" id="email" placeholder="Email">
            @error('email')
            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
            @enderror
        </div>

        <div class="form-group">
            <input type="password" class="form-control item @error('password') is-invalid @enderror" name="password" id="password" placeholder="Password">
            @error('password')
            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
            @enderror
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-block create-account">Log In</button>
        </div>
    </form>
</div>

<script type="text/javascript" src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.15/jquery.mask.min.js"></script>
<script src="{{ asset('js/donor_app.js') }}"></script>

</body>
</html>
