<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Servicio a domicilio. La solución rápida y cómoda para tus averías" />
    <meta name="keywords"
        content="Las Palmas de Gran Canaria, Telde, Santa Lucía de Tirajana, Arucas, Gran Canaria, Islas Canarias, turismo, viaje, lugares de interés, vacaciones, turismo en Gran Canaria">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2563eb" />
    <link rel="apple-touch-icon" sizes="180x180" href="img/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="img/favicon/favicon-16x16.png">
    <link rel="manifest" href="img/favicon/site.webmanifest">

    <title> SERVISPIN | Servicio De Reparación De Lavadoras Y Secadoras a "DOMICILIO"
        en GRAN CANARIA!</title>

    <!-- Google Tag Manager -->
    <script>
        (function(w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(),
                event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', 'GTM-PHXST37L');
    </script>
    <!-- End Google Tag Manager -->


    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />


    <!-- Scripts -->

    <!-- Styles -->
    <!--====== Slick css ======-->
    <link rel="stylesheet" href="files/css/slick.css" />

    <!--====== Line Icons css ======-->
    <link rel="stylesheet" href="files/css/LineIcons.css" />

    <!--====== Magnific Popup css ======-->
    <link rel="stylesheet" href="files/css/magnific-popup.css" />

    <!--====== tailwind css ======-->
    <link rel="stylesheet" href="files/css/tailwind.css" />

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-PVMDBD4L5Z"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-PVMDBD4L5Z');
    </script>



</head>
<style>
    body {
        font-family: 'Roboto', sans-serif;
    }

    ::-webkit-scrollbar {
        width: 12px;
    }

    ::-webkit-scrollbar-track {
        background-color: #e5e7eb;
        border-radius: 9px;
    }

    ::-webkit-scrollbar-thumb {
        background-color: #6b7280;
        border-radius: 7px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background-color: #3b82f6;
        border-radius: 7px;
    }



    @media screen and (max-width: 768px) {
        #titleResponsive {
            font-size: 26px;
        }
    }
</style>
</head>

<body class="antialiased">
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PHXST37L" height="0" width="0"
            style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <!--====== HEADER PART START ======-->

    <header class="header-area">
        <div class="navigation">
            <div class="container">
                <div class="row">
                    <div class="w-full">
                        <nav class="flex items-center justify-between navbar navbar-expand-md">
                            <a class="mr-4 navbar-brand" href="#home">
                                <img src="files/images/logo.png" style="width: 100px;" alt="Logo" />
                            </a>

                            <button class="block navbar-toggler focus:outline-none md:hidden" type="button"
                                data-toggle="collapse" data-target="#navbarOne" aria-controls="navbarOne"
                                aria-expanded="false" aria-label="Toggle navigation">
                                <span class="toggler-icon"></span>
                                <span class="toggler-icon"></span>
                                <span class="toggler-icon"></span>
                            </button>

                            <!-- justify-center hidden md:flex collapse navbar-collapse sub-menu-bar -->
                            <div class="absolute left-0 z-30 hidden w-full px-5 py-3 duration-300 bg-white shadow md:opacity-100 md:w-auto collapse navbar-collapse md:block top-100 mt-full md:static md:bg-transparent md:shadow-none"
                                id="navbarOne">
                                <ul
                                    class="items-center content-start mr-auto lg:justify-center md:justify-end navbar-nav md:flex">
                                    <!-- flex flex-row mx-auto my-0 navbar-nav -->
                                    <li class="nav-item active">
                                        <a class="page-scroll" href="#home">Inicio</a>
                                    </li>

                                    <li class="nav-item">
                                        <a class="page-scroll" href="#ourservices">Servicios</a>
                                    </li>

                                    <li class="nav-item">
                                        <a class="page-scroll" href="#testimonial">Testimonios</a>
                                    </li>

                                    <li class="nav-item">
                                        <a class="page-scroll" target="_blank"
                                            href="{{ route('appointments.book') }}">Agendar
                                            Cita</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="page-scroll" href="#contact">Contacto</a>
                                    </li>
                                </ul>
                            </div>

                            <div class="items-center justify-end hidden navbar-social lg:flex">
                                <span class="mr-4 font-bold text-gray-900 uppercase">FOLLOW US</span>
                                <ul class="flex footer-social">
                                    @if ($companyData && $companyData->social_media_facebook)
                                        <li>
                                            <a href="{{ $companyData->social_media_facebook }}" target="_blank"
                                                rel="noopener noreferrer"><i class="lni-facebook-filled"></i></a>
                                        </li>
                                    @endif
                                    @if ($companyData && $companyData->social_media_twitter)
                                        <li>
                                            <a href="{{ $companyData->social_media_twitter }}" target="_blank"
                                                rel="noopener noreferrer"><i class="fa-brands fa-x-twitter"></i></a>
                                        </li>
                                    @endif
                                    @if ($companyData && $companyData->social_media_instagram)
                                        <li>
                                            <a href="{{ $companyData->social_media_instagram }}" target="_blank"
                                                rel="noopener noreferrer"><i class="lni-instagram-original"></i></a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </nav>
                        <!-- navbar -->
                    </div>
                </div>
                <!-- row -->
            </div>
            <!-- container -->
        </div>
        <!-- navgition -->

        <div id="home" class="relative z-10 header-hero"
            style="background-image: url(files/images/header-bg.jpg)">
            <div class="container">
                <div class="justify-center row">
                    <div class="w-full lg:w-5/6 xl:w-2/3">
                        <div class="pt-48 pb-64 text-center header-content">
                            <h3 class="mb-5 text-3xl font-semibold leading-tight text-gray-900 md:text-5xl">
                                Trabajamos en toda Gran Canaria: Expertos en el cuidado de tus electrodomésticos
                            </h3>
                            <p class="px-5 mb-10 text-xl text-gray-700">
                                Somos tu mejor opción para el cuidado de tus electrodomésticos en <b>Las Palmas y sus
                                    alrededores.</b> Ofrecemos un servicio técnico de confianza, rápido y eficiente, con
                                la
                                garantía de que tu equipo estará en las mejores manos.
                            </p>
                            @if ($companyData && $companyData->phone)
                                <ul class="flex flex-wrap justify-center">
                                    <li>
                                        <a class="mx-3 main-btn gradient-btn"
                                            href="javascript:abrirWhatsApp('{{ $companyData->phone }}')">
                                            <i class="fa-brands fa-whatsapp" style="margin-right: 7px"></i>Whatsapp
                                        </a>
                                    </li>
                                    <li>
                                        <a class="mx-3 main-btn" href="tel:{{ $companyData->phone }}">Llamar Ahora <i
                                                class="fa-solid fa-phone ml-2"></i></a>
                                    </li>
                                </ul>
                            @endif
                        </div>
                        <!-- header content -->
                    </div>
                </div>
                <!-- row -->
            </div>
            <!-- container -->
            <div class="absolute bottom-0 z-20 w-full h-auto -mb-1 header-shape">
                <img src="files/images/header-shape.svg" alt="shape" />
            </div>
        </div>
        <!-- header content -->
    </header>

    <!--====== HEADER PART ENDS ======-->



    <!--====== SERVICES PART START ======-->

    <section id="service" class="relative services-area py-120">
        <div class="container">
            <div class="flex">
                <div class="w-full mx-4 lg:w-1/2">
                    <div class="pb-10 section-title">
                        <h4 class="title" id="titleResponsive">Nuestros Servicios</h4>
                        <p class="text">
                            Nuestro mayor compromiso es el cuidado de tus electrodomésticos. Sentimos una profunda
                            pasión por ayudarte a mantenerlos en perfecto estado, para que puedas disfrutarlos al máximo
                            durante muchos años.
                        </p>
                    </div>
                    <!-- section title -->
                </div>
            </div>
            <!-- row -->
            <div class="flex">
                <div class="w-full lg:w-2/3">
                    <div class="row">
                        <div class="w-full md:w-1/2">
                            <div class="block mx-4 services-content sm:flex">
                                <div class="services-icon">
                                    <i class="fa-solid fa-power-off"></i>
                                </div>
                                <div class="mb-8 ml-0 services-content media-body sm:ml-3">
                                    <h4 class="services-title">Lavadoras</h4>
                                    <p class="text">
                                        Ofrecemos una amplia gama de servicios para tu lavadora, garantizando su
                                        correcto funcionamiento y prolongando su vida útil.
                                    </p>
                                </div>
                            </div>
                            <!-- services content -->
                        </div>
                        <div class="w-full md:w-1/2">
                            <div class="block mx-4 services-content sm:flex">
                                <div class="services-icon">
                                    <i class="fa-solid fa-clock-rotate-left"></i>
                                </div>
                                <div class="mb-8 ml-0 services-content media-body sm:ml-3">
                                    <h4 class="services-title">Lavavajillas</h4>
                                    <p class="text">
                                        Ofrecemos reparación, limpieza, mantenimiento y sustitución de piezas para
                                        lavavajillas. ¡Tu lavavajillas en las mejores manos!
                                    </p>
                                </div>
                            </div>
                            <!-- services content -->
                        </div>
                        <div class="w-full md:w-1/2">
                            <div class="block mx-4 services-content sm:flex">
                                <div class="services-icon">
                                    <i class="fa-regular fa-sun"></i>
                                </div>
                                <div class="mb-8 ml-0 services-content media-body sm:ml-3">
                                    <h4 class="services-title">Secadoras</h4>
                                    <p class="text">
                                        Extendemos la vida de tu secadora. Disfruta de un funcionamiento óptimo por más
                                        tiempo gracias a nuestros servicios especializados.
                                    </p>
                                </div>
                            </div>
                            <!-- services content -->
                        </div>
                        <div class="w-full md:w-1/2">
                            <div class="block mx-4 services-content sm:flex">
                                <div class="services-icon">
                                    <i class="fa-solid fa-fan"></i>
                                </div>
                                <div class="mb-8 ml-0 services-content media-body sm:ml-3">
                                    <h4 class="services-title">Aire Acondicionados</h4>
                                    <p class="text">
                                        Venta e instalación de aires acondicionados, industriales y residenciales.
                                    </p>
                                </div>
                            </div>
                            <!-- services content -->
                        </div>

                        <div class="w-full md:w-1/2">
                            <div class="block mx-4 services-content sm:flex">
                                <div class="services-icon">
                                    <i class="fa-solid fa-snowflake"></i>
                                </div>
                                <div class="mb-8 ml-0 services-content media-body sm:ml-3">
                                    <h4 class="services-title">Neveras de Hosteleria</h4>
                                    <p class="text">
                                        Contamos con técnicos expertos en neveras de
                                        hostelería y con las herramientas y repuestos necesarios para realizar cualquier
                                        tipo de reparación.
                                    </p>
                                </div>
                            </div>
                            <!-- services content -->
                        </div>
                        <div class="w-full md:w-1/2">
                            <div class="block mx-4 services-content sm:flex">
                                <div class="services-icon">
                                    <i class="fa-solid fa-tv"></i>
                                </div>
                                <div class="mb-8 ml-0 services-content media-body sm:ml-3">
                                    <h4 class="services-title">Televisores OLED, LED, LCD</h4>
                                    <p class="text">
                                        Instalación de soportes de pared, configuración de mandos y canales, sustitución
                                        de tiras LED y más. ¡Tu televisor en las mejores manos!
                                    </p>
                                </div>
                            </div>
                            <!-- services content -->
                        </div>
                        <!-- services content -->
                    </div>
                </div>
                <!-- row -->
            </div>
            <!-- row -->
        </div>
        <!-- row -->
        </div>
        <!-- container -->
        <div class="services-image">
            <div class="image">
                <img src="files/images/services.png" alt="Services" />
            </div>
        </div>
        <!-- services image -->
    </section>

    <!--====== SERVICES PART ENDS ======-->

    <!--====== OUR SERVICES PART START ======-->

    <section id="ourservices" class="bg-white pricing-area py-120" style="margin-top:-140px;">
        <div class="container">
            <div class="justify-center row">
                <div class="w-full mx-4 lg:w-1/2">
                    <div class="pb-10 text-center section-title">
                        <h4 class="title" id="titleResponsive">Tu aliado para soluciones personalizadas</h4>
                        <p class="text">
                            No importa cuál sea el problema, nuestro equipo de expertos técnicos está altamente
                            capacitado para diagnosticar y reparar cualquier avería de forma rápida y eficiente
                        </p>
                    </div>
                    <!-- section title -->
                </div>
            </div>
            <div class="container mx-auto " id="">
                <div class="flex flex-wrap">
                    <div class="w-full md:w-1/2" style="max-width: 600px;">
                        <img src="files/images/services10.png" alt="Imagen" class="rounded"
                            style=" object-fit: cover;">
                    </div>

                    <div class="w-full md:w-1/2 p-4">

                        <ul class="list-disc p-4 space-y-4" style="line-height: 33px">
                            <li> <i class="fa-regular fa-circle-check" style="color: #166534; margin-right:8px"></i>
                                Instalación de Electrodomésticos</li>
                            <li> <i class="fa-regular fa-circle-check" style="color: #166534; margin-right:8px"></i>
                                Diagnóstico preciso de averías.</li>

                            <li> <i class="fa-regular fa-circle-check" style="color: #166534; margin-right:8px"></i>
                                Reparación de todas las marcas de lavadoras, secadoras y lavavajillas.</li>

                            <li> <i class="fa-regular fa-circle-check" style="color: #166534; margin-right:8px"></i>
                                Uso de
                                repuestos originales y de alta calidad.
                            </li>
                            <li> <i class="fa-regular fa-circle-check" style="color: #166534; margin-right:8px"></i>
                                Servicio
                                técnico a domicilio en Gran Canaria.</li>
                            <li> <i class="fa-regular fa-circle-check" style="color: #166534; margin-right:8px"></i>
                                Venta de
                                repuestos y accesorios.</li>

                            <li> <i class="fa-regular fa-circle-check" style="color: #166534; margin-right:8px"></i>
                                Garantía
                                en todas nuestras reparaciones.</li>




                    </div>
                </div>
            </div>
        </div>
        <!-- container -->
    </section>

    <!--====== OUR SERVICES PART ENDS ======-->


    <!--====== BRANDS PART START ======-->
    <section>
        <div class="container">
            <div class="justify-center row ">
                <div class="w-full mx-4 lg:w-1/2">
                    <div class="pb-10 text-center section-title">
                        <h4 class="title" id="titleResponsive">Garantía de satisfacción</h4>
                        <p class="text">
                            Trabajamos con las mejores marcas del mercado, como LG, Samsung, Bosch, Whirlpool, General
                            Electric,
                            entre otras. Esto nos permite asegurar la calidad de nuestras reparaciones y la
                            disponibilidad de repuestos originales.
                        </p>
                    </div>
                    <!-- section title -->
                </div>
            </div>


            <div class="galeria2">
                <img src="files/images/gallery/logo-1.png" alt="logo marca 1">
                <img src="files/images/gallery/logo-2.png" alt="logo marca 2">
                <img src="files/images/gallery/logo-3.png" alt="logo marca 3">
                <img src="files/images/gallery/logo-4.png" alt="logo marca 4">
                <img src="files/images/gallery/logo-5.png" alt="logo marca 5">
                <img src="files/images/gallery/logo-6.png" alt="logo marca 7">
                <img src="files/images/gallery/logo-7.png" alt="logo marca 8">
                <img src="files/images/gallery/logo-9.png" alt="logo marca 9">
                <img src="files/images/gallery/logo-10.png" alt="logo marca 10">
                <img src="files/images/gallery/logo-11.png" class="logoge" alt="logo marca 11">
                <img src="files/images/gallery/logo-12.png" alt="logo marca 12">
                <img src="files/images/gallery/logo-13.png" alt="logo marca 13">
                <img src="files/images/gallery/logo-14.png" alt="logo marca 14">
                <img src="files/images/gallery/logo-15.png" alt="logo marca 15">
                <img src="files/images/gallery/logo-16.png" alt="logo marca 16">
                <img src="files/images/gallery/logo-17.png" alt="logo marca 17">
                <img src="files/images/gallery/logo-18.png" alt="logo marca 18">
                <img src="files/images/gallery/logo-19.png" alt="logo marca 19">
                <img src="files/images/gallery/logo-20.png" alt="logo marca 20">
                <img src="files/images/gallery/logo-21.png" alt="logo marca 21">
                <img src="files/images/gallery/logo-22.png" alt="logo marca 22">
                <img src="files/images/gallery/logo-23.png" alt="logo marca 23">
                <img src="files/images/gallery/logo-24.png" alt="logo marca 24">
                <img src="files/images/gallery/logo-25.png" alt="logo marca 25">
                <img src="files/images/gallery/logo-26.png" alt="logo marca 26">
                <img src="files/images/gallery/logo-27.png" alt="logo marca 27">
                <img src="files/images/gallery/logo-28.png" alt="logo marca 28">
                <img src="files/images/gallery/logo-29.png" alt="logo marca 29">
                <img src="files/images/gallery/logo-30.png" alt="logo marca 30">

                <img src="files/images/gallery/logo-32.png" alt="logo marca 32">
            </div>
        </div>
    </section>
    <style>
        .galeria2 {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        .galeria2 img {


            margin: 13px;

            width: 120px;
            height: 45px;
        }



        .galeria2 img:hover {
            border-color: #000;
        }
    </style>

    <!--====== BRANDS PART END ======-->
    <!--====== GALLERY PART START ======-->
    <section id="" class="testimonial-area py-120">
        <div class="container">
            <div class="justify-center row">
                <div class="w-full mx-4 lg:w-1/2">
                    <div class="pb-10 text-center section-title">
                        <h4 class="title" id="titleResponsive">Galería</h4>
                        <p class="text">
                            Desde la recepción hasta el área de trabajo, te mostramos donde se lleva a cabo la magia.
                            Conoce a los técnicos expertos que se encargarán de tu electrodoméstico.
                        </p>
                    </div>
                    <!-- section title -->
                </div>
            </div>
            <!-- row -->

            <div class="row justify-center mx-auto">
                <div class="w-full">
                    <div class="galeria">
                        <a href="https://www.google.com/maps/uv?pb=!1s0xc409571ff99c2a3%3A0xd01682bca0393f02!3m1!7e131!4s!5sServiSpin!15sCgIgAQ&hl=es&imagekey=!1e10!2sAF1QipOWoWrubP2JwHHRK6MpybXSc7oj5vibrICYrQDG"
                            target="_blank">
                            <img src="files/images/gallery/gallery-1.jpg" alt="imagen 1">
                        </a>

                        <a href="https://www.google.com/maps/uv?pb=!1s0xc409571ff99c2a3%3A0xd01682bca0393f02!3m1!7e131!4s!5sServiSpin!15sCgIgAQ&hl=es&imagekey=!1e10!2sAF1QipNrRdbPld5LVJfHUwWuxlnHJgJAnMU5yrb8IC0h"
                            target="_blank">
                            <img src="files/images/gallery/gallery-2.jpg" alt="imagen 2">
                        </a>
                        <a href="https://www.google.com/maps/uv?pb=!1s0xc409571ff99c2a3%3A0xd01682bca0393f02!3m1!7e131!4s!5sServiSpin!15sCgIgAQ&hl=es&imagekey=!1e10!2sAF1QipNdxqeySRg95GuHTwcukzp18MG5brKxOfCQcpzs"
                            target="_blank">
                            <img src="files/images/gallery/gallery-3.jpg" alt="imagen 3">
                        </a>

                        <a href="https://www.google.com/maps/uv?pb=!1s0xc409571ff99c2a3%3A0xd01682bca0393f02!3m1!7e131!4s!5sServiSpin!15sCgIgAQ&hl=es&imagekey=!1e10!2sAF1QipMI9qEVfR7QzgqcUs14MESQpnDWamjW0s0hIZJK"
                            target="_blank">
                            <img src="files/images/gallery/gallery-4.jpg" alt="imagen 4">
                        </a>
                        <a href="https://www.google.com/maps/uv?pb=!1s0xc409571ff99c2a3%3A0xd01682bca0393f02!3m1!7e131!4s!5sServiSpin!15sCgIgAQ&hl=es&imagekey=!1e10!2sAF1QipOCEyCE9bDlnQrluwgYUMym9Luq4hF0Rh1pDOuA"
                            target="_blank">
                            <img src="files/images/gallery/gallery-5.jpg" alt="imagen 5">
                        </a>
                    </div>
                    <style>
                        .galeria {
                            display: flex;
                            flex-wrap: wrap;
                            justify-content: center;
                            align-items: center;
                        }

                        .galeria img {
                            margin: 10px;
                            border: 1px solid #ddd;
                            border-radius: 10%;
                            width: 200px;
                            height: 200px;
                        }

                        .galeria img:hover {
                            border-color: #000;
                        }
                    </style>
                    <!-- row -->
                </div>
            </div>
            <!-- row -->
        </div>
        <!-- container -->
    </section>



    <!--====== GALLERY PART ENDS ======-->

    <!--=====  CALL TO ACTION PART START =====-->

    <section id="call-to-action" class="relative overflow-hidden bg-blue-600 call-to-action">
        <div class="absolute top-0 left-0 w-1/2 h-full call-action-image">
            <img src="files/images/call-to-action.png" alt="call-to-action" />
        </div>

        <div class="container-fluid">
            <div class="justify-end row">
                <div class="w-full lg:w-1/2">
                    <div class="py-32 mx-auto text-center call-action-content">
                        <h2 class="mb-5 text-5xl font-semibold leading-tight text-white">
                            No lo dejes para mañana
                        </h2>
                        <p class="mb-6 text-white">
                            Agenda tu cita y recibe asesoramiento experto.
                        </p>
                        @if ($companyData && $companyData->phone)
                            <ul class="flex flex-wrap justify-center">
                                <li>
                                    <a class="mx-3 main-btn gradient-btn"
                                        href="javascript:abrirWhatsApp('{{ $companyData->phone }}')">
                                        <i class="fa-brands fa-whatsapp" style="margin-right: 7px"></i>Whatsapp</a>
                                </li>
                                <li>
                                    <a class="mx-3 main-btn" href="tel:{{ $companyData->phone }}">Llamar Ahora <i
                                            class="fa-solid fa-phone ml-2"></i></a>
                                </li>
                            </ul>
                        @endif
                    </div>
                    <!-- slider-content -->
                </div>
            </div>
            <!-- row -->
        </div>
        <!-- container -->
    </section>
    <!--=====  END CALL TO ACTION PART START =====-->
    <!--====== WHY CHOOSE US PART START ======-->
    <br><br><br><br>
    <div class="container mx-auto " id="">
        <div class="flex flex-wrap">
            <div class="w-full md:w-1/2">
                <img src="files/images/choose-us.png" alt="Imagen" class="rounded">
            </div>
            <div class="w-full md:w-1/2 p-4">
                <h4 class="title" id="titleResponsive">¿Por qué elegirnos?</h4>
                <ul class="list-disc p-4 space-y-4" style="line-height: 33px">

                    <li> <i class="fa-regular fa-circle-check" style="color: #166534; margin-right:8px"></i>
                        Profesionalidad y
                        compromiso con la satisfacción del
                        cliente.</li>

                    <li> <i class="fa-regular fa-circle-check" style="color: #166534; margin-right:8px"></i> Atención
                        personalizada y
                        asesoramiento experto.</li>

                    <li> <i class="fa-regular fa-circle-check" style="color: #166534; margin-right:8px"></i> Precios
                        competitivos: Te
                        ofrecemos los mejores precios
                        del mercado sin sacrificar la calidad.
                    </li>
                    <li> <i class="fa-regular fa-circle-check" style="color: #166534; margin-right:8px"></i> Garantía
                        en todas nuestras
                        reparaciones: Puedes estar
                        seguro de que tu electrodoméstico estará
                        en buenas manos.</li>
                    <li> <i class="fa-regular fa-circle-check" style="color: #166534; margin-right:8px"></i> Uso de
                        las últimas
                        tecnologías: Estamos constantemente
                        actualizados con las últimas herramientas
                        y técnicas de reparación.</li>
                    <li> <i class="fa-regular fa-circle-check" style="color: #166534; margin-right:8px"></i> Respeto
                        por el medio
                        ambiente: Seguimos prácticas
                        responsables para minimizar nuestro impacto
                        ambiental.</li>

                </ul>




            </div>
        </div>
    </div>
    <!--====== WHY CHOOSE US PART ENDS ======-->
    <!--====== TESTIMONIAL THREE PART START ======-->

    <section id="testimonial" class="testimonial-area py-120">
        <div class="container">
            <div class="justify-center row">
                <div class="w-full mx-4 lg:w-1/2">
                    <div class="pb-10 text-center section-title">
                        <h4 class="title" id="titleResponsive">Testimonios</h4>
                        <p class="text">
                            La voz de nuestros clientes satisfechos
                        </p>
                    </div>
                    <!-- section title -->
                </div>
            </div>




            <script src="https://widget.trustmary.com/MHr-vVVpu"></script>

            <div class="items-center justify-center text-center">
                <div class="embedsocial-hashtag" data-ref="106f4e07bb24a96203d363bd2540e608e75bbf09"> <a
                        class="feed-powered-by-es feed-powered-by-es-badge-img"
                        href="https://embedsocial.com/blog/embed-google-reviews/" target="_blank"
                        title="Embed Google reviews">
                        <img src="https://embedsocial.com/cdn/images/embedsocial-icon.png" alt="EmbedSocial"> </a>
                </div>
                <script>
                    (function(d, s, id) {
                        var js;
                        if (d.getElementById(id)) {
                            return;
                        }
                        js = d.createElement(s);
                        js.id = id;
                        js.src = "https://embedsocial.com/cdn/ht.js";
                        d.getElementsByTagName("head")[0].appendChild(js);
                    }(document, "script", "EmbedSocialHashtagScript"));
                </script>
            </div>
        </div>
        <!-- container -->
    </section>
    <!--====== TESTIMONIAL THREE PART ENDS ======-->

    <!--====== LATEST POSTS START ======-->
    @livewire('latest-posts')
    <!--====== LATEST POSTS END ======-->




    <!--====== MAP START ======-->

    <section class=" bg-gray-100 client-logo-area">
        @if ($companyData && $companyData->address_google_map)
            <iframe src="{{ $companyData->address_google_map }}" width="100%" height="350" style="border:0;"
                allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        @else
            <p>Map could not be loaded.</p>
        @endif
    </section>

    <!--====== MAP PART ENDS ======-->


    <!--====== CONTACT PART START ======-->
    @include('contact.form')
    <!--====== CONTACT PART ENDS ======-->

    <!--====== CITIES START ======-->
    <div class="bg-white shadow-md rounded-md p-5 my-10 py-6">
        <h1 class="text-center font-bold text-2xl" style="font-size: 20px; padding:10px;">Nuestro Alcance: Ciudades de
            Gran Canaria</h1>
        <ul class="city-list">
            <li>Las Palmas de Gran Canaria</li>
            <li><a href="#">Telde</a></li>
            <li><a href="#">Santa Lucía de Tirajana</a></li>
            <li><a href="#">Arucas</a></li>
            <li><a href="#">San Bartolomé de Tirajana</a></li>
            <li><a href="#">Mogán</a></li>
            <li><a href="#">Guía de Gran Canaria</a></li>
            <li><a href="#">Agüimes</a></li>
            <li><a href="#">Ingenio</a></li>
            <li><a href="#">Gáldar</a></li>
            <li><a href="#">Santa Brígida</a></li>
            <li><a href="#">Teror</a></li>
            <li><a href="#">Valsequillo de Gran Canaria</a></li>
            <li><a href="#">Tejeda</a></li>
            <li><a href="#">Valleseco</a></li>
            <li><a href="#">Firgas</a></li>
            <li><a href="#">Moya</a></li>
            <li><a href="#">Artenara</a></li>
            <li><a href="#">La Aldea de San Nicolás</a></li>
        </ul>
    </div>
    <style>
        .city-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 0;
            margin: 0;
        }

        .city-list li {
            margin-right: 1rem;
            margin-bottom: 1rem;
            padding: 0.5rem 1rem;
            list-style: none;
            background-color: #f3f4f6;
            border-radius: 0.25rem;
        }

        @media (max-width: 768px) {
            .city-list li {
                margin-right: 1rem;
                margin-bottom: 1rem;
                padding: 0.5rem 1rem;
                list-style: none;
                background-color: #f3f4f6;
                border-radius: 0.25rem;
            }
        }
    </style>

    <!--====== CITIES END ======-->

    <!--====== FOOTER PART START ======-->

    <footer id="footer" class="bg-gray-100 footer-area">
        <div class="mb-16 footer-widget">
            <div class="container">
                <div class="row">
                    <div class="w-full">
                        <div class="items-end justify-between block mb-8 footer-logo-support md:flex">
                            <div class="flex items-end footer-logo">
                                <a class="mt-8" href="#home">
                                    @if ($companyData && $companyData->logo_path)
                                        <img src="{{ asset($companyData->logo_path) }}" style="width: 100px;"
                                            alt="Logo" />
                                    @else
                                        <img src="{{ asset('files/images/logo.png') }}" style="width: 100px;"
                                            alt="Logo" />
                                    @endif
                                </a>

                                <ul class="flex mt-8 ml-8 footer-social">
                                    @if ($companyData && $companyData->social_media_facebook)
                                        <li>
                                            <a href="{{ $companyData->social_media_facebook }}" target="_blank"
                                                rel="noopener noreferrer"><i class="lni-facebook-filled"></i></a>
                                        </li>
                                    @endif
                                    @if ($companyData && $companyData->social_media_twitter)
                                        <li>
                                            <a href="{{ $companyData->social_media_twitter }}" target="_blank"
                                                rel="noopener noreferrer"><i class="fa-brands fa-x-twitter"></i></a>
                                        </li>
                                    @endif
                                    @if ($companyData && $companyData->social_media_instagram)
                                        <li>
                                            <a href="{{ $companyData->social_media_instagram }}" target="_blank"
                                                rel="noopener noreferrer"><i class="lni-instagram-original"></i></a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                            <!-- footer logo -->
                        </div>
                        <!-- footer logo support -->
                    </div>
                </div>
                <!-- row -->
                <div class="row">
                    <div class="w-full sm:w-1/2 md:w-1/4 lg:w-1/6">
                        <div class="mb-8 footer-link">
                            <h6 class="footer-title">Compañia</h6>
                            <ul>
                                <li><a href="#about">Quiénes Somos</a></li>
                                <li><a href="#testimonial">Testimonios</a></li>

                            </ul>
                        </div>
                        <!-- footer link -->
                    </div>
                    <div class="w-full sm:w-1/2 md:w-1/3 lg:w-1/4">
                        <div class="mb-8 footer-link">
                            <h6 class="footer-title">Producto & Servicios</h6>
                            <ul>
                                <li><a href="#ourservices">Nuestros Servicios</a></li>

                            </ul>
                        </div>
                        <!-- footer link -->
                    </div>
                    <div class="w-full sm:w-5/12 md:w-1/3 lg:w-1/4">
                        <div class="mb-8 footer-link">
                            <h6 class="footer-title">Soporte</h6>
                            <ul>
                                <li><a class="page-scroll" href="#contact">Contacto</a></li>
                                <li><a target="_blank" href="{{ route('appointments.book') }}">Agendar
                                        Cita</a></li>

                            </ul>
                        </div>
                        <!-- footer link -->
                    </div>
                    <div class="w-full sm:w-7/12 md:w-1/2 lg:w-1/3">
                        <div class="mb-8 footer-newsletter">
                            <h6 class="footer-title">Horario de Apertura</h6>
                            <ul class="mb-8">
                                <li>Lun a Vie: 7:00 AM – 9:00 PM</li>
                                <li>Sáb: 9:00 AM – 9:00 PM</li>
                                <li>Dom: 9:00 AM – 8:00 PM</li>
                            </ul>
                            @if ($companyData && $companyData->phone)
                                <div class="flex flex-col gap-4 footer-buttons-container">
                                    <div>
                                        <a class="main-btn gradient-btn w-full text-center"
                                            href="javascript:abrirWhatsApp('{{ $companyData->phone }}')">
                                            <i class="fa-brands fa-whatsapp"
                                                style="margin-right: 7px"></i>Whatsapp</a>
                                    </div>
                                    <div>
                                        <a class="main-btn  w-full text-center"
                                            href="tel:{{ $companyData->phone }}"><i class="fa-solid fa-phone "></i>
                                            Llamar
                                            Ahora </a>
                                    </div>
                                </div>
                            @endif

                        </div>

                        <!-- footer newsletter -->
                    </div>
                </div>
                <!-- row -->
            </div>
            <!-- container -->
        </div>
        <!-- footer widget -->

        <div class="bg-blue-900 footer-copyright">
            <div class="container">
                <div class="row">
                    <div class="w-full">
                        <div class="py-6 text-center">
                            <p class="text-white">
                                © {{ date('Y') }}
                                @if ($companyData)
                                    <a class="text-blue-500 duration-300 hover:text-blue-700" rel="nofollow"
                                        href="#home">
                                        {{ $companyData->company_name }}
                                    </a>
                                @else
                                    SERVISPIN
                                @endif
                                . Todos los derechos
                                reservados.

                            </p>
                        </div>
                    </div>
                </div>
                <!-- row -->
            </div>
            <!-- container -->
        </div>
        <!-- footer copyright -->
    </footer>
    <!--====== FOOTER PART ENDS ======-->

    <style>
        .footer-buttons-container {
            display: flex;
            flex-direction: column;
            /* Default: Stack buttons vertically */
            gap: 0.75rem;
            /* Tailwind's gap-3 equivalent */
        }

        @media (min-width: 768px) {
            .footer-buttons-container {
                flex-direction: row;
                /*  Side by Side on larger screens*/
            }

            .footer-buttons-container a {
                width: auto;
                flex: 1;
                /* Equal width for buttons on larger screens*/
            }
        }
    </style>
    <!--====== FOOTER PART ENDS ======-->




    <!-- START JQUERY   -->
    <!--====== BACK TO TOP PART START ======-->

    <a class="back-to-top" href="#"><i class="lni-chevron-up"></i></a>

    <!--====== BACK TO TOP PART ENDS ======-->

    <!--====== jquery js ======-->
    <script src="files/js/vendor/modernizr-3.6.0.min.js"></script>
    <script src="files/js/vendor/jquery-1.12.4.min.js"></script>

    <!--====== Ajax Contact js ======-->
    <script src="files/js/ajax-contact.js"></script>

    <!--====== Scrolling Nav js ======-->
    <script src="files/js/jquery.easing.min.js"></script>
    <script src="files/js/scrolling-nav.js"></script>

    <!--====== Validator js ======-->
    <script src="files/js/validator.min.js"></script>

    <!--====== Magnific Popup js ======-->
    <script src="files/js/jquery.magnific-popup.min.js"></script>

    <!--====== Slick js ======-->
    <script src="files/js/slick.min.js"></script>

    <!--====== Main js ======-->
    <script src="files/js/main.js"></script>

    <script>
        function abrirWhatsApp(numero) {
            const cleanedNumber = numero.replace(/\+/g, '').replace(/\s/g, '');
            const url = `https://wa.me/${cleanedNumber}`;
            window.open(url, '_blank'); // Abre la ventana de WhatsApp en una pestaña nueva
        }
    </script>
    <!--END JQUERY  -->



</body>

</html>
