<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Spatie\Permission\Models\Role;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User|JsonResponse
    {
        $validator = Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],

            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',

            'role_id' => ['required', 'exists:roles,id'], // Validación para asegurar que el ID del rol existe en la tabla 'roles'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Generar un número aleatorio de 3 dígitos
        $randomNumber = rand(100, 999);
        $user = User::create([
            'name' => $input['name'],
            'username' => $input['name'].$randomNumber,
            'email' => $input['email'],
            'password' => Hash::make($input['password']),

        ]);

        $role = Role::find($input['role_id']);

        if (! $role) {
            return response()->json(['error' => 'Invalid role ID'], 422);
        }

        $user->assignRole($role);
        // Genera un token de autenticación y asigna al usuario
        $user->token = $user->createToken('API Token')->plainTextToken;

        return $user;
    }
}
