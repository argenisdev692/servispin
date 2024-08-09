<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Post;
use Carbon\Carbon;

use Artesaos\SEOTools\Facades\SEOTools;

use Artesaos\SEOTools\Facades\SEOMeta;

class PostController extends Controller
{
   

    public function showPost($postId)
    {
       

          $post = Post::where('post_title_slug', $postId)->firstOrFail();
         
          // OR use single only SEOTools

        SEOTools::setTitle($post->post_title);
       
        SEOTools::setDescription($post->meta_description);
        SEOTools::opengraph()->setUrl('https://aiosrealestate.com/');
        SEOTools::setCanonical('https://aiosrealestate.com');
        SEOTools::opengraph()->addProperty('type', 'articles');
       
        SEOTools::jsonLd()->addImage('https://www.aiosrealestate.com/img/logo.jpg');
        SEOMeta::addKeyword($post->meta_keywords);
        SEOMeta::addMeta('article:published_time', $post->post_date = Carbon::parse($post->created_at)->format('F d, Y'), 'property');
        // OR use single only SEOTools

        return view('livewire.show-post', compact('post'))
            ->layout('layouts.app');
    }


    public function showLatestPosts()
{
    $latestPosts = Post::latest()->take(10)->get();

    return view('livewire.latest-posts', compact('latestPosts'))
        ->layout('layouts.app');
}
}
