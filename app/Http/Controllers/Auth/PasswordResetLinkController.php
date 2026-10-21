<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    private const MENSAJES = [
        Password::RESET_LINK_SENT => 'Te enviamos un enlace para cambiar tu contraseña. Revisa tu correo.',
        Password::PASSWORD_RESET => 'Tu contraseña se cambió. Ya puedes iniciar sesión.',
        Password::INVALID_USER => 'No encontramos una cuenta con ese correo.',
        Password::INVALID_TOKEN => 'El enlace para cambiar la contraseña ya no es válido. Pide uno nuevo.',
        Password::RESET_THROTTLED => 'Espera un momento antes de pedir otro enlace.',
    ];

    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'Escribe tu correo institucional.',
            'email.email' => 'El correo no tiene un formato válido.',
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', self::MENSAJES[$status] ?? __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => self::MENSAJES[$status] ?? __($status)]);
    }
}
