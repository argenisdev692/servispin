<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Response;

class LoginController extends Controller
{
    // USER LOGIN
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            $token = $user->createToken('API Token')->plainTextToken;

            // Crear el objeto user
            $userObject = [
                'name' => $user->name,
                'email' => $user->email,
                'token' => $token,
            ];

            // Crear la respuesta completa
            $response = [
                'user' => $userObject,
            ];

            return Response::json($response);
        }

        return Response::json(['error' => 'Credenciales inválidas'], 401);
    }

    // USER LOGOUT
    public function logout()
    {
        if (auth()->user()) {
            auth()->user()->tokens()->delete();

            return Response::json(['message' => 'Sesión cerrada correctamente'], 200);
        } else {
            return Response::json(['message' => 'No se encontró ninguna sesión activa'], 401);
        }
    }

    // / DETAILS CURRENT USER
    public function user()
    {
        return response()->json(['User' => auth()->user()]);
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
            return response()->json(['error' => 'La contraseña actual no coincide'], 401);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json(['message' => 'Contraseña actualizada correctamente']);
    }

    // RESET PASSWORD LINK
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => 'Link de restablecimiento de contraseña enviado al correo electrónico.']);
        } else {
            return response()->json(['error' => 'No se pudo enviar el link de restablecimiento de contraseña.'], 500);
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
            return response()->json(['message' => 'Contraseña restablecida correctamente.']);
        } else {
            return response()->json(['error' => 'No se pudo restablecer la contraseña.'], 500);
        }
    }

    // UPDATE PROFILE USER
    public function updateProfile(Request $request, UpdateUserProfileInformation $updater)
    {
        $user = $request->user();

        $updater->update($user, $request->all());

        return Response::json(['message' => 'Perfil actualizado correctamente.']);
    }
}
