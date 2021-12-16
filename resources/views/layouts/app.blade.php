
<!doctype html>
<html lang="en"  style="scroll-behavior: smooth;">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Donate Now </title>
    <link rel="shortcut icon" href="{{asset('img/icons/favicon1.png')}}">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css?family=Merriweather&display=swap" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="{{asset('css/Styles/style.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{asset('css/Styles/ss.css')}}" />

</head>
<body>
<div class="header">
    <img src="{{asset('img/3lives.jpg')}}" width="1535px" height="400 px">
</div>
<div class="nav">
    <ul>
        <li><a href="{{ route('logout') }}" onclick="event.preventDefault();

                                                     document.getElementById('logout-form').submit();">LogOut</a>

            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </li>
        <li><a href="#">Admin LogIn</a></li>
        <li><a href="#">Contact Us</a></li>
        <li><a href="#">History</a></li>
        <li><a class="active" href="#">Home</a></li>
    </ul>
</div>
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



