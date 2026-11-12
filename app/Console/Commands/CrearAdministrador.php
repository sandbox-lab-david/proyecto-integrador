<?php

namespace App\Console\Commands;

use App\Models\Rol;
use App\Models\Trabajador;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CrearAdministrador extends Command
{
    protected $signature = 'admin:crear
        {correo : Correo institucional @uees.edu.ec}
        {nombres : Nombres de la persona}
        {apellidos : Apellidos de la persona}
        {--codigo=ADM-001 : Código de empleado}';

    protected $description = 'Crea un administrador con una contraseña al azar y le envía el enlace para crear la suya';

    public function handle(): int
    {
        $datos = [
            'correo' => mb_strtolower(trim($this->argument('correo'))),
            'nombres' => trim($this->argument('nombres')),
            'apellidos' => trim($this->argument('apellidos')),
            'codigo' => trim($this->option('codigo')),
        ];

        $validador = Validator::make($datos, [
            'correo' => ['required', 'email', 'ends_with:@uees.edu.ec', 'unique:users,email', 'unique:trabajadores,correo'],
            'nombres' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'codigo' => ['required', 'string', 'max:20', 'unique:trabajadores,codigo'],
        ], [
            'correo.ends_with' => 'El correo debe terminar en @uees.edu.ec.',
            'correo.unique' => 'Ya existe una cuenta o una persona con ese correo.',
            'codigo.unique' => 'Ya existe una persona con ese código; usa --codigo para elegir otro.',
        ]);

        if ($validador->fails()) {
            foreach ($validador->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $rolId = Rol::where('codigo', 'admin')->value('id');
        if (! $rolId) {
            $this->error('No existe el rol admin. Ejecuta primero php artisan db:seed.');

            return self::FAILURE;
        }

        $usuario = DB::transaction(function () use ($datos, $rolId) {
            $usuario = User::create([
                'name' => "{$datos['nombres']} {$datos['apellidos']}",
                'email' => $datos['correo'],
                'password' => Str::password(32),
                'tipo' => 'trabajador',
                'activo' => true,
            ]);

            $trabajador = Trabajador::create([
                'user_id' => $usuario->id,
                'codigo' => $datos['codigo'],
                'nombres' => $datos['nombres'],
                'apellidos' => $datos['apellidos'],
                'correo' => $datos['correo'],
                'activo' => true,
            ]);

            DB::table('rol_trabajador')->insert([
                'trabajador_id' => $trabajador->id,
                'rol' => 'admin',
                'rol_id' => $rolId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $usuario;
        });

        $this->info("Administrador creado: {$usuario->name} <{$usuario->email}>.");

        if (Password::sendResetLink(['email' => $usuario->email]) === Password::RESET_LINK_SENT) {
            $this->info('Se envió el enlace para que cree su contraseña.');
        } else {
            $this->warn('No se pudo enviar el enlace. Puede pedirlo desde el inicio de sesión con "¿Primera vez? Crea tu contraseña".');
        }

        return self::SUCCESS;
    }
}
