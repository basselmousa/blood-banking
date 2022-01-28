<!doctype html>
<html lang="en"  style="scroll-behavior: smooth;">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Donate Now </title>
    <link rel="shortcut icon" href="Img/icons/favicon1.png">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css?family=Merriweather&display=swap" rel="stylesheet">



    @if(request()->routeIs('search') || request()->routeIs('search.post'))
        <link rel="stylesheet" type="text/css" href="{{asset('css/Styles/sstt.css')}}" />
        <link rel="shortcut icon" href="{{asset('img/Search.png')}}">

        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>

    @else
        <link rel="stylesheet" type="text/css" href="{{asset('css/Styles/style.css')}}" />
        <link rel="stylesheet" type="text/css" href="{{asset('css/Styles/ss.css')}}" />


    @endif

</head>
<body>
@if(request()->routeIs('search') || request()->routeIs('search.post'))
    <nav class="navbar navbar-default" >
        <div class="container-fluid">
            <div class="navbar-header">
                <a class="navbar-brand" href="#"><img src="{{asset('img/logo2.png')}}" width="90px" height="90px" style="margin-top: -14px;"></a>
            </div>
            <ul class="nav navbar-nav" style="height: 80px; margin-top: 20px; float: right; font-size: 20px;" >
                <li><a href="{{ route('home') }}" onMouseOver="this.style.color='red'"
                       onMouseOut="this.style.color='rgb(114, 114, 114)'">Home</a></li>
                <li><a href="{{ route('personal-information') }}" onMouseOver="this.style.color='red'"
                       onMouseOut="this.style.color='rgb(114, 114, 114)'">Personal Info</a></li>
                <li><a href="{{route('welcome')}}#section5" onMouseOver="this.style.color='red'"
                       onMouseOut="this.style.color='rgb(114, 114, 114)'">Contact Us</a></li>

                <li><a href="#" onMouseOver="this.style.color='red'"
                       onMouseOut="this.style.color='rgb(114, 114, 114)'"
                    onclick="event.preventDefault();
                    document.getElementById('logout').submit();
                    "
                    >Log Out</a></li>
                <form id="logout" action="{{ route('logout') }}" method="post" class="d-none">
                @csrf

                </form>
            </ul>
        </div>
    </nav>
@else
    <div class="nav">
        <div class="navbar-header">
            <a class="navbar-brand" href="#"><img src="{{asset('img/logo2.png')}}" width="90px" height="90px" style="margin-top: -14px;"></a>
        </div>
        <ul style="margin-left: 33%; margin-top: 1%; ">
            <li><a href="#" onclick="event.preventDefault();
                    document.getElementById('logout').submit();
                    " >Logout</a></li>
            <form id="logout" action="{{ route('logout') }}" method="post" style="display: none">
                @csrf

            </form>
            <li><a href="{{ route('welcome')}}#section5">Contact Us</a></li>
            <li><a href="{{ route('personal-information') }}">Personal Info</a></li>
            <li><a href="{{ route('home') }}" >Home</a></li>
        </ul>
    </div>
@endif



<br>
<br>

@yield('homeContent')

<script>
    let modalBtns = [...document.querySelectorAll(".button")];
    modalBtns.forEach(function(btn) {
        btn.onclick = function() {
            let modal = btn.getAttribute('data-modal');
            document.getElementById(modal)
                .style.display = "block";
        }
    });
    let closeBtns = [...document.querySelectorAll(".close")];
    closeBtns.forEach(function(btn) {
        btn.onclick = function() {
            let modal = btn.closest('.modal');
            modal.style.display = "none";
        }
    });
    window.onclick = function(event) {
        if(event.target.className === "modal") {
            event.target.style.display = "none";
        }
    }

</script>

</body>
</html>



