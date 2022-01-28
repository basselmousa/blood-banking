<!doctype html>
<html lang="en"  style="scroll-behavior: smooth;">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Life Share (LSBB)</title>
    <link rel="shortcut icon" href="Img/icons/favicon1.png" style="width: 10px; height: 16px;">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Merriweather&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="{{asset('css/Styles/style.css')}}" />

    <script src="https://kit.fontawesome.com/yourcode.js" crossorigin="anonymous"></script>

</head>

<body>
<!-- Navbar section -->
<button onclick="topFunction()" id="myBtn" title="Go to top"><i class="fa fa-angle-double-up" style="font-size: 40px;"></i> </button>


<div class="navbar">
    <img src="{{asset('img/logo2.png')}}" class="logo">
    <nav>
        <ul>
            <li><a href="">Home</a></li>
            <li><a href="#section1">About Us</a></li>
            <li><a href="#section2">Donation Process</a></li>
            <li><a href="#section4">Our Gallery</a></li>
            <li><a href="#section5">Contact Us</a></li>
            <li>
                <button class="btn donate"> <a style="color: white;" href="{{ route('login') }}" target="_blank">DONATE NOW</a></button>
            </li>
        </ul>
    </nav>
</div>

<br>
<div  class="container" id="section">
    <div class="row">
        <div class="col">
            <h2>Donate Your<span style="color: #d6382d;"> Blood</span> <br> To Us, Save More <br> Life <span style="color: #03a4ed;">Together</span></h2>
            <br>
            <button class="btn donate"> <a style="color: white; text-decoration: none;" href="{{ route('login') }}" target="_blank">DONATE NOW</a></button>

        </div>

        <div class="col">
            <img class="image" src="{{asset('img/15.jpg')}}" alt="">
        </div>

    </div>
</div>


<br>
<br>
<!-- About us-->
<section class="container" id="section1" style="background-image: url({{asset('img/contact-dec.png')}});">
    <p style="font-size: 44px; text-align: center; color: #000;" > About Us</p>
    <div class="row">
        <div class="col">
            <img class="aboutus" src="{{asset('img/23.jpg')}}">
        </div>

        <div class="col">
            <p style="font-size: 30px;">We solve the problem of <span style="color: #d6382d;">blood</span> emergencies by connecting <span style="color: #d6382d;">blood</span> donors directly with people in <span style="color: #d6382d;">blood</span> need. </p>
            <br>
            <p style="font-size: 30px;">We connect <span style="color: #d6382d;">blood</span> donors with recipients, without any intermediary such as <span style="color: #d6382d;">blood</span> banks, for an efficient and seamless process.</p>
        </div>

    </div>
    </div>

</section>


<!-- Donation Process-->

<section id="section2" style="background-image: url({{asset('img/21.png')}}); margin-left: 30px;">
    <p style="font-size: 44px; text-align: center; color: #000;" >Donation Process</p>
    <br>
    <br>
    <!-- first pic-->
    <div class="grid">
        <div class="grid-item">
            <div class="card">
                <img class="card-img" src="{{asset('img/12.jfif')}}">

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
                <img class="card-img" src="{{asset('img/25.jpg')}}">

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
                <img class="card-img" src="{{asset('img/7.jpg')}}">

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
<section style="background-image: url({{asset('img/21.png')}});">
    <p style="font-size: 44px; text-align: center; color: #000;" id="section4" >Our Gallery</p>
    <br>

    <div class="slideshow-container" style="text-align: center;">

        <!-- Full-width images with number and caption text -->
        <div class="mySlides fade">
            <div class="numbertext">1 / 8</div>
            <img src="{{asset('img/health1.jpg')}}" style="width:70%; height: 70%;">
        </div>

        <div class="mySlides fade">
            <div class="numbertext">2 / 8</div>
            <img src="{{asset('img/health2.jpg')}}" style="width:70%; height: 70%;">
        </div>

        <div class="mySlides fade">
            <div class="numbertext">3 / 8</div>
            <img src="{{asset('img/Health3.jpg')}}" style="width:70%; height: 70%;">
        </div>

        <div class="mySlides fade">
            <div class="numbertext">4 / 8</div>
            <img src="{{asset('img/why.jpg')}}" style="width:70%; height: 70%;">
        </div>

        <div class="mySlides fade">
            <div class="numbertext">5 / 8</div>
            <img src="{{asset('img/compatible.png')}}" style="width:70%; height: 70%;">
        </div>

        <div class="mySlides fade">
            <div class="numbertext">6 / 8</div>
            <img src="{{asset('img/why.jpg')}}" style="width:70%; height: 70%;">
        </div>

        <div class="mySlides fade">
            <div class="numbertext">7 / 8</div>
            <img src="{{asset('img/blog-benefits.jpg')}}" style="width:70%; height: 70%;">
        </div>

        <div class="mySlides fade">
            <div class="numbertext">8 / 8</div>
            <img src="{{asset('img/9.jpg')}}" style="width:70%; height: 70%;">
        </div>



        <!-- Next and previous buttons -->
        <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
        <a class="next" onclick="plusSlides(1)">&#10095;</a>
    </div>
    <br>

    <!-- The dots/circles -->
    <div style="text-align:center">
        <span class="dot" onclick="currentSlide(1)"></span>
        <span class="dot" onclick="currentSlide(2)"></span>
        <span class="dot" onclick="currentSlide(3)"></span>
        <span class="dot" onclick="currentSlide(4)"></span>
        <span class="dot" onclick="currentSlide(5)"></span>
        <span class="dot" onclick="currentSlide(6)"></span>
        <span class="dot" onclick="currentSlide(7)"></span>
        <span class="dot" onclick="currentSlide(8)"></span>

    </div>

