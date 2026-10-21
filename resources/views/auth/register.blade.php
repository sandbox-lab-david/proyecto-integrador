<x-guest-layout>
    <div class="student-shell" style="max-width: 600px; padding-top: 8vh;">
        <div class="step-panel active">
            
            <div class="form-heading" style="justify-content: center; margin-bottom: 24px;">
                <h2 style="text-align: center;">Crear Cuenta</h2>
            </div>

            @if ($errors->any())
                <div class="notice warning mb-4">
                    Revisa los campos en rojo.
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" style="display: grid; gap: 16px;">
                @csrf

                <div class="field">
                    <label for="name">Nombres y Apellidos</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus>
                </div>

                <div class="field">
                    <label for="email">Correo Institucional</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required>
                </div>
                
                <div class="field">
                    <label for="tipo">Tipo de Usuario</label>
                    <select id="tipo" name="tipo" required>
                        <option value="estudiante">Estudiante</option>
                        <option value="trabajador">Trabajador (Asistente/Director)</option>
                    </select>
                </div>

                <!-- Uso de la clase form-grid del CSS para dos columnas -->
                <div class="form-grid">
                    <div class="field">
                        <label for="password">Contraseña</label>
                        <input id="password" type="password" name="password" required>
                    </div>

                    <div class="field">
                        <label for="password_confirmation">Confirmar Contraseña</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="{{ route('login') }}" class="text-btn">Ya tengo cuenta</a>
                    <button type="submit" class="primary-btn">Registrarse</button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>