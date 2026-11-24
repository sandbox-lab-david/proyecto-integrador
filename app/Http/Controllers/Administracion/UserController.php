<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class UserController extends Controller
{
    use MensajesValidacion;

    public function index(Request $request)
    {
        $cuentas = $this->buscar(User::with(['trabajador', 'estudiante']), $request->input('buscar'), ['name', 'email'])
            ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->input('tipo')))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('administracion.cuentas', compact('cuentas'));
    }

    public function cambiarEstado(User $usuario)
    {
        if ($usuario->is(auth()->user())) {
            return back()->with('error', 'No puedes desactivar tu propia cuenta.');
        }

        $usuario->update(['activo' => ! $usuario->activo]);

        return back()->with('exito', "Cuenta de {$usuario->email} ".($usuario->activo ? 'activada.' : 'desactivada. Ya no podrá iniciar sesión.'));
    }

    public function enviarEnlace(User $usuario)
    {
        if (! $usuario->activo) {
            return back()->with('error', "Activa primero la cuenta de {$usuario->email}.");
        }

        $estado = Password::sendResetLink(['email' => $usuario->email]);

        return $estado === Password::RESET_LINK_SENT
            ? back()->with('exito', "Se envió a {$usuario->email} un enlace para restablecer la contraseña. Vence en ".config('auth.passwords.users.expire').' minutos.')
            : back()->with('error', 'Ya se envió un enlace hace poco. Espera un minuto antes de enviar otro.');
    }
}
