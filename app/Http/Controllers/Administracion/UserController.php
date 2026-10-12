<?php

namespace App\Http\Controllers\Administracion;

use App\Http\Controllers\Controller;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::paginate(10); // Paginamos de 10 en 10 como pide el proyecto
        
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view ('users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'tipo' => 'required|in:estudiante,trabajador',
        ]);

        Users::create([
            'name' => $request->name,
            'email' => $request->email,
            'password'=> Hash::make($request->password),
            'tipo' => $request->tipo,
            'activo' => true,
        ]);

        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(user $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, user $user)
    {
        $request->validate([
            'name'=> 'required|string|max:255',
            'email'=> 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password'=> 'required|string|max:255|confirmed',
            'tipo'=> 'required|in:estudiante,trabajador',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->tipo = $request->tipo;

        if ($request->filled('password')){
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect->route('user.index')->with('Success', 'Usuario actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(user $user)
    {
        $user->update(['activo' => false]);

        return redirect->route('user.index')->with('Succes', 'Usuario desactivado correctamente');
    }
}