</section>
<br>
<br>
<br>
<br>





<!--FOOTER-->
<footer class="footer-distributed" id="section5">

    <div class="footer-left">

        <h3><img src="{{asset('img/logo2.png')}}" width="40%" height="60%"></h3>

        <p class="footer-links">
            <a href="#section" class="link-1">Home</a>

            <a href="#section1">About us</a>

            <a href="#section2">Donation Proces</a>

            <a href="#section4">Our Gallery</a>

            <a href="#section5">Contact Us</a>
        </p>

        <p class="footer-company-name">Blood Donation (LSBB) © 2022</p>
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
            <p><a href="mailto:rara1999_sh@hotmail.com"> LSBB909_jo@hotmail.com</a></p>
        </div>

        <div class="footer-icons">

            <a href="https://www.facebook.com/LSBB555" target="_blank"><i class="fa fa-facebook"></i></a>
            <a href="https://www.twitter.com/LSBB69506682" target="_blank"><i class="fa fa-twitter"></i></a>
            <a href="https://www.instagram.com/lsbb555/" target="_blank"><i class="fa fa-instagram"></i></a>
            <a href="#"><i class="fa fa-whatsapp" target="_blank"></i></a>

        </div>
    </div>

    <div class="footer-right">

        <p style="font-size: 30px; text-align: center; font-family: sans-serif;">Contact Us</p>
        <br>
        <div class="containeeer">
            <form action="{{ route('contact') }}" method="post">
                @csrf
                <label for="fname">First Name</label>
                <input type="text" id="fname" name="firstname" placeholder="Your name..">

                <label for="lname">Last Name</label>
                <input type="text" id="lname" name="lastname" placeholder="Your last name..">

                <label for="lname">Your Email</label>
                <input type="text" id="email" name="email" placeholder="Your Email..">

                <label for="subject">Message</label>
                <textarea id="Message" name="message" placeholder="Write something.." style="height:100px"></textarea>

                <div style="text-align: center;"> <input type="submit" value="Submit"> </div>
            </form>
        </div>

    </div>

</footer>

<script>
    var slideIndex = 1;
    showSlides(slideIndex);

    // Next/previous controls
    function plusSlides(n) {
        showSlides(slideIndex += n);
    }

    // Thumbnail image controls
    function currentSlide(n) {
        showSlides(slideIndex = n);
    }

    function showSlides(n) {
        var i;
        var slides = document.getElementsByClassName("mySlides");
        var dots = document.getElementsByClassName("dot");
        if (n > slides.length) {slideIndex = 1}
        if (n < 1) {slideIndex = slides.length}
        for (i = 0; i < slides.length; i++) {
            slides[i].style.display = "none";
        }
        for (i = 0; i < dots.length; i++) {
            dots[i].className = dots[i].className.replace(" active", "");
        }
        slides[slideIndex-1].style.display = "block";
        dots[slideIndex-1].className += " active";
    }

    var slideIndex = 0;
    showSlides();

    function showSlides() {
        var i;
        var slides = document.getElementsByClassName("mySlides");
        for (i = 0; i < slides.length; i++) {
            slides[i].style.display = "none";
        }
        slideIndex++;
        if (slideIndex > slides.length) {slideIndex = 1}
        slides[slideIndex-1].style.display = "block";
        setTimeout(showSlides, 2000); // Change image every 2 seconds
    }


    //Get the button
    var mybutton = document.getElementById("myBtn");

    // When the user scrolls down 20px from the top of the document, show the button
    window.onscroll = function() {scrollFunction()};

    function scrollFunction() {
        if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
            mybutton.style.display = "block";
        } else {
            mybutton.style.display = "none";
        }
    }

    // When the user clicks on the button, scroll to the top of the document
    function topFunction() {
        document.body.scrollTop = 0;
        document.documentElement.scrollTop = 0;
    }
</script>



<script src="{{asset('js/donor_app.js')}}"></script>

</body>
</html>
