<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    private const MENSAJES = [
        Password::RESET_LINK_SENT => 'Te enviamos un enlace para cambiar tu contraseña. Revisa tu correo.',
        Password::PASSWORD_RESET => 'Tu contraseña se cambió. Ya puedes iniciar sesión.',
        Password::INVALID_USER => 'No encontramos una cuenta con ese correo.',
        Password::INVALID_TOKEN => 'El enlace para cambiar la contraseña ya no es válido. Pide uno nuevo.',
        Password::RESET_THROTTLED => 'Espera un momento antes de pedir otro enlace.',
    ];

    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'email.required' => 'Escribe tu correo institucional.',
            'email.email' => 'El correo no tiene un formato válido.',
            'password.required' => 'Escribe la nueva contraseña.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos :min caracteres.',
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $status == Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', self::MENSAJES[$status] ?? __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => self::MENSAJES[$status] ?? __($status)]);
    }
}
