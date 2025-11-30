<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta
      name="description"
      content="{{$general->app_name}} | Best Platform that meet your digital needs at an affordable price "
    />
    <!-- fav icon -->
    <link rel="icon" href="img/welcome.png" />

    <!-- bootstarp css file -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css" />

    <!-- aos.css file -->
    <link rel="stylesheet" href="assets/css/aos.css" />

    <!-- fancybox css file -->
    <link rel="stylesheet" href="assets/css/jquery.fancybox.min.css" />

    <!-- owl carousel css file -->
    <link rel="stylesheet" href="assets/css/owl.carousel.min.css" />
    <link rel="stylesheet" href="assets/css/owl.theme.default.min.css" />

    <!--  toasts file     -->
    <link rel="stylesheet" href="assets/css/toastr.min.css" />

    <!-- bootstrap icons -->
    <link rel="stylesheet" href="assets/css/bootstrap-icons_1.css" />
    <link rel="stylesheet" href="assets/css/bootstrap-icons.css" />

    <!-- Google font -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <link
      href="https://fonts.googleapis.com/css2?family=Outfit:wght@100;200;300;400;500;600;700;800&amp;display=swap"
      rel="stylesheet"
    />

    <!-- main css file -->
    <link rel="stylesheet" href="assets/css/style.css" />
    <title>{{$general->app_name}} | Best Technology platform that offers solution to your data, airtime, utility bills, etc at an affordable price</title>
  </head>

  <body class="home-1">
   
    <!-- start scroll to top button -->
    <div id="progress">
      <span id="progress-value"><i class="bi bi-arrow-up-short"></i></span>
    </div>
    <!-- end scroll to top button -->

    <!-- ======= start Header ======= -->
    <header class="header">
      <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
          <a class="navbar-brand" href="#"
            ><img src="img/welcome.png" class="logo" alt="LOGO" style="max-width:60px"
          /></a>
          <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbarNav"
            aria-controls="navbarNav"
            aria-expanded="false"
            aria-label="Toggle navigation"
          >
            <i class="bi bi-list" id="menu"></i>
          </button>
          <div
            class="collapse navbar-collapse justify-content-between"
            id="navbarNav"
          >
            <ul class="navbar-nav ms-auto" id="navbar">
              <li class="nav-item">
                <a class="nav-link" href="#">Home</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#about">About</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#services">Services</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#pricing">Pricing</a>
              </li>
              <li class="nav-item dropdown">
                <a
                  class="nav-link dropdown-toggle"
                  href="#"
                  id="navbarDropdown"
                  role="button"
                  data-bs-toggle="dropdown"
                  aria-expanded="false"
                >
               User Authentication
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                  <li>
                    <a class="dropdown-item py-2" href="{{ env('APP_URL') . '/auth/register' }}">Register</a>
                  </li>
                  <li>
                    <a class="dropdown-item py-2" href="{{ env('APP_URL') . '/auth/login' }}">Login</a>
                  </li>
                  <li>
                    <a class="dropdown-item py-2" href="#faq"
                      >FAQ</a
                    >
                  </li>
                </ul>
              </li>
            </ul>
            <div class="d-flex ms-auto">
              <a class="btn" href="#contact">Contact Us</a>
              <button id="mode-toggle" class="btn-light-mode switch-button">
                <i id="mode-icon" class="bi bi-moon-fill"></i>
              </button>
            </div>
          </div>
        </div>
      </nav>
    </header>
    <!-- ======= end Header ======= -->

    <!-- ============== Start Hero section ========== -->
    <section
      class="hero d-flex flex-column justify-content-center align-items-center mt-5 pt-5"
      id="hero"
    >
      <div id="particles-js"></div>
      <div class="container">
        <div class="row justify-content-center align-items-center">
          <div class="hero-text col-12 col-lg-5">
            <div class="row">
              <div class="col-12">
                <h1
                  class="title col-12"
                  data-aos="fade-up"
                  data-aos-delay="150"
                >
              Welcome to <span class="unique-text"> {{$general->app_name}}</span> - <span id="element"></span>
                </h1>
              </div>
              <div
                class="col-lg-10 col-12"
                data-aos="fade-up"
                data-aos-delay="200"
              >
                {{-- <p>
                  At any rate, Lorem ipsum dolor sit amet consectetur
                  adipisicing elit. Eaque amet, quod doloribus the framework of
                  the essence represents a bond between the flexible production
                  planning and The Distribution of Repetitive Aspect
                </p> --}}
              </div>
              <div class="col-10" data-aos="fade-up" data-aos-delay="250">
                <a href="{{ env('APP_URL') . '/auth/register' }}" class="btn">Get Started For Free</a>
              </div>
              
            </div>
          </div>
          <div
            class="col-12 col-lg-7 mx-md-auto text-center"
            data-aos="fade-left"
            data-aos-delay="100"
          >
            <div class="hero-image">
              <div class="hero-img">
                <img
                  class="img-fluid"
                  alt="hero-img"
                  src="assets/images/green.png"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="creative-shape">
        <img src="assets/images/home-bottom-shape.png" alt="svg shape">
    </div>
    </section>
    <!-- ============== End Hero section ========== -->

    <!-- ============== Start services section ========== -->
    <section class="container services py-5" id="services">
      <div class="heading">
        <h4 class="pretitle" data-aos="fade-up">About Us</h4>
        <h1
          class="title col-lg-10 col-12"
          data-aos="fade-up"
          data-aos-delay="100"
        >
          What We’re Offering?
        </h1>
        <p class="col-lg-7 col-12" data-aos="fade-up" data-aos-delay="150">
          At {{$general->app_name}}, we understand the importance of staying connected in today's fast-paced world. Whether you're making important calls, sending urgent messages, or browsing the internet, having sufficient mobile credit and data is essential. That's where we come in.
        </p>
      </div>
      <div class="row gy-4 gx-4">
        <!-- service number 1 -->
        <div class="col-md-6 col-12 col-lg-4 mx-auto">
          <div
            class="box box-service box-hover"
            data-aos="fade-right"
            data-aos-delay="250"
          >
            <div class="box-icon my-2">
         <img src="assets/images/viber.png" alt="" x class="d-img">
            </div>
            <h2 class="title-2 my-2">Mobile Credit Top-Up</h2>
            <p>
              Need to recharge your mobile credit in a hurry? With our easy-to-use platform, you can top up your credit instantly for all major mobile networks.
            </p>
            <a href="{{ env('APP_URL') . '/auth/register' }}" class="my-2 learn-more"
              >Get Started <i class="bi bi-arrow-right"></i
            ></a>
          </div>
        </div>
        <div
          class="col-md-6 col-lg-4 mx-auto"
          data-aos="fade-down"
          data-aos-delay="200"
        >
          <!-- service number 2 -->
          <div class="box box-service box-hover">
            <div class="box-icon my-2">
              <img src="assets/images/signal.png" alt="" x class="d-img">
            </div>
            <h2 class="title-2 my-2">Data Subscription</h2>
            <p>
              Stay connected on the go with our data subscription services. Choose from a variety of data plans offered by leading telecom providers and purchase subscriptions directly through our platform.We have the right plan for you at an affordable price
            </p>
            <a href="{{ env('APP_URL') . '/auth/register' }}" class="my-2 learn-more"
              >Get Started<i class="bi bi-arrow-right"></i
            ></a>
          </div>
        </div>
        <div
          class="col-md-6 col-lg-4 mx-auto"
          data-aos="fade-left"
          data-aos-delay="250"
        >
          <!-- service number 3 -->
          <div class="box box-service box-hover">
            <div class="box-icon my-2">
              <img src="assets/images/backup.png" alt="" x class="d-img">
            </div>
            <h2 class="title-2 my-2">Utilty Bill Payment</h2>
            <p>
              Simplify your life by paying your utility bills directly through our platform. From electricity and water bills to cable TV and internet subscriptions, we've got you covered. Say goodbye to long queues and late payment fees – pay your bills conveniently from the comfort of your home or office with just a few clicks.
            </p>
            <a href="{{ env('APP_URL') . '/auth/register' }}" class="my-2 learn-more"
              >Get Started<i class="bi bi-arrow-right"></i
            ></a>
          </div>
        </div>
        <div
          class="col-md-6 col-lg-4 mx-auto"
          data-aos="fade-right"
          data-aos-delay="350"
        >
          <!-- service number 4 -->
          <div class="box box-service box-hover">
            <div class="box-icon my-2">
              <img src="assets/images/report-card.png" alt="" x class="d-img">
            </div>
            <h2 class="title-2 my-2">Online Result Checker</h2>
            <p>
              Stay informed about your educational or professional progress with our result checker service.
            </p>
            <a href="{{ env('APP_URL') . '/auth/register' }}" class="my-2 learn-more"
              >Get Started<i class="bi bi-arrow-right"></i
            ></a>
          </div>
        </div>
        <div
          class="col-md-6 col-lg-4 mx-auto"
          data-aos="fade-up"
          data-aos-delay="300"
        >
          <!-- service number 5 -->
          <div class="box box-service box-hover">
            <div class="box-icon my-2">
              <img src="assets/images/mobile.png" alt="" x class="d-img">
            </div>
            <h2 class="title-2 my-2">Airtime To Cash</h2>
            <p>
              Convert your excess airtime balance into cash with our airtime to cash service. Simply sell your unused airtime credits through our platform and receive instant payment directly to your bank account or mobile wallet. Turn your unused airtime into real money in no time!
            </p>
            <a href="{{ env('APP_URL') . '/auth/register' }}" class="my-2 learn-more"
              >Get Started<i class="bi bi-arrow-right"></i
            ></a>
          </div>
        </div>
        <div class="col-md-6 col-lg-4 mx-auto">
          <!-- service number 6 -->
          <div
            class="box box-service box-hover"
            data-aos="fade-left"
            data-aos-delay="350"
          >
            <div class="box-icon my-2">
              <img src="assets/images/cable-tv.png" alt="" x class="d-img">
            </div>
            <h2 class="title-2 my-2">TV Cable Subscription</h2>
            <p>
              Enjoy uninterrupted entertainment with our TV cable subscription services. Choose from a wide selection of cable TV packages from top providers and subscribe directly through our platform. Whether you're a sports enthusiast, movie buff, or news junkie, we have the perfect package to suit your preferences.
            </p>
            <a href="{{ env('APP_URL') . '/auth/register' }}" class="my-2 learn-more"
              >Get Started <i class="bi bi-arrow-right"></i
            ></a>
          </div>
        </div>
      </div>
    </section>
    <!-- ============== End services section ========== -->

    <!-- ============== Start About section ========== -->
    <section class="about py-5 mt-5" id="about">
      <div class="container">
        <div class="row mt-5 justify-content-center align-items-center">
          <div class="col-12 col-lg-6">
            <h4 class="pretitle" data-aos="fade-up" data-aos-delay="200">
              about us
            </h4>
            <h1 class="title col-12" data-aos="fade-up" data-aos-delay="250">
              The best <span class="unique-text">VTU</span> Platform
            </h1>
            <p class="col-lg-10 col-12" data-aos="fade-up" data-aos-delay="300">
              Welcome to {{$general->app_name}}, the premier VTU platform offering unparalleled convenience, reliability, and value to our customers. We pride ourselves on being the best in the industry.
            </p>
            <a href="#" class="btn" data-aos="fade-up" data-aos-delay="350"
              >Get Started</a
            >
          </div>
          <!-- image -->
          <div
            class="col-12 col-lg-6 d-flex align-items-center"
            data-aos="fade-left"
            data-aos-delay="100"
          >
            <img src="assets/images/yellow.png" alt="about-img" />
          </div>
        </div>
        <div class="row mt-5 py-4 justify-content-center align-items-center">
          <!-- image -->
          <div
            class="col-12 col-lg-6 d-flex align-items-center"
            data-aos="fade-right"
            data-aos-delay="100"
          >
            <img src="assets/images/female-green.png" alt="about-img" />
          </div>
          <div class="col-12 col-lg-6">
            <h1 class="title col-12" data-aos="fade-up" data-aos-delay="200">
          Our Services Provide 100% Satisfaction To All Your 
              <span class="unique-text">Digital Needs</span>
            </h1>
            <p class="col-lg-10 col-12" data-aos="fade-up" data-aos-delay="250">
              Experience the convenience and reliability of VTU services with {{$general->app_name}}. Recharge, connect, and manage your finances with ease. Get started today and discover a new way to stay connected and in control!
            </p>
            <div class="row gx-2 gy-2 col-12">
              <div
                class="col-lg-6 col-md-6 col-12"
                data-aos="fade-up"
                data-aos-delay="300"
              >
                <div class="box box-hover">
                  <i class="bi bi-shield-check"></i>
                  <h5 class="mx-4 title-2 fw-bold">Secure Payments</h5>
                </div>
              </div>
              <div
                class="col-lg-6 col-md-6 col-12"
                data-aos="fade-up"
                data-aos-delay="350"
              >
                <div class="box box-hover">
                  <i class="bi bi-headset"></i>
                  <h5 class="mx-4 title-2 fw-bold">24/7 Customer Care Support</h5>
                </div>
              </div>
              <div
                class="col-lg-6 col-md-6 col-12"
                data-aos="fade-up"
                data-aos-delay="400"
              >
                <div class="box box-hover">
                  <i class="bi bi-lightning"></i>
                  <h5 class="mx-4 title-2 fw-bold">Instant Transactions</h5>
                </div>
              </div>
              <div
                class="col-lg-6 col-md-6 col-12"
                data-aos="fade-up"
                data-aos-delay="450"
              >
                <div class="box box-hover">
                  <i class="bi bi-cash-coin"></i>
                  <h5 class="mx-4 title-2 fw-bold">Affordable Prices</h5>
                </div>
              </div>
            </div>
          </div>
        </div>
       
      </div>
    </section>
    <!-- ============== End About section ========== -->

    <!-- ============== Start Why us section ========== -->
    <section class="container why-choose-us py-5">
      <div class="heading">
        <h4 class="pretitle" data-aos="fade-up">why choose us</h4>
        <h1 class="title col-12" data-aos="fade-up" data-aos-delay="100">
          why our clients choose us
        </h1>
        <p class="col-lg-7 col-12" data-aos="fade-up" data-aos-delay="150">
          At {{$general->app_name}}, we take pride in being the preferred choice for thousands of clients when it comes to Virtual Top-Up (VTU) services. Here are some compelling reasons why our clients choose us:
        </p>
      </div>
      <div class="row gy-4 gx-4">
        <div
          class="col-md-6 col-lg-4 mx-auto"
          data-aos="fade-right"
          data-aos-delay="250"
        >
          <div class="box">
            <h1 class="my-4">01.</h1>
            <h2 class="title-2 my-2">Reliability</h2>
            <p>
              Clients trust us because we deliver on our promises. With our platform, they can top up their mobile credit, purchase data subscriptions, pay utility bills, and more with confidence, knowing that their transactions are processed seamlessly and efficiently.
            </p>
          </div>
        </div>
        <div
          class="col-md-6 col-lg-4 mx-auto"
          data-aos="fade-up"
          data-aos-delay="200"
        >
          <div class="box">
            <h1 class="my-4">02.</h1>
            <h2 class="title-2 my-2">Convenience</h2>
            <p>
              Convenience is key in today's fast-paced world, and our platform offers just that. Clients can access our services anytime, anywhere, using their smartphones or computers. Whether they're at home, at work, or on the go, they can easily recharge, connect, and manage their finances with a few simple clicks.
            </p>
          </div>
        </div>
        <div
          class="col-md-6 col-lg-4 mx-auto"
          data-aos="fade-left"
          data-aos-delay="250"
        >
          <div class="box">
            <h1 class="my-4">03.</h1>
            <h2 class="title-2 my-2">Wide Range of Services</h2>
            <p>
              We understand that our clients have diverse needs, which is why we offer a wide range of services to cater to them all. From mobile credit top-ups to data subscriptions, utility bill payments, TV cable subscriptions, airtime to cash conversion, and result checking, we have everything our clients need under one roof.
            </p>
          </div>
        </div>
      </div>
    </section>
    <!-- ============== End Why us section ========== -->

    <!-- ============== Start clients section ========== -->

    <div class="clients py-lg-5 py-2" data-aos="fade-up" data-aos-delay="100">
      <div class="container">
        <div class="row">
          <div class="owl-carousel client owl-theme">
            <div class="item">
              <img src="assets/images/waec.png" alt="client-logo" />
            </div>
            <div class="item">
              <img src="assets/images/mtn.jpeg" alt="client-logo" />
            </div>
            <div class="item">
              <img src="assets/images/airtel.jpeg" alt="client-logo" />
            </div>
            <div class="item">
              <img src="assets/images/9mobile.jpeg" alt="client-logo" />
            </div>
            <div class="item">
              <img src="assets/images/ikeja.jpeg" alt="client-logo" />
            </div>
            <div class="item">
              <img src="assets/images/monnify.jpeg" alt="client-logo" />
            </div>
            <div class="item">
              <img src="assets/images/glo.webp" alt="client-logo" />
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- ============== End clients section ========== -->

    <!-- ============== Start Pricing section ========== -->
    <section class="container pricing py-5" id="pricing">
      <div class="heading">
        <h4 class="pretitle" data-aos="fade-up">Pricing</h4>
        <h1 class="title col-12" data-aos="fade-up" data-aos-delay="100">
             Our Affordable Data Plans
        </h1>
        <p class="col-lg-7 col-12" data-aos="fade-up" data-aos-delay="150">
         
        </p>
        <div class="row gy-4 gx-4">
          <div
            class="col-md-6 col-lg-3 mx-auto"
            data-aos="fade-up"
            data-aos-delay="200"
          >
            <div class="box">
            <img src="assets/images/mtn-banner.png" alt="mtn banner" style="max-width: 90px">
              <h1 class="my-2 title-2">MTN</h1>
              <table class="table table-all ">
                @foreach ($mtn as $mtns)
                <tr>
                  <td style="color:#515151; font-size:15px; font-weight: bolder;">
                    {{ $mtns->plan_name . $mtns->plan_size}} </td>
                  <td style="color:#515151; font-size:15px;  font-weight: bolder;">
                    &#8358;{{ $mtns->smart }}
                  </td>
                  <td style="color:#515151; font-size:15px;  font-weight: bolder;">
                    {{ $mtns->plan_day }}
                  </td>
                </tr>
              @endforeach</table>
              <a href="{{ env('APP_URL') . '/auth/register' }}" class="btn my-4">Buy now</a>
            </div>
          </div>
          <div
            class="col-md-6 col-lg-3 mx-auto"
            data-aos="fade-up"
            data-aos-delay="250"
          >
            <div class="box">
             <img src="assets/images/airtel-banner.png" alt="airtel banner"style="max-width: 90px">
             <h1 class="my-2 title-2">AIRTEL</h1>
              <table class="table table-all ">
                @foreach ($airtel as $mtns)
                  <tr>
                   <td style="color:#414141;font-size:15px; font-weight: bolder;">
                     {{ $mtns->plan_name . $mtns->plan_size}} </td>
                       <td style="color:#414141; font-size:15px;  font-weight: bolder;">
                           &#8358;{{ $mtns->smart }}
                              </td>
                          <td style="color:#414141; font-size:15px;  font-weight: bolder;">
                              {{ $mtns->plan_day }}
                                 </td>
                                </tr>
                           @endforeach</table>
              <a href="{{ env('APP_URL') . '/auth/register' }}" class="btn my-4">Buy now</a>
            </div>
          </div>
          <div
            class="col-md-6 col-lg-3 mx-auto"
            data-aos="fade-up"
            data-aos-delay="300"
          >
            <div class="box" id="table3">
              <img src="assets/images/glo-banner.png" alt="glo banner"style="max-width: 90px">
              <h1 class="my-2 title-2">GLO</h1>
              <table class="table table-all ">
                @foreach ($glo as $mtns)
                  <tr>
                   <td style="color:#414141;font-size:15px; font-weight: bolder;">
                     {{ $mtns->plan_name . $mtns->plan_size}} </td>
                       <td style="color:#414141; font-size:15px;  font-weight: bolder;">
                           &#8358;{{ $mtns->smart }}
                              </td>
                          <td style="color:#414141; font-size:15px;  font-weight: bolder;">
                              {{ $mtns->plan_day }}
                                 </td>
                                </tr>
                           @endforeach</table>
              <a href="{{ env('APP_URL') . '/auth/register' }}" class="btn my-4">Buy Now</a>
            </div>
          </div>
          <div
            class="col-md-6 col-lg-3 mx-auto"
            data-aos="fade-up"
            data-aos-delay="350"
          >
            <div class="box">
              <img src="assets/images/mobile-banner.png" alt="9mobile banner"style="max-width: 90px">
                <h1 class="my-2 title-2">9MOBILE</h1>
              <table class="table table-all ">
                @foreach ($mobile as $mtns)
                  <tr>
                   <td style="color:#414141;font-size:15px; font-weight: bolder;">
                     {{ $mtns->plan_name . $mtns->plan_size}} </td>
                       <td style="color:#414141; font-size:15px;  font-weight: bolder;">
                           &#8358;{{ $mtns->smart }}
                              </td>
                          <td style="color:#414141; font-size:15px;  font-weight: bolder;">
                              {{ $mtns->plan_day }}
                                 </td>
                                </tr>
                           @endforeach</table>
              <a href="{{ env('APP_URL') . '/auth/register' }}" class="btn my-4">Buy Now</a>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- ============== End Pricing section ========== -->

    {{-- start download app --}}
    <div class="row mt-5 py-4 justify-content-center align-items-center">
      <!-- image -->
      <div
        class="col-12 col-lg-6 d-flex align-items-center"
        data-aos="fade-right"
        data-aos-delay="100"
      >
        <img src="assets/images/phone-1.png" alt="about-img" />
      </div>
      <div class="col-12 col-lg-6">
        <h1 class="title col-12" data-aos="fade-up" data-aos-delay="200">
          Download our VTU android and Ios app
         
        </h1>
        <p class="col-lg-10 col-12" data-aos="fade-up" data-aos-delay="250">
          At {{$general->app_name}}, we're committed to making your VTU experience as seamless and effortless as possible. That's why we're thrilled to introduce the {{$general->app_name}} app – your all-in-one solution for VTU services on the go.
        </p>
        <div class="row gx-2 gy-2 col-12">
          <div
            class="col-lg-6 col-md-6 col-12"
            data-aos="fade-up"
            data-aos-delay="300"
          >
          <a href=""> <img src="assets/images/google-play-banner.png" alt="" style="max-width: 150px"></a>
         <a href="">  <img src="assets/images/app-store.webp" alt="" style="max-width: 150px"></a>
          </div>
         
            
          </div>
        </div>
      </div>
    </div>
    <!-- ============== Start FAQ section ========== -->
    <section class="container faq py-5" id="faq">
      <div class="heading">
        <h4 class="pretitle" data-aos="fade-up">FAQ</h4>
        <h1 class="title col-12" data-aos="fade-up" data-aos-delay="100">
          Frequently Asked Questions
        </h1>
        <p class="col-lg-7 col-12" data-aos="fade-up" data-aos-delay="150">
          
        </p>
      </div>
      <div class="row justify-content-center align-items-center gx-4">
        <div class="col-12 col-lg-6" data-aos="fade-right" data-aos-delay="200">
          <img src="assets/images/orange.png" alt="faq" />
        </div>
        <div class="col-12 col-lg-6">
          <div class="col-12 my-4" data-aos="fade-up" data-aos-delay="250">
            <div class="box">
              <div class="d-flex w-100 justify-content-between">
                <a
                  data-bs-toggle="collapse"
                  class="w-100"
                  onclick="rotateIcon('icon1')"
                  href="#answer1"
                  role="button"
                  aria-expanded="false"
                >
                  <h4
                    class="d-flex justify-content-between w-100 heading-3 m-0 p-0"
                  >
                  How does VTU work?
                    <i
                      class=""
                      id="icon1"
                    ></i>
                  </h4>
                </a>
              </div>
              <p id="answer1" class="collapse">
                VTU works by leveraging digital platforms such as mobile apps, websites, or USSD codes to facilitate electronic transactions for mobile credit top-ups, data subscriptions, bill payments, and other services. Users simply need to access the VTU platform, select the desired service, enter relevant details such as phone number or account information, and complete the transaction securely.
              </p>
            </div>
          </div>
          <div class="col-12 my-4" data-aos="fade-up" data-aos-delay="300">
            <div class="box">
              <div class="d-flex w-100 justify-content-between">
                <a
                  data-bs-toggle="collapse"
                  class="w-100"
                  onclick="rotateIcon('icon2')"
                  href="#answer2"
                  role="button"
                  aria-expanded="false"
                >
                  <h4
                    class="d-flex justify-content-between w-100 heading-3 m-0 p-0"
                  >
                  What services does {{$general->app_name}} offer?
                    <i
                      class="bi bi-chevron-compact-down mx-4 rotate-icon"
                      id="icon2"
                    ></i>
                  </h4>
                </a>
              </div>
              <p id="answer2" class="collapse">
                [Your VTU Business Name] offers a comprehensive range of VTU services, including mobile credit top-ups, data subscriptions, utility bill payments, TV cable subscriptions, airtime to cash conversion, result checking, and more.
              </p>
            </div>
          </div>
          <div class="col-12 my-4" data-aos="fade-up" data-aos-delay="350">
            <div class="box">
              <div class="d-flex w-100 justify-content-between">
                <a
                  data-bs-toggle="collapse"
                  class="w-100"
                  onclick="rotateIcon('icon3')"
                  href="#answer3"
                  role="button"
                  aria-expanded="false"
                >
                  <h4
                    class="d-flex justify-content-between w-100 heading-3 m-0 p-0"
                  >
                  What should I do if I encounter any issues or have questions about {{$general->app_name}}?
                    <i
                      class="bi bi-chevron-compact-down mx-4 rotate-icon"
                      id="icon3"
                    ></i>
                  </h4>
                </a>
              </div>
              <p id="answer3" class="collapse">
                If you encounter any issues or have questions about {{$general->app_name}}, our dedicated customer support team is here to help. Simply reach out to us through the app, website, or contact information provided, and we'll assist you promptly with any inquiries or concerns you may have.
              </p>
            </div>
          </div>
          <div class="col-12 my-4" data-aos="fade-up" data-aos-delay="400">
            <div class="box">
              <div class="d-flex w-100 justify-content-between">
                <a
                  data-bs-toggle="collapse"
                  class="w-100"
                  onclick="rotateIcon('icon4')"
                  href="#answer4"
                  role="button"
                  aria-expanded="false"
                >
                  <h4
                    class="d-flex justify-content-between w-100 heading-3 m-0 p-0"
                  >
                  Is it safe to use {{$general->app_name}} for transactions?
                    <i
                      class="bi bi-chevron-compact-down mx-4 rotate-icon"
                      id="icon4"
                    ></i>
                  </h4>
                </a>
              </div>
              <p id="answer4" class="collapse">
                Yes, {{$general->app_name}} prioritizes the security and privacy of our users. We employ advanced encryption technologies and secure payment gateways to ensure that your personal and financial information remains safe and secure at all times.
              </p>
            </div>
          </div>
          <div class="col-12 my-4" data-aos="fade-up" data-aos-delay="400">
            <div class="box">
              <div class="d-flex w-100 justify-content-between">
                <a
                  data-bs-toggle="collapse"
                  class="w-100"
                  onclick="rotateIcon('icon4')"
                  href="#answer4"
                  role="button"
                  aria-expanded="false"
                >
                  <h4
                    class="d-flex justify-content-between w-100 heading-3 m-0 p-0"
                  >
                 How do i get started?
                    <i
                      class="bi bi-chevron-compact-down mx-4 rotate-icon"
                      id="icon4"
                    ></i>
                  </h4>
                </a>
              </div>
              <p id="answer4" class="collapse">
                First step is for you to create and verify your account, This process is free. Second step is for you to fund your wallet with any of the available payment options. Third step is for you to explore our data offerings and make a swift purchase to meet your connectivity needs
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>
    <!-- ============== End FAQ section ========== -->

    <!-- ============== Start contact section ========== -->
    <section class="container contact py-5" id="contact">
      <div class="heading">
        <h4 class="pretitle" data-aos="fade-up">contact</h4>
        <h1 class="title col-12" data-aos="fade-up" data-aos-delay="100">
         Contact us for more information
        </h1>
        <p class="col-lg-7 col-12" data-aos="fade-up" data-aos-delay="150">
          Feel free to reach out to our support team for latest news and other interesting offers about {{$general->app_name}}
        </p>
      </div>
      <div class="row gx-4">
        <div class="col-12 col-lg-6 gy-3">
          <h2 class="title-2" data-aos="fade-right" data-aos-delay="200">
            contact info :
          </h2>
          <div
            class="info d-flex my-4"
            data-aos="fade-right"
            data-aos-delay="250"
          >
            <h5><i class="bi bi-envelope-fill mx-4"></i> {{ $general->app_email }}</h5>
          </div>
          <div class="info d-flex my-4" data-aos="fade-up" data-aos-delay="300">
            <h5><i class="bi bi-phone-fill mx-4"></i>  {{ $general->app_phone }}
            </h5>
          </div>
          <div class="info d-flex my-4" data-aos="fade-up" data-aos-delay="350">
            <h5>
              <i class="bi bi-geo-alt-fill mx-4"></i> {{ $general->app_address }}    
            </h5>
          </div>
        </div>
        <div class="col-12 col-lg-6">
          <!--Form To have user messages-->
          <form
            class="main-form"
            id="contact-us-form"
            action="contact.php"
            method="post"
          >
            <div class="col-12">
              <div class="row g-3 mb-1">
                <div
                  class="col-lg-6 col-12"
                  data-aos="fade-right"
                  data-aos-delay="200"
                >
                  <input
                    placeholder="name"
                    type="text"
                    id="name"
                    name="name"
                    required=""
                    class="text-input"
                  />
                </div>
                <div
                  class="col-lg-6 col-12"
                  data-aos="fade-left"
                  data-aos-delay="200"
                >
                  <input
                    placeholder="subject"
                    type="text"
                    id="subject"
                    name="subject"
                    required=""
                    class="text-input"
                  />
                </div>
              </div>
            </div>
            <div class="col-12" data-aos="fade-up" data-aos-delay="250">
              <input
                placeholder="email"
                type="email"
                id="email"
                name="email"
                required=""
                class="text-input my-2"
              />
            </div>
            <div class="col-12" data-aos="fade-up" data-aos-delay="300">
              <textarea
                placeholder="message"
                class="text-input my-2"
                rows="7"
                cols="30"
                id="message"
                name="message"
                required=""
              ></textarea>
            </div>
            <div class="col-12" data-aos="fade-up" data-aos-delay="350">
              <button type="submit" value="Submit" class="btn"><a href="mailto: {{ $general->app_email }}" style="color: white">Send Now</a></button>
            </div>
          </form>
        </div>
      </div>
    </section>
    <!-- ============== end contact section ========== -->

    <!-- ============== Start Footer section ========== -->
    <div class="footer">
      <div class="container">
        <div class="row justify-content-center align-items-center g-4">
          <div class="col-12 col-lg-3 col-md-6 mx-auto my-4">
            <div class="box">
              <a href="#" class="logo"
                ><img class="my-2" src="img/welcome.png" alt=""
              /></a>
              <p>
            <span id="multiple"></span>
              </p>
              <h4 class="my-2">Follow Us</h4>
              <div class="social d-flex gap-2">
                <div class="icon"><i class="bi bi-facebook"></i></div>
                <div class="icon"><i class="bi bi-instagram"></i></div>
                <div class="icon"><i class="bi bi-twitter"></i></div>
                <div class="icon"><i class="bi bi-youtube"></i></div>
              </div>
            </div>
          </div>
          <div
            class="col-12 col-lg-3 col-md-6 mx-auto d-flex flex-column my-4 gap-3"
          >
            <h4>site map</h4>
            <ul class="d-flex flex-column gap-3">
              <li class="d-flex align-items-center gap-2">
                <i class="bi bi-chevron-right"></i><a href="#">Home</a>
              </li>
              <li class="d-flex align-items-center gap-2">
                <i class="bi bi-chevron-right"></i
                ><a href="#about">About</a>
              </li>
              <li class="d-flex align-items-center gap-2">
                <i class="bi bi-chevron-right"></i
                ><a href="#services">Services</a>
              </li>
              <li class="d-flex align-items-center gap-2">
                <i class="bi bi-chevron-right"></i
                ><a href="#contact">Contact</a>
              </li>
              <li class="d-flex align-items-center gap-2">
                <i class="bi bi-chevron-right"></i
                ><a href="#faq">FAQ</a>
              </li>
            </ul>
          </div>
          <div
            class="col-12 col-lg-3 col-md-6 mx-auto d-flex flex-column my-4 gap-3"
          >
            <h4>User Authentication</h4>
            <ul class="d-flex flex-column gap-3">
              <li class="d-flex align-items-center gap-2">
                <i class="bi bi-chevron-right"></i
                ><a href="{{ env('APP_URL') . '/auth/register' }}">Register</a>
              </li>
              <li class="d-flex align-items-center gap-2">
                <i class="bi bi-chevron-right"></i
                ><a href="{{ env('APP_URL') . '/auth/login' }}">Sign In</a>
              </li>
            </ul>
          </div>
        </div>
      </div>
      <hr />
      <div class="copyrights">
        <div class="container">
          <div class="row">
            <div class="col-12 col-md-6 d-flex justify-content-start">
              <p style="color:white">Copyright © {{date('Y')}} {{ $general->app_name }}.  Developed by
                <a href="https://adedevelopers.com"  target="_blank" style="color:#1fa84f">A D E Developers </a> .All Rights Reserved</p>

            </div>
            <div class="col-12 col-md-6 d-flex justify-content-end">
              <div>
                <a href="terms-of-use.html">Terms of Use </a>
                | <a href="privacy-policy.html">Privacy Policy</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- ============== end Footer section ========== -->

    <!--  JQuery file     -->
    <script src="assets/js/jquery-3.6.1.min.js"></script>

    <!-- bootstrap min js -->
    <script src="assets/js/bootstrap.min.js"></script>

    <!--  toasts file     -->
    <script src="assets/js/toastr.min.js"></script>

    <!--  owl carousel    -->
    <script src="assets/js/owl.carousel.min.js"></script>

    <!-- aos file    -->
    <script src="assets/js/aos.js"></script>

    <!-- gsap file    -->
    <script src="assets/js/gsap.min.js"></script>

    <!--  counter     -->
    <script src="assets/js/jquery.counterup.min.js"></script>
    <script src="assets/js/jquery.waypoints.js"></script>

    <!-- particles js file  -->
    <script src="assets/js/particles.min.js"></script>

    <!-- jquery isotope file -->
    <script src="assets/js/isotope.min.js"></script>

    <!-- jquery fancybox file -->
    <script src="assets/js/jquery.fancybox.min.js"></script>

    <!--  main js file  -->
    <script src="assets/js/main.js"></script>
    {{-- typed --}}
    <script src="https://unpkg.com/typed.js@2.1.0/dist/typed.umd.js"></script>
    <script>
      var typed = new Typed('#element', {
        strings: ['your trusted partner for all your Virtual Top-Up (VTU) needs. '],
        typeSpeed: 50,
        backspeed: 500,
        backDelay: 2000,
        loop: true,
      });
    </script>
    <script>
      var typed = new Typed('#multiple', {
        strings: ['Best VTU platform for your digital needs'],
        typeSpeed: 50,
        backspeed: 500,
        backDelay: 2000,
        loop: true,
      });
    </script>
  </body>
</html>
