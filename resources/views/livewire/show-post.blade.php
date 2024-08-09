<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {!! SEO::generate() !!}
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .article-details {
            margin-bottom: 20px;
        }

        .article-details img {
            width: 100%;
            max-width: 30%;
            /* Establece el ancho máximo deseado para la imagen */
            height: auto;

            margin: 0 auto;
            /* Centrar la imagen */
            display: block;
            /* Evitar el espacio adicional debajo de la imagen */
            border-radius: 10px;
            /* Agregar borde redondo */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            /* Agregar sombra gris */
        }

        .article-details video {
            width: 100%;
            max-width: 30%;
            /* Establece el ancho máximo deseado para la imagen */
            height: auto;

            margin: 0 auto;
            /* Centrar la imagen */
            display: block;
            /* Evitar el espacio adicional debajo de la imagen */
            border-radius: 10px;
            /* Agregar borde redondo */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            /* Agregar sombra gris */
        }

        .published-date {
            font-size: 14px;
            color: #777;
            font-weight: bold;
            margin-top: 10px;
        }

        h2 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        p {
            font-size: 16px;
            line-height: 1.5;
        }

        .back-button {
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #0369a1;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .back-button i {
            margin-right: 5px;
        }

        .back-button:hover {
            background-color: #155e75;
        }
    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

</head>

<body>

    <div class="container">
        <button class="back-button" onclick="javascript:history.go(-1);">
            <i class="fas fa-arrow-left"></i>
            Regresar
        </button>
        <!-- Mostrar detalles del artículo -->
        <div class="article-details">
            @if (strpos($post->post_image, 'videos') !== false)
                {{-- Si es un video, mostrar el reproductor de video --}}
                <video width="240" height="160" controls>
                    <source src="{{ Storage::url($post->post_image) }}" type="video/mp4">
                    Tu navegador no soporta el elemento de video.
                </video>
            @else
                {{-- Si es una imagen, mostrar la imagen --}}
                <img src="{{ Storage::url($post->post_image) }}" alt="Imagen del artículo">
            @endif

            <p class="published-date">Publicado <span>{{ $post->post_date }}</span></p>
        </div>


        <h2>{{ $post->post_title }}</h2>
        <p>{{ $post->post_content }}</p>
        <!-- ... -->
    </div>

    <!-- Section: Design Block -->
    <script type="text/javascript"
        src="https://platform-api.sharethis.com/js/sharethis.js#property=646e837f413e9c001905a213&product=inline-share-buttons&source=platform"
        async="async"></script>
    <div class="flex flex-col items-center" style="text-align: center">
        <h3 style="color:#0e7490;">¡Comparte este artículo y expande su
            impacto!</h3>

        <div class="sharethis-inline-share-buttons my-5 pb-10"></div>

    </div>

</body>

</html>
