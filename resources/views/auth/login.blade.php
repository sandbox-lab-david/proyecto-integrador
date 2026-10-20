<x-guest-layout>
    <!-- Contenedor centrado para el login -->
    <div class="student-shell" style="max-width: 480px; padding-top: 10vh;">
        
        <!-- Panel tipo tarjeta del CSS de la profesora -->
        <div class="step-panel active">
            
            <div class="form-heading" style="justify-content: center; margin-bottom: 24px;">
                <h2 style="text-align: center;">Iniciar Sesión</h2>
            </div>

            <!-- Mostrar errores de validación si existen -->
            @if ($errors->any())
                <div class="notice warning mb-4">
                    <strong>Error de acceso:</strong> Revisa tus credenciales.
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" style="display: grid; gap: 18px;">
                @csrf

                <!-- Campo Correo -->
                <div class="field">
                    <label for="email">Correo Institucional</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="usuario@uees.edu.ec">
                </div>

                <!-- Campo Contraseña -->
                <div class="field">
                    <label for="password">Contraseña</label>
                    <input id="password" type="password" name="password" required>
                </div>

                <!-- Recordar sesión -->
                <div class="checkbox-field" style="margin-top: 5px;">
                    <input id="remember_me" type="checkbox" name="remember">
                    <label for="remember_me">Mantener sesión iniciada</label>
                </div>

                <!-- Acciones -->
                <div class="form-actions" style="margin-top: 10px;">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-btn">¿Olvidaste tu contraseña?</a>
                    @endif
                    <button type="submit" class="primary-btn">Ingresar</button>
                </div>
            </form>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="{{ route('register') }}" class="text-btn" style="font-size: 0.8rem;">¿No tienes cuenta? Regístrate aquí</a>
            </div>
        </div>
    </div>
</x-guest-layout>