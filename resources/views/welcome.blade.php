<!doctype html>
<html lang="en"  style="scroll-behavior: smooth;">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Life Share (LSBB)</title>
    <link rel="shortcut icon" href="{{asset('img/icons/favicon1.png')}}" style="width: 10px; height: 16px;">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Merriweather&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/Styles/style.css') }}" />
</head>

<body>
<!-- Navbar section -->

<div class="navbar">
    <img src="{{ asset('img/logo2.png') }}" class="logo">
    <nav>
        <ul>
            <li><a href="" class="active">HOME</a></li>
            <li><a href="#section1">ABOUT US</a></li>
            <li><a href="#section2">DONATION PROCESS</a></li>
            <li><a href="#section4">OUR GALLERY</a></li>
            <li><a href="#section5">CONTACT US</a></li>
            <li>
                <button class="btn donate"> <a style="color: white;" href="{{ route('login') }}">DONATE NOW</a></button>
            </li>
        </ul>
    </nav>
</div>

<br>
<div  class="container" id="section">
    <div class="row">
        <div class="col">
            <p class="up">Welcome to our website</p>
            <h2>Donate Your<span style="color: #d6382d;"> Blood</span> <br> To Us, Save More <br> Life <span style="color: #03a4ed;">Together</span></h2>
            <br>
            <button class="btn donate"> <a style="color: white; text-decoration: none;" href="{{ route('login') }}" target="_blank">DONATE NOW</a></button>

        </div>

        <div class="col">
            <img class="image" src="{{ asset('img/15.jpg') }}" alt="">
        </div>

    </div>
</div>


<br>
<br>
<!-- About us-->
<section class="container" id="section1" style="background-image: url({{ asset('img/about.png') }});">
    <p style="font-size: 44px; text-align: center; color: #000;" > About Us</p>
    <div class="row">
        <div class="col">
            <img class="aboutus" src="{{ asset('img/23.jpg') }}">
        </div>

        <div class="col">
            <p style="font-size: 30px;">We solve the problem of <span style="color: #d6382d;">blood</span> emergencies by connecting <span style="color: #d6382d;">blood</span> donors directly with people in <span style="color: #d6382d;">blood</span> need. </p>
            <br>
            <p style="font-size: 30px;">We connect <span style="color: #d6382d;">blood</span> donors with recipients, without any intermediary such as <span style="color: #d6382d;">blood</span> banks, for an efficient and seamless process.</p>
        </div>

    </div>


</section>


<!-- Donation Process-->

<section id="section2" >
    <p style="font-size: 44px; text-align: center; color: #000;" >Donation Process</p>
    <br>
    <br>
    <!-- first pic-->
    <div class="grid">
        <div class="grid-item">
            <div class="card">
                <img class="card-img" src="{{ asset('img/12.jfif') }}">

                <div class="card-content">
                    <h1 class="card-header">1. REGESTRATION</h1>

                    <p class="card-text">


                    </p>

                </div>
            </div>
        </div>

        <!-- Second pic-->

        <div class="grid-item">
            <div class="card">
                <img class="card-img" src="{{ asset('img/25.jpg') }}">

                <div class="card-content">
                    <h1 class="card-header">2. SEEING</h1>

                    <p class="card-text">


                    </p>

                </div>
            </div>
        </div>

        <!-- Third pic-->

        <div class="grid-item">
            <div class="card">
                <img class="card-img" src="{{asset('img/3.jpg')}}">

                <div class="card-content">
                    <h1 class="card-header">3. DONATION</h1>

                    <p class="card-text">


                    </p>

                </div>
            </div>
        </div>

        <!-- fourth pic-->

        <div class="grid-item">
            <div class="card">
                <img class="card-img" src="{{ asset('img/7.jpg') }}">

                <div class="card-content">
                    <h1 class="card-header">4. SAVE LIFE</h1>

                    <p class="card-text">


                    </p>

                </div>
            </div>
        </div>

    </div>

</section>
<br>
<br>
<!--image slider start-->
<p style="font-size: 44px; text-align: center; color: #000;" id="section4" >Our Gallery</p>
<br>


<!--FOOTER-->
<footer class="footer-distributed" id="section5">

    <div class="footer-left">

        <h3><img src="{{ asset('img/logo2.png') }}" width="250px" height="120px"></h3>

        <p class="footer-links">
            <a href="#section" class="link-1">Home</a>

            <a href="#section1">About us</a>

            <a href="#section2">Donation Proces</a>

            <a href="#section4">Our Gallery</a>

            <a href="#section5">Contact Us</a>
        </p>

        <p class="footer-company-name">Blood Donation © 2021</p>
    </div>

    <div class="footer-center">

        <div>
            <i class="fa fa-map-marker"></i>
            <p>Jordan, Amman</p>
        </div>

        <div>
            <i class="fa fa-phone"></i>
            <p>+962 796775015</p>
        </div>

        <div>
            <i class="fa fa-envelope"></i>
            <p><a href="mailto:rara1999_sh@hotmail.com">rara1999_sh@hotmail.com</a></p>
        </div>

        <div class="footer-icons">

            <a href="#"><i class="fa fa-facebook"></i></a>
            <a href="#"><i class="fa fa-twitter"></i></a>
            <a href="#"><i class="fa fa-instagram"></i></a>
            <a href="#"><i class="fa fa-whatsapp"></i></a>

        </div>
    </div>

    <div class="footer-right">

        <p style="font-size: 30px; text-align: center; font-family: sans-serif;">Contact Us</p>
        <br>
        <div class="containeeer">
            <form action="">
                <label for="fname">First Name</label>
                <input type="text" id="fname" name="firstname" placeholder="Your name..">

                <label for="lname">Last Name</label>
                <input type="text" id="lname" name="lastname" placeholder="Your last name..">

                <label for="lname">Your Email</label>
                <input type="text" id="email" name="email" placeholder="Your Email..">

                <label for="subject">Message</label>
                <textarea id="Message" name="Message" placeholder="Write something.." style="height:100px"></textarea>

                <div style="text-align: center;"> <input type="submit" value="Submit"> </div>
            </form>
        </div>

    </div>

</footer>



<script src="{{asset('js/donor_app.js')}}"></script>

</body>
</html>
