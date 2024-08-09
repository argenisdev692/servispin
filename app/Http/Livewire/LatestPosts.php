<?php

namespace App\Http\Livewire;
use App\Models\Post;
use Livewire\Component;

class LatestPosts extends Component
{
    public $latestPosts;

    public function mount()
    {
       
        $this->latestPosts = Post::latest()->take(10)->get();
    }

    public function render()
    {
        return view('livewire.latest-posts');
    }
}
