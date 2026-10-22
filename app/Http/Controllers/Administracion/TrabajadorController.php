<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;
use App\Models\Facultad;
use App\Models\Rol;
use App\Models\Trabajador;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TrabajadorController extends Controller
{
    use MensajesValidacion;

    public function index(Request $request, ?Trabajador $trabajador = null)
    {
        $personal = $this->buscar(Trabajador::with(['user', 'facultades']), $request->input('buscar'), ['codigo', 'nombres', 'apellidos', 'correo'])
            ->when($request->filled('rol'), fn ($q) => $q->conRol($request->input('rol')))
            ->orderBy('apellidos')
            ->paginate(15)
            ->withQueryString();

        $editando = $trabajador?->exists ? $trabajador->load('facultades', 'user') : null;

        return view('administracion.personal', [
            'personal' => $personal,
            'rolesDe' => $this->rolesDe($personal->pluck('id')->push($editando?->id)->filter()),
            'roles' => Rol::orderBy('id')->get(),
            'facultades' => Facultad::where('activo', true)->orWhereIn('id', $editando?->facultades->pluck('id') ?? [])->orderBy('nombre')->get(),
            'editando' => $editando,
        ]);
    }

    public function store(Request $request)
    {
        $datos = $this->validar($request);

        $trabajador = DB::transaction(function () use ($datos) {
            $trabajador = Trabajador::create($datos['trabajador'] + ['activo' => true]);
            $this->guardarRelaciones($trabajador, $datos);

            return $trabajador;
        });

        return redirect()->route('administracion.personal')
            ->with('exito', "{$trabajador->nombres} {$trabajador->apellidos} registrado.".$this->avisoAcceso($trabajador, $datos['acceso']));
    }

    public function update(Request $request, Trabajador $trabajador)
    {
        $datos = $this->validar($request, $trabajador);

        if ($this->esUnoMismo($trabajador) && ! in_array('admin', $datos['roles'], true)) {
            return back()->withInput()->withErrors(['roles' => 'No puedes quitarte el rol de administrador a ti mismo.']);
        }

        if ($this->esUnoMismo($trabajador) && ! $datos['acceso']) {
            return back()->withInput()->withErrors(['acceso' => 'No puedes quitarte el acceso a ti mismo.']);
        }

        $teniaAcceso = (bool) $trabajador->user?->activo;

        DB::transaction(function () use ($trabajador, $datos) {
            $trabajador->update($datos['trabajador']);
            $this->guardarRelaciones($trabajador, $datos);
        });

        $aviso = $datos['acceso'] && ! $teniaAcceso ? $this->avisoAcceso($trabajador->fresh('user'), true) : '';

        return redirect()->route('administracion.personal')
            ->with('exito', "{$trabajador->nombres} {$trabajador->apellidos} actualizado.".$aviso);
    }

    public function cambiarEstado(Trabajador $trabajador)
    {
        if ($this->esUnoMismo($trabajador)) {
            return back()->with('error', 'No puedes desactivarte a ti mismo.');
        }

        $activo = ! $trabajador->activo;

        DB::transaction(function () use ($trabajador, $activo) {
            $trabajador->update(['activo' => $activo]);
            $trabajador->user?->update(['activo' => $activo]);
        });

        return back()->with('exito', "{$trabajador->nombres} {$trabajador->apellidos} ".($activo ? 'activado.' : 'desactivado. Ya no podrá entrar al sistema.'));
    }

    private function validar(Request $request, ?Trabajador $trabajador = null): array
    {
        $datos = $request->validate([
            'codigo' => ['required', 'string', 'max:20', Rule::unique('trabajadores', 'codigo')->ignore($trabajador)],
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'correo' => [
                'required', 'email', 'ends_with:@uees.edu.ec', 'max:255',
                Rule::unique('trabajadores', 'correo')->ignore($trabajador),
                Rule::unique('users', 'email')->ignore($trabajador?->user_id),
            ],
            'roles' => 'required|array|min:1',
            'roles.*' => 'string|exists:roles,codigo',
            'facultades' => [Rule::requiredIf(in_array('asistente', (array) $request->input('roles'), true)), 'array'],
            'facultades.*' => 'integer|exists:facultades,id',
        ], $this->mensajes([
            'correo.unique' => 'Ya hay una persona o una cuenta con este correo.',
            'correo.ends_with' => 'Usa el correo institucional @uees.edu.ec.',
            'codigo.unique' => 'Ya hay una persona con este código.',
            'roles.required' => 'Marca al menos un rol.',
            'roles.min' => 'Marca al menos un rol.',
            'facultades.required' => 'Una asistente debe tener al menos una facultad: es lo que decide qué solicitudes ve.',
        ]));

        return [
            'trabajador' => [
                'codigo' => $datos['codigo'],
                'nombres' => $datos['nombres'],
                'apellidos' => $datos['apellidos'],
                'correo' => mb_strtolower($datos['correo']),
            ],
            'roles' => array_values($datos['roles']),
            'facultades' => array_map('intval', $datos['facultades'] ?? []),
            'acceso' => $request->boolean('acceso'),
        ];
    }

    private function guardarRelaciones(Trabajador $trabajador, array $datos): void
    {
        DB::table('rol_trabajador')->where('trabajador_id', $trabajador->id)->delete();
        $ids = Rol::whereIn('codigo', $datos['roles'])->pluck('id', 'codigo');
        DB::table('rol_trabajador')->insert(collect($datos['roles'])->map(fn ($codigo) => [
            'trabajador_id' => $trabajador->id,
            'rol' => $codigo,
            'rol_id' => $ids[$codigo],
            'created_at' => now(),
            'updated_at' => now(),
        ])->all());

        $trabajador->facultades()->sync($datos['facultades']);

        $nombre = "{$trabajador->nombres} {$trabajador->apellidos}";

        if ($trabajador->user) {
            $trabajador->user->update([
                'name' => $nombre,
                'email' => $trabajador->correo,
                'activo' => $datos['acceso'] && $trabajador->activo,
            ]);
        } elseif ($datos['acceso']) {
            $usuario = User::create([
                'name' => $nombre,
                'email' => $trabajador->correo,
                'password' => Str::password(32),
                'tipo' => 'trabajador',
                'activo' => (bool) ($trabajador->activo ?? true),
            ]);
            $trabajador->update(['user_id' => $usuario->id]);
            $trabajador->setRelation('user', $usuario);
        }
    }

    private function avisoAcceso(Trabajador $trabajador, bool $acceso): string
    {
        if (! $acceso || ! $trabajador->user) {
            return '';
        }

        $estado = Password::sendResetLink(['email' => $trabajador->user->email]);

        return $estado === Password::RESET_LINK_SENT
            ? " Se envió a {$trabajador->user->email} un enlace para que cree su contraseña."
            : ' No se pudo enviar el enlace para crear la contraseña; envíalo desde Cuentas de acceso.';
    }

    private function rolesDe(Collection $ids): Collection
    {
        return DB::table('rol_trabajador')
            ->leftJoin('roles', 'roles.id', '=', 'rol_trabajador.rol_id')
            ->whereIn('rol_trabajador.trabajador_id', $ids)
            ->get(['rol_trabajador.trabajador_id', DB::raw('COALESCE(roles.codigo, rol_trabajador.rol) as codigo')])
            ->groupBy('trabajador_id')
            ->map(fn ($filas) => $filas->pluck('codigo')->unique()->values());
    }

    private function esUnoMismo(Trabajador $trabajador): bool
    {
        return $trabajador->user_id !== null && $trabajador->user_id === auth()->id();
    }
}
