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
use App\Http\Controllers\CompanyDataController; // Import the new controller
use App\Http\Controllers\Api\AppointmentController; // Import the appointment controller
use App\Http\Controllers\Api\AvailabilityController; // Import the availability controller
use App\Http\Controllers\Admin\AppointmentCalendarController; // Import the new admin controller
use App\Http\Controllers\BrandController; // Import the brand controller
use App\Http\Controllers\Admin\AvailabilityExceptionController; // Import the availability exception controller
use App\Http\Controllers\Admin\ServiceController; // Import the service controller
use App\Http\Controllers\ContactController; // Import the contact controller

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

// Aplicar rate limiter global a todas las rutas web
Route::middleware(['throttle:global'])->group(function () {

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

// Rutas públicas para el sistema de citas (no requieren autenticación)
Route::prefix('appointments')->name('appointments.')->middleware('throttle:appointments')->group(function () {
    Route::get('book', [AppointmentController::class, 'bookingForm'])->name('book');
    Route::get('services', [AppointmentController::class, 'getServices'])->name('services');
    Route::post('store', [AppointmentController::class, 'store'])->name('store');
    
    // Ruta para verificar disponibilidad
    Route::post('availability/slots', [AvailabilityController::class, 'getTimeSlots'])->name('availability.slots');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'throttle:api'
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    Route::get('users', UsersCrud::class)->name('users');
    Route::get('posts', PostComponent::class)->name('posts');
    // Brand Routes (using Controller instead of Livewire)
    Route::get('brands', [BrandController::class, 'index'])->name('brands.index');
    Route::post('brands', [BrandController::class, 'store'])->name('brands.store');
    Route::get('brands/{uuid}/edit', [BrandController::class, 'edit'])->name('brands.edit');
    Route::put('brands/{uuid}', [BrandController::class, 'update'])->name('brands.update');
    Route::delete('brands/{uuid}', [BrandController::class, 'destroy'])->name('brands.destroy');
    Route::patch('brands/{uuid}/restore', [BrandController::class, 'restore'])->name('brands.restore');
    Route::post('brands/check-name', [BrandController::class, 'checkNameExists'])->name('brands.check-name');
    
    // Service Routes
    Route::prefix('admin/services')->name('services.')->group(function () {
        Route::get('/', [ServiceController::class, 'index'])->name('index');
        Route::post('/', [ServiceController::class, 'store'])->name('store');
        Route::get('/{uuid}/edit', [ServiceController::class, 'edit'])->name('edit');
        Route::put('/{uuid}', [ServiceController::class, 'update'])->name('update');
        Route::delete('/{uuid}', [ServiceController::class, 'destroy'])->name('destroy');
        Route::patch('/{uuid}/restore', [ServiceController::class, 'restore'])->name('restore');
        Route::post('/check-name', [ServiceController::class, 'checkNameExists'])->name('check-name');
    });
    
    // Availability Exception Routes
    Route::prefix('admin/availability-exceptions')->name('admin.availability-exceptions.')->group(function () {
        Route::get('/', [AvailabilityExceptionController::class, 'index'])->name('index');
        Route::post('/', [AvailabilityExceptionController::class, 'store'])->name('store');
        Route::get('/{uuid}/edit', [AvailabilityExceptionController::class, 'edit'])->name('edit');
        Route::put('/{uuid}', [AvailabilityExceptionController::class, 'update'])->name('update');
        Route::delete('/{uuid}', [AvailabilityExceptionController::class, 'destroy'])->name('destroy');
        Route::patch('/{uuid}/restore', [AvailabilityExceptionController::class, 'restore'])->name('restore');
        Route::post('/check-date', [AvailabilityExceptionController::class, 'checkDateExists'])->name('check-date');
    });
    
    // Company Data Routes (AJAX focused)
    Route::get('company-data', [CompanyDataController::class, 'index'])->name('company-data.index'); // Serves view & gets data
    Route::post('company-data', [CompanyDataController::class, 'store'])->name('company-data.store'); // Handles create/update
    // Route for getting data to edit (though store handles update, useful for populating form)
    Route::get('company-data/{companyData}/edit', [CompanyDataController::class, 'edit'])->name('company-data.edit');
    Route::put('company-data/{companyData}', [CompanyDataController::class, 'update'])->name('company-data.update'); // Explicit update route
    // Route::delete('company-data/{companyData}', [CompanyDataController::class, 'destroy'])->name('company-data.destroy'); // Add if needed

    // Rutas protegidas para la gestión de citas
    Route::prefix('admin/appointments')->name('admin.appointments.')->group(function () {
        Route::get('/', [AppointmentController::class, 'index'])->name('index');
        Route::get('/{id}', [AppointmentController::class, 'show'])->name('show');
        Route::put('/{id}', [AppointmentController::class, 'update'])->name('update');
        Route::delete('/{id}', [AppointmentController::class, 'destroy'])->name('destroy');
        
        // Acciones especiales
        Route::patch('/{id}/confirm', [AppointmentController::class, 'confirm'])->name('confirm');
        Route::patch('/{id}/cancel', [AppointmentController::class, 'cancel'])->name('cancel');
        Route::patch('/{id}/complete', [AppointmentController::class, 'complete'])->name('complete');
    });
    
    // Rutas protegidas para la gestión de disponibilidad
    Route::prefix('admin/availability')->name('admin.availability.')->group(function () {
        Route::get('/rules', [AvailabilityController::class, 'getRules'])->name('rules');
        Route::post('/rules', [AvailabilityController::class, 'saveRule'])->name('rules.store');
        
        Route::get('/exceptions', [AvailabilityController::class, 'getExceptions'])->name('exceptions');
        Route::post('/exceptions', [AvailabilityController::class, 'saveException'])->name('exceptions.store');
        Route::delete('/exceptions/{id}', [AvailabilityController::class, 'deleteException'])->name('exceptions.destroy');
    });

    // Admin Appointment Calendar Routes
    Route::prefix('admin/appointment-calendar')->name('admin.appointment.calendar.')->middleware('throttle:calendar')->group(function () {
        Route::get('/', [AppointmentCalendarController::class, 'index'])->name('index'); // Shows the calendar view
        Route::get('/events', [AppointmentCalendarController::class, 'events'])->name('events'); // Gets events for the calendar
        Route::patch('/events/{appointment}', [AppointmentCalendarController::class, 'update'])->name('update'); // Handles drag-and-drop updates
        Route::patch('/status/{id}', [AppointmentCalendarController::class, 'updateStatus'])->name('status.update'); // Ruta para actualizar el estado (confirmar/rechazar)
    });
});

// Contact form routes
Route::post('/contact/submit', [ContactController::class, 'submitForm'])->middleware('throttle:contact')->name('contact.submit');

}); // Cierre del grupo throttle:global
