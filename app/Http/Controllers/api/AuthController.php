<?php

namespace App\Http\Controllers\Api;

use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage admin')->only(['getUsers']);

    }

    // USER REGISTER

    public function register(Request $request)
    {
        // Validar los datos del formulario, incluyendo la unicidad del correo electrónico
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role_id' => 'required|exists:roles,id', // Asegúrate de que el campo 'role_id' exista en la tabla 'roles'
        ]);

        // Si la validación falla, devolver errores de validación
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Generar un número aleatorio de 3 dígitos
        $randomNumber = rand(100, 999);

        // Crear el usuario
        $user = User::create([
            'name' => $request->input('name'),
            'username' => $request->input('name').$randomNumber,
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        // Encontrar el rol por ID
        $role = Role::find($request->input('role_id'));

        if (! $role) {
            return response()->json(['error' => 'Invalid role ID'], 422);
        }

        // Asignar el rol al usuario
        $user->assignRole($role);

        // Generar un token de autenticación y asignarlo al usuario
        $user->token = $user->createToken('API Token')->plainTextToken;

        // Envía la notificación de verificación de correo electrónico
        $user->sendEmailVerificationNotification();

        $message = 'User data was successfully registered';

        return response()->json(['user' => $user, 'message' => $message, 'token' => $user->token], 201);
    }

    // USER LOGIN

    public function login(Request $request)
    {
        $loginField = $request->input('identity'); // Agrega un campo 'identity' en la solicitud para enviar correo electrónico o nombre de usuario
        $password = $request->input('password');

        $credentials = [];

        // Verifica si el campo 'login' es un correo electrónico o un nombre de usuario
        if (filter_var($loginField, FILTER_VALIDATE_EMAIL)) {
            // Es un correo electrónico
            $credentials['email'] = $loginField;
        } else {
            // Es un nombre de usuario
            $credentials['username'] = $loginField;
        }

        // Agrega la contraseña a las credenciales
        $credentials['password'] = $password;

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $user = Auth::user();

            if ($request->filled('remember')) {
                $rememberToken = Str::random(60); // Generar un token de recordar aleatorio
                $user->forceFill([
                    'remember_token' => hash('sha256', $rememberToken),
                ])->save();

                $userObject = $user->toArray();
                $userObject['remember_token'] = $rememberToken;
            } else {
                $userObject = $user->toArray();
            }

            $userObject['token'] = $user->createToken('API Token')->plainTextToken;

            $roles = Role::pluck('name', 'name')->all();
            $userRole = $user->roles->pluck('name', 'name')->all();

            $welcomeMessage = '¡Welcome '.$user->name.'! Your user type is '.implode(', ', $userRole).'.';

            return response()->json(['user' => $userObject, 'roles' => $roles, 'userRole' => $userRole, 'message' => $welcomeMessage], 200);
        }

        return response()->json(['error' => 'Invalid credentials'], 401);
    }

    // USER LOGOUT
    public function logout()
    {
        if (auth()->user()) {
            auth()->user()->tokens()->delete();

            return Response::json(['message' => 'Session closed successfully'], 200);
        } else {
            return Response::json(['message' => 'No active session found'], 401);
        }
    }

    // DETAILS CURRENT USER
    public function user()
    {
        // Obtener al usuario autenticado
        $user = auth()->user();

        // Verificar si el usuario está autenticado
        if (! $user) {
            return response()->json(['message' => 'User not authenticated'], 401);
        }

        // Obtener los roles disponibles
        $roles = Role::pluck('name', 'id')->all();

        // Obtener los IDs de roles asignados al usuario
        $userRoles = $user->roles->pluck('id')->all();

        // Devolver la respuesta JSON con los detalles del usuario y los roles
        return response()->json([
            'user' => $user,
            'roles' => $roles,
            'userRoles' => $userRoles,
        ], 200);
    }

    // UPDATE USER PASSWORD
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed'],
        ]);

        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json(['error' => 'Current password does not match'], 401);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'Password updated successfully']);
    }

    // RESET PASSWORD LINK
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Password reset link sent to email.']);
        } else {
            return response()->json(['error' => 'Failed to send password reset link.'], 500);
        }
    }

    // RESET PASSWORD PAGE MAIL
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password reset successfully.']);
        } else {
            return response()->json(['error' => 'Password could not be reset.'], 500);
        }
    }

    // UPDATE PROFILE USER
    public function updateProfile(Request $request, UpdateUserProfileInformation $updater)
    {
        $user = $request->user();

        // --- VALIDAR SIZE DE LA PHOTO
        try {
            $request->validate([
                'photo' => 'nullable|file|max:1024', // Cambiar 1024 por 1000 para 1MB
            ], [
                'photo.max' => 'The photo must not be larger than 1MB.', // Mensaje personalizado
            ]);
        } catch (ValidationException $e) {
            return Response::json(['message' => $e->errors()], 422);
        }

        // -- Verificar si se envió una nueva foto
        if ($request->hasFile('photo')) {
            $input['photo'] = $request->file('photo');
        }

        // --- INPUTS UPDATE
        $updater->update($user, $request->all());

        // --- MESSAGE
        return Response::json(['message' => 'Profile updated successfully.']);
    }

    // GET ALL USERS
    public function getUsers()
    {

        $users = User::all();

        return response()->json(['users' => $users], 200);
    }

    // ----- FUNCTION CHECK EMAIL REGISTER USER ----//
    public function checkEmail(Request $request)
    {
        $email = $request->input('email');
        $user = User::where('email', $email)->first();

        if ($user) {
            return response()->json(['message' => 'Email is not available'], 200);
        } else {
            return response()->json(['message' => 'Email is available'], 200);
        }
    }
}
