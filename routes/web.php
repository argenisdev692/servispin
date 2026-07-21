<?php

use App\Http\Controllers\Admin\AppointmentCalendarController;
use App\Http\Controllers\Admin\AvailabilityExceptionController;
use App\Http\Controllers\Admin\BackupHistoryController;
use App\Http\Controllers\Admin\GoogleCalendarOAuthController;
use App\Http\Controllers\Admin\RemoteAssistanceAdminController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Api\AppointmentController;
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\RemoteAssistanceController; // LARAVEL SOCIALITE
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CompanyDataController;
use App\Http\Controllers\ContactController; // Import the new controller
use App\Http\Controllers\GalleryImageController; // Import the appointment controller
use App\Http\Controllers\PostController; // Import the availability controller
use App\Livewire\PostComponent; // Import the remote assistance controller
use App\Livewire\UsersCrud; // Import the new admin controller
use App\Models\CompanyData; // Import the remote assistance admin controller
use App\Models\GalleryImage; // Import the brand controller
use App\Services\GoogleCalendarOAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route; // Import the contact controller

// Import the gallery image controller

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

    Route::get('/', function (Request $request) {
        $code = trim((string) $request->query('code', ''));
        $scope = $request->query('scope');

        if (app(GoogleCalendarOAuthService::class)->isCalendarOAuthCallback(
            $code,
            is_string($scope) ? $scope : null
        )) {
            return app(GoogleCalendarOAuthController::class)->callback($request);
        }

        $galleryImages = GalleryImage::orderBy('sort_order', 'asc')->get();

        return view('welcome', compact('galleryImages'));
    });

    Route::get('/posts/{postId}', [PostController::class, 'showPost'])->name('posts.show');
    // Registro cerrado: solo miembros existentes pueden acceder
    Route::get('/register', function () {
        return redirect()->route('login')->with('members_only', true);
    })->name('register');

    Route::post('/register', function () {
        return redirect()->route('login')->with('members_only', true);
    });

    // /------------- ROUTE GOOGLE AUTH (Socialite) ---------///
    Route::get('/google-auth/redirect', [GoogleAuthController::class, 'redirect']);
    Route::get('/google-auth/callback', [GoogleAuthController::class, 'callback']);
    // Alias compatible con GOOGLE_REDIRECT_URI=/auth/google/callback
    Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect']);
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);
    // /------------- END ROUTE GOOGLE AUTH ---------///

    // Rutas públicas para el sistema de citas (no requieren autenticación)
    Route::prefix('appointments')->name('appointments.')->middleware('throttle:appointments')->group(function () {
        Route::get('book', [AppointmentController::class, 'bookingForm'])->name('book');
        Route::get('services', [AppointmentController::class, 'getServices'])->name('services');
        Route::post('store', [AppointmentController::class, 'store'])->name('store');

        // Ruta para verificar disponibilidad
        Route::post('availability/slots', [AvailabilityController::class, 'getTimeSlots'])->name('availability.slots');
    });

    // Asistencia técnica remota — público y anónimo (FR-1).
    // Sin cuenta: la protección es throttle + honeypot + el propio pago previo, que
    // es el mejor filtro anti-spam que existe (plan §8).
    Route::prefix('asistencia-remota')->name('remote-assistance.')->middleware('throttle:appointments')->group(function () {
        Route::get('/', [RemoteAssistanceController::class, 'landing'])->name('landing');
        Route::get('solicitar', [RemoteAssistanceController::class, 'bookingForm'])->name('book');
        Route::post('store', [RemoteAssistanceController::class, 'store'])->name('store');

        // Reutiliza el mismo cálculo de huecos que el flujo presencial: la agenda del
        // técnico es una sola (plan §1). Aquí se le pasa además el huso del cliente.
        Route::post('slots', [AvailabilityController::class, 'getTimeSlots'])->name('slots');
    });

    Route::middleware([
        'auth:sanctum',
        config('jetstream.auth_session'),
        'verified',
        'throttle:api',
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

            // Asistencia remota (US-2, US-6). La autorización de admin se comprueba
            // en las FormRequest con el guard 'sanctum' explícito: el middleware
            // `role:`/`permission:` de Spatie resolvería el guard 'web' y dejaría
            // fuera hasta al propio Cesar (ver VerifyPaymentRequest::authorize()).
            Route::patch('/{id}/verify-payment', [RemoteAssistanceAdminController::class, 'verifyPayment'])
                ->name('verify-payment');
            Route::patch('/{id}/meeting-link', [RemoteAssistanceAdminController::class, 'updateMeetingLink'])
                ->name('meeting-link');
            Route::patch('/{id}/cancel-awaiting-link', [RemoteAssistanceAdminController::class, 'cancelAwaitingLink'])
                ->name('cancel-awaiting-link');
            Route::post('/{id}/resend-confirmation', [RemoteAssistanceAdminController::class, 'resendConfirmation'])
                ->name('resend-confirmation');
            Route::post('/remote', [RemoteAssistanceAdminController::class, 'store'])->name('remote.store');
        });

        // Bandeja de verificación de pagos remotos (US-2)
        Route::get('admin/remote-assistance', [RemoteAssistanceAdminController::class, 'index'])
            ->name('admin.remote-assistance.index');
        Route::get('admin/remote-assistance/pagos', [RemoteAssistanceAdminController::class, 'paymentHistory'])
            ->name('admin.remote-assistance.payments');

        // OAuth Google Calendar/Meet — flujo por navegador (recomendado con Sail)
        Route::get('admin/google-calendar/oauth/connect', [GoogleCalendarOAuthController::class, 'connect'])
            ->name('admin.google-calendar.oauth.connect');

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

        // Backup History Routes
        Route::prefix('admin/backup-history')->name('admin.backup-history.')->group(function () {
            Route::get('/', [BackupHistoryController::class, 'index'])->name('index');
            Route::get('/datatable', [BackupHistoryController::class, 'datatable'])->name('datatable');
            Route::get('/{backupFile}', [BackupHistoryController::class, 'show'])->name('show');
            Route::get('/{backupFile}/download', [BackupHistoryController::class, 'download'])->name('download');
            Route::delete('/{backupFile}', [BackupHistoryController::class, 'destroy'])->name('destroy');
        });

        // Gallery Image Routes
        Route::prefix('gallery-images')->name('gallery-images.')->group(function () {
            Route::get('/', [GalleryImageController::class, 'index'])->name('index');
            Route::post('/', [GalleryImageController::class, 'store'])->name('store');
            Route::get('/{uuid}/edit', [GalleryImageController::class, 'edit'])->name('edit');
            Route::put('/{uuid}', [GalleryImageController::class, 'update'])->name('update');
            Route::delete('/{uuid}', [GalleryImageController::class, 'destroy'])->name('destroy');
            Route::post('/reorder', [GalleryImageController::class, 'reorder'])->name('reorder');
        });
    });

    // Public route for serving gallery images (must be outside auth middleware)
    Route::get('storage-gallery/{path}', [GalleryImageController::class, 'serveFile'])
        ->where('path', '.*')
        ->name('gallery.serve');

    // Contact form routes
    Route::post('/contact/submit', [ContactController::class, 'submitForm'])->middleware('throttle:contact')->name('contact.submit');

    // Páginas legales (GDPR / LSSI). Públicas.
    Route::get('/politica-de-privacidad', function () {
        return view('legal.privacidad', ['companyData' => CompanyData::first()]);
    })->name('legal.privacidad');
    Route::get('/politica-de-cookies', function () {
        return view('legal.cookies', ['companyData' => CompanyData::first()]);
    })->name('legal.cookies');
    Route::get('/aviso-legal', function () {
        return view('legal.terminos', ['companyData' => CompanyData::first()]);
    })->name('legal.terminos');

}); // Cierre del grupo throttle:global
