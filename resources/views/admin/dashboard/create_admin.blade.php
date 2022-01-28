<!doctype html>
<html lang="en"  style="scroll-behavior: smooth;">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Create Admin</title>
    <link rel="shortcut icon" href="{{asset('img/new.png')}}">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css?family=Merriweather&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/simple-line-icons/2.4.1/css/simple-line-icons.min.css" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="{{ asset('css/Styles/sstt.css') }}" />

</head>
<body>

<nav class="navbar navbar-default" >
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="#"><img src="{{asset('img/logo2.png')}}" width="90px" height="90px" style="margin-top: -14px;"></a>
        </div>
        <ul class="nav navbar-nav" style="height: 80px; margin-top: 20px; float: right; font-size: 20px;">
            <li><a href="{{ route('admin.home')}}" onMouseOver="this.style.color='red'"
                   onMouseOut="this.style.color='rgb(114, 114, 114)'">Home</a></li>
            <li><a href="#" onMouseOver="this.style.color='red'"
                   onMouseOut="this.style.color='rgb(114, 114, 114)'">Log Out</a></li>
        </ul>
    </div>
</nav>
<br>
<br>

<h1 style="font-size: 60px; text-align: center; font-weight:300; font-family:'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif">Create Admin</h1>


<div class="registration-form">
    <form action="{{ route('admin.create-admin-post') }}" method="post">
        @csrf


        <img class="newww" src="{{asset('img/new.png')}}" alt="">
        <br>
        <br>
        <div class="form-group">
            <input type="text" class="form-control item" name="full_name" id="fullname" placeholder="Full Name">
            @error('full_name')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>
        <div class="form-group">
            <input type="text" class="form-control item" name="username" id="username" placeholder="Username">
            @error('username')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>
        <div class="form-group">
            <input type="text" class="form-control item" name="email" id="email" placeholder="Email">
            @error('email')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>
        <div class="form-group">
            <input type="text" class="form-control item" name="phone_number" id="phone-number" placeholder="Phone Number">
            @error('phone_number')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>
        <div class="form-group">
            <input type="password" class="form-control item" name="password" id="password" placeholder="Password">
            @error('password')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>
        <div class="form-group">
            <input type="password" class="form-control item" name="password_confirmation" id="password" placeholder="Confirm Password">
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-block create-account">Create Admin</button>
        </div>
    </form>
</div>
<script type="text/javascript" src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.15/jquery.mask.min.js"></script>
<script src="{{ asset('js/donor_app.js') }}"></script>

</body>
</html>
