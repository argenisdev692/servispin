<?php

namespace App\Http\Livewire;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Carbon\Carbon;
use Intervention\Image\ImageManager;
use Illuminate\Support\Str;

class PostComponent extends Component
{
     use WithFileUploads;
    use WithPagination;
    public $showingDataModal = false;
    public $newImage;
    public $oldImage;
    public $post_title;
    
    public $post_content;
    
    public $post_status ;
  
    public $category_id ;
    public $post_date;

     public $meta_description;
      public $meta_title;
       public $meta_keywords;
    
    public $user_id;
    
    public $isEditMode = false;
  
    public $post;
  
    public $search = '';
    protected $listeners = ['render','delete']; 
    
    public function authorize()
{
    return true;
}

public function showPostBlog($postId)
    {
        // Emitir un evento de redirección a la ruta específica
        $this->redirect(route('posts.show', ['postId' => $postId]));
    }

    public function render()
    {
       

      
   $this->authorize('manage admin');
        $categories = Category::latest()->get();
        $posts = Post::where('post_title', 'like', '%'.$this->search.'%')->where('posts.post_status', '=', 'ACTIVE')
        ->join('categories', 'categories.id', '=', 'posts.category_id')
        ->select( 'posts.*','categories.category_name')
            ->orderBy('posts.id','DESC')->paginate(10);

            
       return view('livewire.post-component', ['posts' => $posts,'categories' => $categories]);
    }

    public function CleanUp()  // FUNCTION CLEAN LIVEWIRE-TMP
    {
   
      $oldfiles= Storage::disk('local');
      foreach ($oldfiles->allFiles('livewire-tmp') as $file)
      {
        $yest=now()->timestamp;
       
        if ($yest > $oldfiles->lastModified($file)) {

            $oldfiles->delete($file);
        }
         
         
      }
  
  }

 public function showDataModal()
    {
        $this->reset();
        $this->showingDataModal = true;
    }
public function closeModal()
    {
          $this->showingDataModal = false;
    }

      public function storeData()
    {
           $this->authorize('manage admin');
         $valid_data = $this->validate([
        'post_title' => 'required|unique:posts|min:3|max:100',
        'post_content' => 'required|min:3',
        'newImage' => 'required',
        'post_status' => 'required',
      
        'category_id' => 'required',
       'meta_description' => 'required|unique:posts|min:3|max:200',
       'meta_title' => 'required|unique:posts|min:3|max:200',
       'meta_keywords' => 'required|min:3|max:200',
      ]);

           // UPLOAD WITH INTERVENTION IMAGE
// Verificar si el archivo es una imagen
if ($this->newImage->getMimeType() && strpos($this->newImage->getMimeType(), 'image') !== false) {
    // UPLOAD WITH INTERVENTION IMAGE
    $image = $this->newImage->store('posts', 'public');

    // Crear un thumbnail de la imagen usando Intervention Image Library
    $imageHashName = $this->newImage->hashName();

    // Usar ImageManager para redimensionar la imagen si es necesario
    $resize = new ImageManager();
    $imageInstance = $resize->make('storage/posts/'.$imageHashName);

    // Verificar si es necesario redimensionar
    if ($imageInstance->width() > 700 || $imageInstance->height() > 700) {
        // Calcular el factor de escala para mantener la relación de aspecto
        $scaleFactor = min(700 / $imageInstance->width(), 700 / $imageInstance->height());

        // Calcular el nuevo ancho y alto para redimensionar la imagen
        $newWidth = $imageInstance->width() * $scaleFactor;
        $newHeight = $imageInstance->height() * $scaleFactor;

        // Redimensionar la imagen
        $imageInstance->resize($newWidth, $newHeight);
    }

    // Guardar la imagen
    $imageInstance->save('storage/posts/'.$imageHashName);

    // END UPLOAD WITH INTERVENTION IMAGE
} elseif ($this->newImage->getMimeType() && strpos($this->newImage->getMimeType(), 'video') !== false) {
    // Si es un video, simplemente guardarlo en el almacenamiento
    $image = $this->newImage->store('videos', 'public');
    // Puedes agregar cualquier lógica adicional necesaria para manejar videos
} else {
    // Manejar otros tipos de archivos si es necesario
    // Por ejemplo, puedes lanzar una excepción o guardarlos en un directorio específico
    // según el tipo de archivo que no sea ni imagen ni video
}

// END UPLOAD WITH INTERVENTION IMAGE

              // CARBON FORMAT DATE
         $date = Carbon::now()->locale('en')->isoFormat('dddd, MMMM Do YYYY, H:mm A');
            // END CARBON FORMAT DATE
$formattedContent = nl2br($this->post_content);
        Post::create([
            'post_title' => $this->post_title,
            'post_content' => $formattedContent,
            'post_image' => 'app/public/'.$image,
            'post_status' => $this->post_status,
            'post_date' => $date,
             'user_id' => auth()->user()->id,
            'category_id' => $this->category_id,
            'meta_description' => $this->meta_description,
            'meta_title' => $this->meta_title,
            'meta_keywords' => $this->meta_keywords,
            'post_title_slug' => Str::slug($this->post_title),
        ]); session()->flash("message", "Data registration successfully.");
        $this->reset();
         $this->CleanUp();
        sleep(2); //BUTTON SPINNER LOADING
    }

