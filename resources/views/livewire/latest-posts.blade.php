 <style>
     .post-container {
         display: grid;
         grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
         gap: 20px;
         padding-bottom: 130px;

     }

     .post-card {
         border: 1px solid #ddd;
         border-radius: 8px;
         overflow: hidden;
         box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
     }

     .post-card img,
     video {
         width: 100%;
         height: 200px;
         /* Ajusta la altura según tus necesidades */
         object-fit: cover;
         border-bottom: 1px solid #ddd;
     }



     .post-content {
         padding: 16px;
     }

     .post-title {
         font-size: 1.5rem;
         margin-bottom: 8px;
     }

     .post-content p {
         font-size: 1rem;
         color: #555;
     }

     .read-more-link {
         display: block;
         text-align: end;
         margin-top: 16px;
     }

     .read-more-button {
         background-color: #fff;
         border: 1px solid #ddd;
         padding: 6px 14px;
         color: #555;
         text-decoration: none;
         border-radius: 4px;
         font-size: 0.8rem;
         transition: background-color 0.3s ease-in-out;
     }

     .read-more-button:hover {
         background-color: #0284c7;
         color: #fff;
     }
 </style>
 @if (count($latestPosts) > 0)
     <div class="container ">
         <div class="justify-center row">
             <div class="w-full mx-4 lg:w-1/2">
                 <div class="pb-10 text-center section-title">
                     <h4 class="title" id="titleResponsive">Últimos Posts</h4>
                     <p class="text">

                         Explora nuestros fascinantes contenidos que te proporcionarán información interesante y te
                         mantendrán entretenido
                     </p>
                 </div>
                 <!-- section title -->
             </div>
         </div>


         <div class="post-container">
             @foreach ($latestPosts as $post)
                 <div class="post-card">
                     @if (strpos($post->post_image, 'videos') !== false)
                         {{-- Si es un video, mostrar el reproductor de video --}}
                         <video controls>
                             <source src="{{ Storage::url($post->post_image) }}" type="video/mp4">
                             Tu navegador no soporta el elemento de video.
                         </video>
                     @else
                         {{-- Si es una imagen, mostrar la imagen --}}
                         <img src="{{ Storage::url($post->post_image) }}" alt="{{ $post->post_title }}">
                     @endif
                     <div class="post-content">
                         <h3 class="post-title"> {{ Str::words($post->post_title, 4, '...') }}</h3>
                         <p>{{ Str::words($post->post_content, 6, '...') }}</p>
                         <!-- Agrega aquí cualquier otra información que desees mostrar -->
                         <a href="{{ route('posts.show', $post->post_title_slug) }}" class="read-more-link">
                             <span class="read-more-button">Leer más</span>
                         </a>
                     </div>
                 </div>
             @endforeach
         </div>
     </div>
 @endif
