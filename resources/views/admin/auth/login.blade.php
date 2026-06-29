<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>HIERROPAR - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style_login.css') }}">

</head>

<body>

    <div class="login-container">
        <h2><i class="fa-solid fa-user-lock"></i> Acceso al Sistema</h2>
        <p class="login-subtitle">Ingresa tus credenciales para continuar</p>

        <form action="{{ route('login') }}" method="POST" novalidate>
            @csrf

            <div class="form-group">
                <label>Correo electrónico</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-envelope input-icon" aria-hidden="true"></i>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="correo@ejemplo.com" required
                        autofocus autocomplete="email">
                </div>
                @error('email')
                    <div class="alert">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group password-group">
                <label>Contraseña</label>
                <div class="password-wrapper">
                    <i class="fa-solid fa-lock input-icon" aria-hidden="true"></i>
                    <input type="password" name="password" id="password" placeholder="••••••••" required
                        autocomplete="current-password">
                    <button type="button" class="toggle-password" aria-label="Mostrar/ocultar contraseña">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                </div>
                @error('password')
                    <div class="alert">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn-login">
                Iniciar sesión
            </button>
        </form>

        <div class="footer">
            © {{ date('Y') }} HIERROPAR Sistema de ventas de ferretería.
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('password');
            const toggle = document.querySelector('.toggle-password');
            const icon = toggle ? toggle.querySelector('i') : null;

            if (!input || !toggle) return;

            toggle.addEventListener('click', () => {
                const wasPassword = input.type === 'password';
                input.type = wasPassword ? 'text' : 'password';

                if (icon) {
                    icon.classList.toggle('fa-eye', !wasPassword);
                    icon.classList.toggle('fa-eye-slash', wasPassword);
                }
            });
        });
    </script>
</body>
</html>