    public function showEditDataModal($id)
    {
           $this->authorize('manage admin');
        $this->post = Post::findOrFail($id);
        $this->post_title = $this->post->post_title;
        $this->post_content = $this->post->post_content;
        $this->post_status = $this->post->post_status;
        $this->category_id = $this->post->category_id;
        $this->oldImage = $this->post->post_image;
         $this->meta_description = $this->post->meta_description;
          $this->meta_title = $this->post->meta_title;
           $this->meta_keywords = $this->post->meta_keywords;
        $this->isEditMode = true;
        $this->showingDataModal = true;
    }

     public function updateData()
    {
           $this->authorize('manage admin');
        
        $this->validate([
            'post_title' => 'required|string|min:3|max:100|unique:posts,post_title,'.$this->post->id.',id',
           'post_content' => 'required|min:3|max:1000',
             'post_status' => 'required|min:3|max:200',
             'category_id' => 'required',
            'meta_description' => 'required|string|min:3|max:200|unique:posts,meta_description,'.$this->post->id.',id',
            'meta_title' => 'required|string|min:3|max:200|unique:posts,meta_title,'.$this->post->id.',id',
           'meta_keywords' => 'required|min:3|max:200',
            
        ]);

        
       if ($this->newImage) {
    // Eliminar el archivo anterior solo si hay un nuevo archivo
    $oldFile = $this->post->post_image;

    if ($oldFile) {
        // Si existe un archivo anterior, eliminarlo
        Storage::disk('public')->delete($oldFile);
    }

    // Subir el nuevo archivo
    $file = $this->newImage->store('posts', 'public');

    // Verificar si el nuevo archivo es una imagen
    if ($this->newImage->getMimeType() && strpos($this->newImage->getMimeType(), 'image') !== false) {
        // Crear un thumbnail de la imagen usando Intervention Image Library
        $imageHashName = $this->newImage->hashName();

        // Usar ImageManager para redimensionar la imagen si es necesario
        $resize = new ImageManager();
        $imageInstance = $resize->make('storage/posts/' . $imageHashName);

        // Verificar si es necesario redimensionar
        if ($imageInstance->width() > 700 || $imageInstance->height() > 700) {
            // Calcular el factor de escala para mantener la relación de aspecto
            $scaleFactor = min(700 / $imageInstance->width(), 700 / $imageInstance->height());

            // Calcular el nuevo ancho y alto para redimensionar la imagen
            $newWidth = $imageInstance->width() * $scaleFactor;
            $newHeight = $imageInstance->height() * $scaleFactor;

            // Redimensionar la imagen
            $imageInstance->resize($newWidth, $newHeight);

            // Guardar la imagen redimensionada
            $imageInstance->save('storage/posts/' . $imageHashName);
        }
    }

    // Asignar la variable correcta dependiendo de si es una imagen o un video
    $image = $this->newImage->getMimeType() && strpos($this->newImage->getMimeType(), 'image') !== false
        ? $imageHashName
        : $file;
    
} elseif ($this->newVideo) {
    // Eliminar el archivo de video anterior solo si hay un nuevo video
    $oldVideo = $this->post->post_image;

    if ($oldVideo) {
        // Si existe un video anterior, eliminarlo
        Storage::disk('public')->delete($oldVideo);
    }

    // Subir el nuevo video
    $video = $this->newVideo->store('videos', 'public');
    // Puedes agregar cualquier lógica adicional necesaria para manejar videos

    // Asignar la variable correcta dependiendo de si es una imagen o un video
    $image = $video;
}

// Actualizar la columna 'post_image' con la variable correcta
$this->post->update(['post_image' => 'app/public/'.$image]);


 $formattedContent = nl2br($this->post_content);
        $this->post->update([
            'post_title' => $this->post_title,
           'post_content' => $formattedContent,
             'post_image' => 'app/public/'.$image,
            'post_status' => $this->post_status,
            'category_id' => $this->category_id,
             'meta_description' => $this->meta_description,
              'meta_title' => $this->meta_title,
               'meta_keywords' => $this->meta_keywords,
               'post_title_slug' => Str::slug($this->post_title),
        ]); session()->flash("message", "Data Updated Successfully.");
        $this->reset();
         $this->CleanUp();
         sleep(2); //BUTTON SPINNER LOADING
    }


    public function delete(Post $post)
    {
           $this->authorize('manage admin');
        $post->delete();
          Storage::disk('public')->delete($post->post_image);
      
      
     
    }

   
public function renderPost()
{
   $data = $this->renderPosts(); // Llamada a la función renderPosts() para obtener los datos

   return view('livewire.blog-section', $data); // Devolver la vista 'dashboard' con los datos
}

public function renderPosts()
{
   $categories = Category::latest()->get();
   $posts = Post::where('post_title', 'like', '%'.$this->search.'%')
      ->where('posts.post_status', '=', 'ACTIVE')
      ->join('categories', 'categories.id', '=', 'posts.category_id')
      ->select('posts.*', 'categories.category_name')
      ->orderBy('posts.id', 'DESC')
      ->paginate(10);
      
// Convertir el campo post_date al nuevo formato deseado
        foreach ($posts as $post) {
            $post->post_date = Carbon::parse($post->created_at)->format('F d, Y');
        }

   return [
      'posts' => $posts,
      'categories' => $categories
   ];
}

public function showPost($id)
{
    $posts = Post::findOrFail($id);
   return view('livewire.showpost',['posts' => $posts]);
}

}
