<!doctype html>
<html lang="en"  style="scroll-behavior: smooth;">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Admin Page</title>
    <link rel="shortcut icon" href="{{asset('img/admin.png')}}">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css?family=Merriweather&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>


    <link rel="stylesheet" type="text/css" href="{{asset('css/Styles/sstt.css')}}" />

</head>
<body>

<nav class="navbar navbar-default" >
    <div class="container-fluid">
        <div class="navbar-header">
            <a class="navbar-brand" href="#"><img src="{{asset('img/logo2.png')}}" width="90px" height="90px" style="margin-top: -14px;"></a>
        </div>
        <ul class="nav navbar-nav" style="height: 80px; margin-top: 20px; float: right; font-size: 20px;">
            @if(! request()->routeIs('admin.home'))
            <li><a href="{{ route('admin.home') }}" onMouseOver="this.style.color='red'"
                   onMouseOut="this.style.color='rgb(114, 114, 114)'">Home</a></li>
            @endif
        <li><a href="{{ route('admin.create-admin') }}" onMouseOver="this.style.color='red'"
                   onMouseOut="this.style.color='rgb(114, 114, 114)'">Create Admin</a></li>
            <li><a href="#" onMouseOver="this.style.color='red'"
                   onMouseOut="this.style.color='rgb(114, 114, 114)'"
                   onclick="event.preventDefault();
                    document.getElementById('logout').submit();
                    " >Logout</a></li>
                <form id="logout" action="{{ route('admin.logout') }}" method="post" style="display: none">
                    @csrf

                </form>
        </ul>
    </div>
</nav>
<br>
<br>

<br>
<br>

@yield('adminContent')


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
