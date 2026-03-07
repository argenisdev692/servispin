<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Servicio a domicilio. La solución rápida y cómoda para tus averías" />
    <meta name="keywords"
        content="Las Palmas de Gran Canaria, Telde, Santa Lucía de Tirajana, Arucas, Gran Canaria, Islas Canarias, turismo, viaje, lugares de interés, vacaciones, turismo en Gran Canaria">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0EA5E9" />
    <link rel="apple-touch-icon" sizes="180x180" href="img/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="img/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="img/favicon/favicon-16x16.png">
    <link rel="manifest" href="img/favicon/site.webmanifest">

    <title>SERVISPIN | Servicio De Reparación De Lavadoras Y Secadoras a "DOMICILIO" en GRAN CANARIA!</title>

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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    <!--====== Slick css ======-->
    <link rel="stylesheet" href="files/css/slick.css" />

    <!--====== Line Icons css ======-->
    <link rel="stylesheet" href="files/css/LineIcons.css" />

    <!--====== Magnific Popup css ======-->
    <link rel="stylesheet" href="files/css/magnific-popup.css" />

    <!--====== tailwind css ======-->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

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
    * {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    body {
        overflow-x: hidden;
    }

    ::-webkit-scrollbar {
        width: 10px;
    }

    ::-webkit-scrollbar-track {
        background-color: #1e293b;
    }

    ::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #0ea5e9, #1e40af);
        border-radius: 10px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, #0284c7, #1e3a8a);
    }

    .glass-effect {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .glass-card:hover {
        transform: scale(1.02);
        box-shadow: 0 12px 40px 0 rgba(14, 165, 233, 0.3);
    }

    .gradient-bg {
        background: linear-gradient(135deg, #0ea5e9 0%, #1e40af 100%);
    }

    .gradient-text {
        background: linear-gradient(135deg, #0ea5e9, #1e40af);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .mesh-gradient {
        background: radial-gradient(at 0% 0%, rgba(14, 165, 233, 0.1) 0px, transparent 50%),
            radial-gradient(at 100% 100%, rgba(16, 185, 129, 0.1) 0px, transparent 50%);
    }

    .floating-animation {
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-20px);
        }
    }

    .pulse-glow {
        animation: pulse-glow 2s ease-in-out infinite;
    }

    @keyframes pulse-glow {
        0%, 100% {
            box-shadow: 0 0 20px rgba(14, 165, 233, 0.4);
        }
        50% {
            box-shadow: 0 0 40px rgba(14, 165, 233, 0.8);
        }
    }

    .navbar-sticky {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        transition: all 0.3s ease;
        background: transparent;
    }

    .navbar-sticky .nav-link {
        color: white;
        transition: color 0.3s ease;
    }

    .navbar-sticky.scrolled {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .navbar-sticky.scrolled .nav-link {
        color: #334155;
    }

    .navbar-sticky.scrolled .mobile-menu-icon {
        color: #334155;
    }

    .mobile-menu-icon {
        color: white;
        transition: color 0.3s ease;
    }

    .hover-card {
        position: relative;
        height: 400px;
        border-radius: 24px;
        overflow: hidden;
        cursor: pointer;
        transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
    }

    .hover-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(180deg, transparent 0%, rgba(0, 0, 0, 0.8) 100%);
        z-index: 1;
        transition: opacity 0.5s ease;
    }

    .hover-card:hover::before {
        opacity: 0.95;
        background: linear-gradient(180deg, rgba(14, 165, 233, 0.9) 0%, rgba(30, 64, 175, 0.95) 100%);
    }

    .hover-card-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s cubic-bezier(0.23, 1, 0.32, 1);
    }

    .hover-card:hover .hover-card-image {
        transform: scale(1.1);
    }

    .hover-card-content {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 32px;
        z-index: 2;
        transform: translateY(0);
        transition: transform 0.5s cubic-bezier(0.23, 1, 0.32, 1);
    }

    .hover-card:hover .hover-card-content {
        transform: translateY(-20px);
    }

    .hover-card-title {
        color: white;
        font-size: 28px;
        font-weight: 700;
        margin-bottom: 12px;
        transition: all 0.3s ease;
    }

    .hover-card-icon {
        font-size: 48px;
        color: white;
        margin-bottom: 16px;
        transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
    }

    .hover-card:hover .hover-card-icon {
        transform: scale(1.2) rotate(5deg);
    }

    .hover-card-description {
        color: white;
        opacity: 0;
        max-height: 0;
        overflow: hidden;
        transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
        line-height: 1.6;
    }

    .hover-card:hover .hover-card-description {
        opacity: 1;
        max-height: 200px;
        margin-bottom: 16px;
    }

    .hover-card-cta {
        display: inline-flex;
        align-items: center;
        color: white;
        font-weight: 600;
        opacity: 0;
        transform: translateX(-20px);
        transition: all 0.5s cubic-bezier(0.23, 1, 0.32, 1);
    }

    .hover-card:hover .hover-card-cta {
        opacity: 1;
        transform: translateX(0);
    }

    .hover-card-cta i {
        margin-left: 8px;
        transition: transform 0.3s ease;
    }

    .hover-card:hover .hover-card-cta i {
        transform: translateX(5px);
    }

    .services-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
    }

    @media (max-width: 1024px) {
        .services-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .hover-card {
            height: 350px;
        }
    }

    @media (max-width: 768px) {
        .services-grid {
            grid-template-columns: 1fr;
        }

        .hover-card {
            height: 320px;
        }

        .hover-card-title {
            font-size: 24px;
        }

        .hover-card-icon {
            font-size: 40px;
        }
    }

    .marquee {
        display: flex;
        overflow: hidden;
        user-select: none;
    }

    .marquee-content {
        flex-shrink: 0;
        display: flex;
        justify-content: space-around;
        min-width: 100%;
        animation: scroll 30s linear infinite;
    }

    @keyframes scroll {
        from {
            transform: translateX(0);
        }
        to {
            transform: translateX(-100%);
        }
    }

    .marquee-content:hover {
        animation-play-state: paused;
    }

    .brand-logo {
        filter: grayscale(100%);
        opacity: 0.6;
        transition: all 0.3s ease;
    }

    .brand-logo:hover {
        filter: grayscale(0%);
        opacity: 1;
        transform: scale(1.1);
    }

    .masonry-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }

    .masonry-item img {
        width: 100%;
        height: 250px;
        object-fit: cover;
    }

    @media (max-width: 1024px) {
        .masonry-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .masonry-grid {
            grid-template-columns: 1fr;
        }

        .masonry-item img {
            height: 200px;
        }
    }

    .service-icon {
        font-size: 56px;
        color: #0ea5e9;
        transition: all 0.3s ease;
        display: block;
    }

    .glass-card:hover .service-icon {
        transform: translateY(-5px) scale(1.05);
        color: #0284c7;
    }

    .btn-primary {
        background: linear-gradient(135deg, #0ea5e9 0%, #1e40af 100%);
        color: white;
        padding: 16px 32px;
        border-radius: 9999px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-block;
        text-align: center;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(14, 165, 233, 0.4);
    }

    .btn-secondary {
        background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
        color: white;
        padding: 16px 32px;
        border-radius: 9999px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-block;
        text-align: center;
    }

    .btn-secondary:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
    }

    .whatsapp-float {
        position: fixed;
        bottom: 24px;
        right: 24px;
        width: 64px;
        height: 64px;
        background: linear-gradient(135deg, #10b981, #34d399);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);
        transition: all 0.3s ease;
        animation: pulse 2s infinite;
    }

    .whatsapp-float:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 30px rgba(16, 185, 129, 0.6);
    }

    @keyframes pulse {
        0%, 100% {
            box-shadow: 0 4px 20px rgba(16, 185, 129, 0.4);
        }
        50% {
            box-shadow: 0 4px 30px rgba(16, 185, 129, 0.7);
        }
    }

    .hero-video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: 0;
    }

    .hero-background {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(14, 165, 233, 0.85) 0%, rgba(30, 64, 175, 0.85) 100%);
        z-index: 1;
    }

    @media (max-width: 768px) {
        .hero-video {
            display: none;
        }
        
        .hero-background {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.95) 0%, rgba(30, 64, 175, 0.95) 100%);
        }
    }

    .hero-content {
        position: relative;
        z-index: 2;
    }

    .animated-gradient-text {
        background: linear-gradient(
            90deg,
            #ffffff 0%,
            #e0f2fe 25%,
            #bae6fd 50%,
            #e0f2fe 75%,
            #ffffff 100%
        );
        background-size: 200% auto;
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: gradient-animation 4s ease infinite;
        font-weight: 800;
    }

    @keyframes gradient-animation {
        0%, 100% {
            background-position: 0% center;
        }
        50% {
            background-position: 100% center;
        }
    }

    .fade-in-up {
        animation: fadeInUp 1s ease-out forwards;
        opacity: 0;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .subtitle-fade {
        animation: fadeIn 1.2s ease-out 0.3s forwards;
        opacity: 0;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    .badges-fade {
        animation: fadeIn 1.5s ease-out 0.6s forwards;
        opacity: 0;
    }

    .buttons-fade {
        animation: fadeInUp 1.8s ease-out 0.9s forwards;
        opacity: 0;
    }
</style>

<body class="antialiased bg-slate-50">
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PHXST37L" height="0" width="0"
            style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

    <!--====== NAVBAR START ======-->
    <nav class="navbar-sticky" id="navbar">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex justify-between items-center">
                <a href="#home" class="flex items-center">
                    @if ($companyData && $companyData->logo_path)
                        <img src="{{ asset($companyData->logo_path) }}" class="h-12 w-auto" alt="SERVISPIN Logo" />
                    @else
                        <img src="files/images/logo.png" class="h-12 w-auto" alt="SERVISPIN Logo" />
                    @endif
                </a>

                <div class="hidden md:flex items-center space-x-8">
                    <a href="#home" class="nav-link font-medium transition-colors">Inicio</a>
                    <a href="#ourservices" class="nav-link font-medium transition-colors">Servicios</a>
                    <a href="#testimonial" class="nav-link font-medium transition-colors">Testimonios</a>
                    <a href="#contact" class="nav-link font-medium transition-colors">Contacto</a>
                    <a href="{{ route('appointments.book') }}" target="_blank" class="btn-secondary text-sm px-6 py-3">
                        <i class="fa-solid fa-calendar-days mr-2"></i>Agendar Cita
                    </a>
                </div>

                <button class="md:hidden mobile-menu-icon" id="mobile-menu-btn">
                    <i class="fa-solid fa-bars text-2xl"></i>
                </button>
            </div>

            <div class="hidden md:hidden mt-4" id="mobile-menu">
                <div class="flex flex-col space-y-3">
                    <a href="#home" class="text-slate-700 hover:text-cyan-500 font-medium transition-colors py-2">Inicio</a>
                    <a href="#ourservices" class="text-slate-700 hover:text-cyan-500 font-medium transition-colors py-2">Servicios</a>
                    <a href="#testimonial" class="text-slate-700 hover:text-cyan-500 font-medium transition-colors py-2">Testimonios</a>
                    <a href="#contact" class="text-slate-700 hover:text-cyan-500 font-medium transition-colors py-2">Contacto</a>
                    <a href="{{ route('appointments.book') }}" target="_blank" class="btn-secondary text-sm text-center">
                        <i class="fa-solid fa-calendar-days mr-2"></i>Agendar Cita
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <!--====== NAVBAR END ======-->

    <!--====== HERO SECTION START ======-->
    <section id="home" class="relative min-h-screen flex items-center justify-center overflow-hidden">
        <!-- Video Background -->
        <video autoplay muted loop playsinline class="hero-video">
            <source src="files/images/video-hero.mp4" type="video/mp4">
            Tu navegador no soporta videos HTML5.
        </video>
        
        <!-- Overlay Gradient -->
        <div class="hero-background"></div>
        
        <div class="hero-content container mx-auto px-4 py-32 text-center">
            <div class="glass-effect max-w-4xl mx-auto rounded-3xl p-8 md:p-12">
                @if ($companyData && $companyData->logo_path)
                    <img src="{{ asset($companyData->logo_path) }}" class="h-16 w-auto mx-auto mb-8 fade-in-up" alt="SERVISPIN" />
                @else
                    <img src="files/images/logo.png" class="h-16 w-auto mx-auto mb-8 fade-in-up" alt="SERVISPIN" />
                @endif
                
                <h1 class="text-4xl md:text-6xl font-extrabold mb-6 leading-tight animated-gradient-text fade-in-up">
                    Reparación de Electrodomésticos en Gran Canaria
                </h1>
                
                <p class="text-lg md:text-xl text-white/90 mb-4 subtitle-fade">
                    Servicio técnico profesional a domicilio. 24/7 disponible
                </p>
                
                <div class="flex flex-wrap justify-center gap-4 text-sm md:text-base text-white/80 mb-8 badges-fade">
                    <span><i class="fa-solid fa-medal mr-2 text-amber-400"></i>10 años experiencia</span>
                    <span><i class="fa-solid fa-users mr-2 text-emerald-400"></i>200+ clientes satisfechos</span>
                    <span><i class="fa-solid fa-shield-check mr-2 text-cyan-400"></i>Garantía total</span>
                </div>

                @if ($companyData && $companyData->phone)
                    <div class="flex flex-col sm:flex-row gap-4 justify-center items-center buttons-fade">
                        <a href="javascript:abrirWhatsApp('+34643940970')" class="btn-secondary w-full sm:w-auto">
                            <i class="fa-brands fa-whatsapp mr-2"></i>Contactar por WhatsApp
                        </a>
                        <a href="{{ route('appointments.book') }}" target="_blank" class="bg-white text-cyan-600 hover:bg-slate-100 px-8 py-4 rounded-full font-semibold transition-all w-full sm:w-auto">
                            <i class="fa-solid fa-calendar-days mr-2"></i>Agendar Cita Ahora
                        </a>
                        <a href="tel:+34643940970" class="bg-transparent border-2 border-white text-white hover:bg-white hover:text-cyan-600 px-8 py-4 rounded-full font-semibold transition-all w-full sm:w-auto">
                            <i class="fa-solid fa-phone mr-2"></i>Llamar Ahora
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </section>
    <!--====== HERO SECTION END ======-->

    <!--====== SERVICES BENTO GRID START ======-->
    <section id="ourservices" class="py-24 bg-slate-50 mesh-gradient">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-bold text-slate-900 mb-4">
                    Nuestros Servicios
                </h2>
                <p class="text-lg md:text-xl text-slate-600 max-w-3xl mx-auto">
                    Tu electrodoméstico en las mejores manos. Servicio profesional y garantizado.
                </p>
            </div>

            <div class="services-grid">
                <!-- Lavadoras -->
                <div class="hover-card">
                    <img src="https://images.unsplash.com/photo-1626806787461-102c1bfaaea1?w=800&q=80" 
                         alt="Reparación de Lavadoras" 
                         class="hover-card-image" />
                    <div class="hover-card-content">
                        <i class="fa-solid fa-power-off hover-card-icon"></i>
                        <h3 class="hover-card-title">Lavadoras</h3>
                        <p class="hover-card-description">
                            Reparación, mantenimiento y limpieza profesional. Repuestos originales garantizados. Servicio rápido y eficiente a domicilio.
                        </p>
                        <a href="#contact" class="hover-card-cta">
                            Ver más <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Aire Acondicionado -->
                <div class="hover-card">
                    <img src="files/images/aire-acondicionados.png" 
                         alt="Aire Acondicionado" 
                         class="hover-card-image" />
                    <div class="hover-card-content">
                        <i class="fa-solid fa-fan hover-card-icon"></i>
                        <h3 class="hover-card-title">Aire Acondicionado</h3>
                        <p class="hover-card-description">
                            Venta, instalación y mantenimiento. Industriales y residenciales. Servicio especializado con garantía total.
                        </p>
                        <a href="#contact" class="hover-card-cta">
                            Ver más <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Lavavajillas -->
                <div class="hover-card">
                    <img src="files/images/lavavajillas.png" 
                         alt="Reparación de Lavavajillas" 
                         class="hover-card-image" />
                    <div class="hover-card-content">
                        <i class="fa-solid fa-clock-rotate-left hover-card-icon"></i>
                        <h3 class="hover-card-title">Lavavajillas</h3>
                        <p class="hover-card-description">
                            Reparación experta, limpieza profunda y sustitución de piezas. ¡Tu lavavajillas como nuevo!
                        </p>
                        <a href="#contact" class="hover-card-cta">
                            Ver más <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Secadoras -->
                <div class="hover-card">
                    <img src="files/images/secadoras.png" 
                         alt="Reparación de Secadoras" 
                         class="hover-card-image" />
                    <div class="hover-card-content">
                        <i class="fa-regular fa-sun hover-card-icon"></i>
                        <h3 class="hover-card-title">Secadoras</h3>
                        <p class="hover-card-description">
                            Extendemos la vida de tu secadora. Mantenimiento preventivo y reparaciones garantizadas.
                        </p>
                        <a href="#contact" class="hover-card-cta">
                            Ver más <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Neveras de Hostelería -->
                <div class="hover-card">
                    <img src="files/images/neveras-hosteleria.png" 
                         alt="Neveras de Hostelería" 
                         class="hover-card-image" />
                    <div class="hover-card-content">
                        <i class="fa-solid fa-snowflake hover-card-icon"></i>
                        <h3 class="hover-card-title">Neveras de Hostelería</h3>
                        <p class="hover-card-description">
                            Técnicos especializados con herramientas y repuestos para cualquier reparación comercial.
                        </p>
                        <a href="#contact" class="hover-card-cta">
                            Ver más <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Televisores -->
                <div class="hover-card">
                    <img src="https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?w=800&q=80" 
                         alt="Televisores" 
                         class="hover-card-image" />
                    <div class="hover-card-content">
                        <i class="fa-solid fa-tv hover-card-icon"></i>
                        <h3 class="hover-card-title">Televisores OLED, LED, LCD</h3>
                        <p class="hover-card-description">
                            Instalación, configuración, reparación de tiras LED y más. Servicio técnico especializado.
                        </p>
                        <a href="#contact" class="hover-card-cta">
                            Ver más <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--====== SERVICES BENTO GRID END ======-->

    <!--====== ADDITIONAL SERVICES START ======-->
    <section class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <img src="files/images/services10.png" alt="Servicios Adicionales" class="rounded-2xl shadow-2xl" />
                </div>
                <div>
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-6">
                        Tu aliado para soluciones personalizadas
                    </h2>
                    <p class="text-lg text-slate-600 mb-8">
                        No importa cuál sea el problema, nuestro equipo de expertos técnicos está altamente capacitado para diagnosticar y reparar cualquier avería de forma rápida y eficiente.
                    </p>
                    <ul class="space-y-4">
                        <li class="flex items-start">
                            <i class="fa-regular fa-circle-check text-emerald-600 text-xl mr-3 mt-1"></i>
                            <span class="text-slate-700">Instalación de Electrodomésticos</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-regular fa-circle-check text-emerald-600 text-xl mr-3 mt-1"></i>
                            <span class="text-slate-700">Diagnóstico preciso de averías</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-regular fa-circle-check text-emerald-600 text-xl mr-3 mt-1"></i>
                            <span class="text-slate-700">Reparación de todas las marcas de lavadoras, secadoras y lavavajillas</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-regular fa-circle-check text-emerald-600 text-xl mr-3 mt-1"></i>
                            <span class="text-slate-700">Uso de repuestos originales y de alta calidad</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-regular fa-circle-check text-emerald-600 text-xl mr-3 mt-1"></i>
                            <span class="text-slate-700">Servicio técnico a domicilio en Gran Canaria</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-regular fa-circle-check text-emerald-600 text-xl mr-3 mt-1"></i>
                            <span class="text-slate-700">Venta de repuestos y accesorios</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-regular fa-circle-check text-emerald-600 text-xl mr-3 mt-1"></i>
                            <span class="text-slate-700">Garantía en todas nuestras reparaciones</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    <!--====== ADDITIONAL SERVICES END ======-->

    <!--====== BRANDS MARQUEE START ======-->
    <section class="py-24 bg-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-12">
            <div class="text-center">
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">
                    Trabajamos con las mejores marcas
                </h2>
                <p class="text-lg text-slate-600 max-w-2xl mx-auto">
                    Garantía de satisfacción con repuestos originales de las marcas líderes del mercado.
                </p>
            </div>
        </div>

        <div class="marquee py-8">
            <div class="marquee-content">
                <img src="files/images/gallery/logo-1.png" alt="Marca 1" class="brand-logo h-12 mx-8" />
                <img src="files/images/gallery/logo-2.png" alt="Marca 2" class="brand-logo h-12 mx-8" />
                <img src="files/images/gallery/logo-3.png" alt="Marca 3" class="brand-logo h-12 mx-8" />
                <img src="files/images/gallery/logo-4.png" alt="Marca 4" class="brand-logo h-12 mx-8" />
                <img src="files/images/gallery/logo-5.png" alt="Marca 5" class="brand-logo h-12 mx-8" />
                <img src="files/images/gallery/logo-6.png" alt="Marca 6" class="brand-logo h-12 mx-8" />
                <img src="files/images/gallery/logo-7.png" alt="Marca 7" class="brand-logo h-12 mx-8" />
                <img src="files/images/gallery/logo-9.png" alt="Marca 8" class="brand-logo h-12 mx-8" />
                <img src="files/images/gallery/logo-10.png" alt="Marca 9" class="brand-logo h-12 mx-8" />
                <img src="files/images/gallery/logo-11.png" alt="Marca 10" class="brand-logo h-12 mx-8" />
                <img src="files/images/gallery/logo-12.png" alt="Marca 11" class="brand-logo h-12 mx-8" />
                <img src="files/images/gallery/logo-13.png" alt="Marca 12" class="brand-logo h-12 mx-8" />
            </div>
            <div class="marquee-content" aria-hidden="true">
                <img src="files/images/gallery/logo-1.png" alt="Marca 1" class="brand-logo h-12 mx-8" />
                <img src="files/images/gallery/logo-2.png" alt="Marca 2" class="brand-logo h-12 mx-8" />
                <img src="files/images/gallery/logo-3.png" alt="Marca 3" class="brand-logo h-12 mx-8" />
                <img src="files/images/gallery/logo-4.png" alt="Marca 4" class="brand-logo h-12 mx-8" />
                <img src="files/images/gallery/logo-5.png" alt="Marca 5" class="brand-logo h-12 mx-8" />
                <img src="files/images/gallery/logo-6.png" alt="Marca 6" class="brand-logo h-12 mx-8" />
                <img src="files/images/gallery/logo-7.png" alt="Marca 7" class="brand-logo h-12 mx-8" />
                <img src="files/images/gallery/logo-9.png" alt="Marca 8" class="brand-logo h-12 mx-8" />
                <img src="files/images/gallery/logo-10.png" alt="Marca 9" class="brand-logo h-12 mx-8" />
                <img src="files/images/gallery/logo-11.png" alt="Marca 10" class="brand-logo h-12 mx-8" />
                <img src="files/images/gallery/logo-12.png" alt="Marca 11" class="brand-logo h-12 mx-8" />
                <img src="files/images/gallery/logo-13.png" alt="Marca 12" class="brand-logo h-12 mx-8" />
            </div>
        </div>
    </section>
    <!--====== BRANDS MARQUEE END ======-->

    <!--====== WHY CHOOSE US START ======-->
    <section class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div class="order-2 md:order-1">
                    <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-6">
                        ¿Por qué elegirnos?
                    </h2>
                    <ul class="space-y-4">
                        <li class="flex items-start">
                            <i class="fa-regular fa-circle-check text-emerald-600 text-xl mr-3 mt-1"></i>
                            <span class="text-slate-700">Profesionalidad y compromiso con la satisfacción del cliente</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-regular fa-circle-check text-emerald-600 text-xl mr-3 mt-1"></i>
                            <span class="text-slate-700">Atención personalizada y asesoramiento experto</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-regular fa-circle-check text-emerald-600 text-xl mr-3 mt-1"></i>
                            <span class="text-slate-700">Precios competitivos sin sacrificar la calidad</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-regular fa-circle-check text-emerald-600 text-xl mr-3 mt-1"></i>
                            <span class="text-slate-700">Garantía en todas nuestras reparaciones</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-regular fa-circle-check text-emerald-600 text-xl mr-3 mt-1"></i>
                            <span class="text-slate-700">Uso de las últimas tecnologías y técnicas de reparación</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fa-regular fa-circle-check text-emerald-600 text-xl mr-3 mt-1"></i>
                            <span class="text-slate-700">Respeto por el medio ambiente con prácticas responsables</span>
                        </li>
                    </ul>
                </div>
                <div class="order-1 md:order-2">
                    <img src="files/images/choose-us.png" alt="Por qué elegirnos" class="rounded-2xl shadow-2xl" />
                </div>
            </div>
        </div>
    </section>
    <!--====== WHY CHOOSE US END ======-->

    <!--====== GALLERY MASONRY START ======-->
    <section class="py-24 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">
                    Galería
                </h2>
                <p class="text-lg text-slate-600 max-w-2xl mx-auto">
                    Desde la recepción hasta el área de trabajo, te mostramos donde se lleva a cabo la magia. Conoce a los técnicos expertos que se encargarán de tu electrodoméstico.
                </p>
            </div>

            <div class="masonry-grid">
                <div class="masonry-item">
                    <a href="https://www.google.com/maps/uv?pb=!1s0xc409571ff99c2a3%3A0xd01682bca0393f02!3m1!7e131!4s!5sServiSpin!15sCgIgAQ&hl=es&imagekey=!1e10!2sAF1QipOWoWrubP2JwHHRK6MpybXSc7oj5vibrICYrQDG" target="_blank" class="block overflow-hidden rounded-2xl shadow-lg hover:shadow-2xl transition-all">
                        <img src="files/images/gallery/gallery-1.jpg" alt="Galería 1" class="w-full h-auto hover:scale-105 transition-transform duration-300" />
                    </a>
                </div>
                <div class="masonry-item">
                    <a href="https://www.google.com/maps/uv?pb=!1s0xc409571ff99c2a3%3A0xd01682bca0393f02!3m1!7e131!4s!5sServiSpin!15sCgIgAQ&hl=es&imagekey=!1e10!2sAF1QipNrRdbPld5LVJfHUwWuxlnHJgJAnMU5yrb8IC0h" target="_blank" class="block overflow-hidden rounded-2xl shadow-lg hover:shadow-2xl transition-all">
                        <img src="files/images/gallery/gallery-2.jpg" alt="Galería 2" class="w-full h-auto hover:scale-105 transition-transform duration-300" />
                    </a>
                </div>
                <div class="masonry-item">
                    <a href="https://www.google.com/maps/uv?pb=!1s0xc409571ff99c2a3%3A0xd01682bca0393f02!3m1!7e131!4s!5sServiSpin!15sCgIgAQ&hl=es&imagekey=!1e10!2sAF1QipNdxqeySRg95GuHTwcukzp18MG5brKxOfCQcpzs" target="_blank" class="block overflow-hidden rounded-2xl shadow-lg hover:shadow-2xl transition-all">
                        <img src="files/images/gallery/gallery-3.jpg" alt="Galería 3" class="w-full h-auto hover:scale-105 transition-transform duration-300" />
                    </a>
                </div>
                <div class="masonry-item">
                    <a href="https://www.google.com/maps/uv?pb=!1s0xc409571ff99c2a3%3A0xd01682bca0393f02!3m1!7e131!4s!5sServiSpin!15sCgIgAQ&hl=es&imagekey=!1e10!2sAF1QipMI9qEVfR7QzgqcUs14MESQpnDWamjW0s0hIZJK" target="_blank" class="block overflow-hidden rounded-2xl shadow-lg hover:shadow-2xl transition-all">
                        <img src="files/images/gallery/gallery-4.jpg" alt="Galería 4" class="w-full h-auto hover:scale-105 transition-transform duration-300" />
                    </a>
                </div>
                <div class="masonry-item">
                    <a href="https://www.google.com/maps/uv?pb=!1s0xc409571ff99c2a3%3A0xd01682bca0393f02!3m1!7e131!4s!5sServiSpin!15sCgIgAQ&hl=es&imagekey=!1e10!2sAF1QipOCEyCE9bDlnQrluwgYUMym9Luq4hF0Rh1pDOuA" target="_blank" class="block overflow-hidden rounded-2xl shadow-lg hover:shadow-2xl transition-all">
                        <img src="files/images/gallery/gallery-5.jpg" alt="Galería 5" class="w-full h-auto hover:scale-105 transition-transform duration-300" />
                    </a>
                </div>
            </div>
        </div>
    </section>
    <!--====== GALLERY MASONRY END ======-->

    <!--====== CTA SECTION START ======-->
    <section class="py-24 gradient-bg relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <img src="files/images/call-to-action.png" alt="Background" class="w-full h-full object-cover" />
        </div>
        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl md:text-5xl font-bold text-white mb-6">
                No lo dejes para mañana
            </h2>
            <p class="text-xl text-white/90 mb-8">
                Agenda tu cita y recibe asesoramiento experto. Tu electrodoméstico necesita atención profesional.
            </p>
            @if ($companyData && $companyData->phone)
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="javascript:abrirWhatsApp('+34643940970')" class="btn-secondary w-full sm:w-auto">
                        <i class="fa-brands fa-whatsapp mr-2"></i>WhatsApp
                    </a>
                    <a href="tel:+34643940970" class="bg-white text-cyan-600 hover:bg-slate-100 px-8 py-4 rounded-full font-semibold transition-all w-full sm:w-auto">
                        <i class="fa-solid fa-phone mr-2"></i>Llamar Ahora
                    </a>
                    <a href="{{ route('appointments.book') }}" target="_blank" class="bg-transparent border-2 border-white text-white hover:bg-white hover:text-cyan-600 px-8 py-4 rounded-full font-semibold transition-all w-full sm:w-auto">
                        <i class="fa-solid fa-calendar-days mr-2"></i>Agendar Cita
                    </a>
                </div>
            @endif
        </div>
    </section>
    <!--====== CTA SECTION END ======-->

    <!--====== TESTIMONIAL SECTION START ======-->
    <section id="testimonial" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-slate-900 mb-4">
                    Testimonios
                </h2>
                <p class="text-lg text-slate-600">
                    La voz de nuestros clientes satisfechos
                </p>
            </div>

            <script src="https://widget.trustmary.com/MHr-vVVpu"></script>

            <div class="items-center justify-center text-center mt-12">
                <div class="embedsocial-hashtag" data-ref="106f4e07bb24a96203d363bd2540e608e75bbf09">
                    <a class="feed-powered-by-es feed-powered-by-es-badge-img"
                        href="https://embedsocial.com/blog/embed-google-reviews/" target="_blank"
                        title="Embed Google reviews">
                        <img src="https://embedsocial.com/cdn/images/embedsocial-icon.png" alt="EmbedSocial">
                    </a>
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
    </section>
    <!--====== TESTIMONIAL SECTION END ======-->

    <!--====== LATEST POSTS START ======-->
    @livewire('latest-posts')
    <!--====== LATEST POSTS END ======-->

    <!--====== SERVICE AREA INFO START ======-->
    <section class="py-16 bg-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold text-slate-900 mb-2">Servicio solo a domicilio</h2>
            <p class="text-slate-600">Cobertura en toda Gran Canaria</p>
        </div>
    </section>
    <!--====== SERVICE AREA INFO END ======-->

    <!--====== CONTACT FORM START ======-->
    @include('contact.form')
    <!--====== CONTACT FORM END ======-->

    <!--====== CITIES START ======-->
    <section class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl md:text-3xl font-bold text-center text-slate-900 mb-8">
                Nuestro Alcance: Ciudades de Gran Canaria
            </h2>
            <div class="flex flex-wrap justify-center gap-3">
                <span class="px-4 py-2 bg-slate-100 rounded-lg text-slate-700 hover:bg-cyan-100 hover:text-cyan-700 transition-colors">Las Palmas de Gran Canaria</span>
                <span class="px-4 py-2 bg-slate-100 rounded-lg text-slate-700 hover:bg-cyan-100 hover:text-cyan-700 transition-colors">Telde</span>
                <span class="px-4 py-2 bg-slate-100 rounded-lg text-slate-700 hover:bg-cyan-100 hover:text-cyan-700 transition-colors">Santa Lucía de Tirajana</span>
                <span class="px-4 py-2 bg-slate-100 rounded-lg text-slate-700 hover:bg-cyan-100 hover:text-cyan-700 transition-colors">Arucas</span>
                <span class="px-4 py-2 bg-slate-100 rounded-lg text-slate-700 hover:bg-cyan-100 hover:text-cyan-700 transition-colors">San Bartolomé de Tirajana</span>
                <span class="px-4 py-2 bg-slate-100 rounded-lg text-slate-700 hover:bg-cyan-100 hover:text-cyan-700 transition-colors">Mogán</span>
                <span class="px-4 py-2 bg-slate-100 rounded-lg text-slate-700 hover:bg-cyan-100 hover:text-cyan-700 transition-colors">Guía de Gran Canaria</span>
                <span class="px-4 py-2 bg-slate-100 rounded-lg text-slate-700 hover:bg-cyan-100 hover:text-cyan-700 transition-colors">Agüimes</span>
                <span class="px-4 py-2 bg-slate-100 rounded-lg text-slate-700 hover:bg-cyan-100 hover:text-cyan-700 transition-colors">Ingenio</span>
                <span class="px-4 py-2 bg-slate-100 rounded-lg text-slate-700 hover:bg-cyan-100 hover:text-cyan-700 transition-colors">Gáldar</span>
                <span class="px-4 py-2 bg-slate-100 rounded-lg text-slate-700 hover:bg-cyan-100 hover:text-cyan-700 transition-colors">Santa Brígida</span>
                <span class="px-4 py-2 bg-slate-100 rounded-lg text-slate-700 hover:bg-cyan-100 hover:text-cyan-700 transition-colors">Teror</span>
                <span class="px-4 py-2 bg-slate-100 rounded-lg text-slate-700 hover:bg-cyan-100 hover:text-cyan-700 transition-colors">Valsequillo de Gran Canaria</span>
                <span class="px-4 py-2 bg-slate-100 rounded-lg text-slate-700 hover:bg-cyan-100 hover:text-cyan-700 transition-colors">Tejeda</span>
                <span class="px-4 py-2 bg-slate-100 rounded-lg text-slate-700 hover:bg-cyan-100 hover:text-cyan-700 transition-colors">Valleseco</span>
                <span class="px-4 py-2 bg-slate-100 rounded-lg text-slate-700 hover:bg-cyan-100 hover:text-cyan-700 transition-colors">Firgas</span>
                <span class="px-4 py-2 bg-slate-100 rounded-lg text-slate-700 hover:bg-cyan-100 hover:text-cyan-700 transition-colors">Moya</span>
                <span class="px-4 py-2 bg-slate-100 rounded-lg text-slate-700 hover:bg-cyan-100 hover:text-cyan-700 transition-colors">Artenara</span>
                <span class="px-4 py-2 bg-slate-100 rounded-lg text-slate-700 hover:bg-cyan-100 hover:text-cyan-700 transition-colors">La Aldea de San Nicolás</span>
            </div>
        </div>
    </section>
    <!--====== CITIES END ======-->

    <!--====== FOOTER START ======-->
    <footer class="bg-slate-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
                <div>
                    <a href="#home" class="inline-block mb-4">
                        @if ($companyData && $companyData->logo_path)
                            <img src="{{ asset($companyData->logo_path) }}" class="h-12 w-auto" alt="SERVISPIN" />
                        @else
                            <img src="files/images/logo.png" class="h-12 w-auto" alt="SERVISPIN" />
                        @endif
                    </a>
                    <p class="text-slate-400 mb-4">
                        Servicio técnico profesional de electrodomésticos en Gran Canaria.
                    </p>
                    <div class="flex space-x-4">
                        @if ($companyData && $companyData->social_media_facebook)
                            <a href="{{ $companyData->social_media_facebook }}" target="_blank" class="text-slate-400 hover:text-cyan-400 transition-colors text-xl">
                                <i class="lni-facebook-filled"></i>
                            </a>
                        @endif
                        @if ($companyData && $companyData->social_media_twitter)
                            <a href="{{ $companyData->social_media_twitter }}" target="_blank" class="text-slate-400 hover:text-cyan-400 transition-colors text-xl">
                                <i class="fa-brands fa-x-twitter"></i>
                            </a>
                        @endif
                        @if ($companyData && $companyData->social_media_instagram)
                            <a href="{{ $companyData->social_media_instagram }}" target="_blank" class="text-slate-400 hover:text-cyan-400 transition-colors text-xl">
                                <i class="lni-instagram-original"></i>
                            </a>
                        @endif
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-4">Compañía</h3>
                    <ul class="space-y-2">
                        <li><a href="#home" class="text-slate-400 hover:text-white transition-colors">Inicio</a></li>
                        <li><a href="#ourservices" class="text-slate-400 hover:text-white transition-colors">Servicios</a></li>
                        <li><a href="#testimonial" class="text-slate-400 hover:text-white transition-colors">Testimonios</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-4">Contacto</h3>
                    <ul class="space-y-2">
                        <li><a href="#contact" class="text-slate-400 hover:text-white transition-colors">Contacto</a></li>
                        <li><a href="{{ route('appointments.book') }}" target="_blank" class="text-slate-400 hover:text-white transition-colors">Agendar Cita</a></li>
                        <li><a href="tel:+34643940970" class="text-slate-400 hover:text-white transition-colors">Llamar Ahora</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-4">Horario de Apertura</h3>
                    <ul class="space-y-2 text-slate-400">
                        <li>Lun a Vie: 7:00 AM – 9:00 PM</li>
                        <li>Sáb: 9:00 AM – 9:00 PM</li>
                        <li>Dom: 9:00 AM – 8:00 PM</li>
                        <li class="mt-4">
                            <span class="inline-block px-3 py-1 bg-emerald-600 text-white rounded-full text-sm">
                                Emergencias 24/7
                            </span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-slate-800 mt-12 pt-8 text-center text-slate-400">
                <p>
                    © {{ date('Y') }}
                    @if ($companyData)
                        <a href="#home" class="text-cyan-400 hover:text-cyan-300 transition-colors">
                            {{ $companyData->company_name }}
                        </a>
                    @else
                        SERVISPIN
                    @endif
                    . Todos los derechos reservados.
                </p>
            </div>
        </div>
    </footer>
    <!--====== FOOTER END ======-->

    <!--====== WHATSAPP FLOATING BUTTON ======-->
    @if ($companyData && $companyData->phone)
        <a href="javascript:abrirWhatsApp('+34643940970')" class="whatsapp-float" title="Contactar por WhatsApp">
            <i class="fa-brands fa-whatsapp text-white text-3xl"></i>
        </a>
    @endif

    <!--====== BACK TO TOP ======-->
    <a href="#home" class="fixed bottom-24 right-6 w-12 h-12 bg-slate-700 hover:bg-cyan-500 rounded-full flex items-center justify-center text-white transition-all shadow-lg z-50" id="back-to-top">
        <i class="lni-chevron-up text-xl"></i>
    </a>

    <!--====== SCRIPTS ======-->
    <script src="files/js/vendor/modernizr-3.6.0.min.js"></script>
    <script src="files/js/vendor/jquery-1.12.4.min.js"></script>
    <script src="files/js/ajax-contact.js"></script>
    <script src="files/js/jquery.easing.min.js"></script>
    <script src="files/js/scrolling-nav.js"></script>
    <script src="files/js/validator.min.js"></script>
    <script src="files/js/jquery.magnific-popup.min.js"></script>
    <script src="files/js/slick.min.js"></script>
    <script src="files/js/main.js"></script>

    <script>
        function abrirWhatsApp(numero) {
            const cleanedNumber = numero.replace(/\+/g, '').replace(/\s/g, '');
            const url = `https://wa.me/${cleanedNumber}`;
            window.open(url, '_blank');
        }

        document.addEventListener('DOMContentLoaded', function() {
            const navbar = document.getElementById('navbar');
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');
            const backToTop = document.getElementById('back-to-top');

            window.addEventListener('scroll', function() {
                if (window.scrollY > 100) {
                    navbar.classList.add('scrolled');
                    backToTop.style.display = 'flex';
                } else {
                    navbar.classList.remove('scrolled');
                    backToTop.style.display = 'none';
                }
            });

            if (mobileMenuBtn && mobileMenu) {
                mobileMenuBtn.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }

            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                        if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                            mobileMenu.classList.add('hidden');
                        }
                    }
                });
            });
        });
    </script>
</body>

</html>
