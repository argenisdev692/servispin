<?php

use Illuminate\Support\Facades\Route;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;// LARAVEL SOCIALITE


use App\Http\Livewire\UsersCrud;
use App\Http\Livewire\PostComponent;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\PostController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/posts/{postId}', [PostController::class, 'showPost'])->name('posts.show');
///------------- ROUTE GOOGLE AUTH ---------///
Route::get('/google-auth/redirect', function () {
    return Socialite::driver('google')->redirect();
});
 

Route::get('/google-auth/callback', function () {
    $googleUser = Socialite::driver('google')->user();

    $randomNumber = rand(100, 999);
   $nameWithoutSpaces = strtolower(str_replace(' ', '', $googleUser->name));

    // Check if user exists with the same email address
    $existingUser = User::where('email', $googleUser->email)->first();

    if ($existingUser) {
    if (!$existingUser->email_verified_at) {
        // User exists but email isn't verified, set verification with DateNow
        $existingUser->email_verified_at = now();
        $existingUser->save();
    }

    // Existing user with verified email, log them in
    Auth::login($existingUser);
    return redirect('/dashboard');
} else {
        $user = User::updateOrCreate([
    'google_id' => $googleUser->id,
    ], [
    'name' => $googleUser->name,
    'username' => $nameWithoutSpaces . $randomNumber,
    'email' => $googleUser->email,
    'email_verified_at' => now(), 
    'password' => bcrypt('finance123=')
    ], function ($user) {
    if ($user->wasRecentlyCreated) {
        $user->email_verified_at = now();
    }
    });


        // Assign default role if not already assigned
        $defaultRole = Role::find(2); // Reemplaza 2 con el ID del rol por defecto
        if (!$user->hasRole($defaultRole)) {
            $user->assignRole($defaultRole);
        }

        // Log in the newly created user
        Auth::login($user);
        return redirect('/dashboard');
    }
});



///------------- END ROUTE GOOGLE AUTH ---------///


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    Route::get('users', UsersCrud::class)->name('users');
    Route::get('posts', PostComponent::class)->name('posts');
    
});
