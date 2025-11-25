<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer Contraseña | Dizany</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap y FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }

        .reset-card {
            background: #fff;
            border-radius: 15px;
            padding: 40px 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            max-width: 420px;
            width: 100%;
            text-align: center;
        }

        .reset-card h4 {
            font-size: 22px;
            font-weight: 700;
            color: #333;
        }

        .form-control {
            border-radius: 8px;
        }

        .btn-reset {
            background-color: #0069ed;
            color: white;
            font-weight: bold;
            border-radius: 8px;
        }

        .btn-reset:hover {
            background-color: #0053ba;
        }

        .volver-login {
            margin-top: 15px;
            display: block;
            font-size: 0.9rem;
            color: #444;
        }

        .volver-login:hover {
            color: #000;
            text-decoration: underline;
        }

        .logo {
            width: 100px;
            margin-bottom: 15px;
        }

        .input-group-text {
            background-color: #f0f0f0;
            cursor: pointer;
        }

        .error-msg {
            background: #f8d7da;
            border: 1px solid #f5c2c7;
            color: #842029;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 0.9rem;
            margin-bottom: 15px;
            text-align: start;
        }
    </style>
</head>
<body>

<div class="reset-card">
    <!--img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo"-->

    <h4><i class="fas fa-lock-open"></i> Nueva Contraseña</h4>
    <p class="text-muted mb-3">Establece una nueva contraseña segura para tu cuenta.</p>

    @if ($errors->any())
        <div class="error-msg">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3 text-start">
            <label for="email" class="form-label">Correo electrónico</label>
            <input type="email" id="email" name="email" class="form-control" value="{{ $email ?? old('email') }}" required>
        </div>

        <div class="mb-3 text-start">
            <label for="password" class="form-label">Nueva contraseña</label>
            <div class="input-group">
                <input type="password" id="password" name="password" class="form-control" required>
                <span class="input-group-text" onclick="togglePassword('password', 'eye1')">
                    <i class="fas fa-eye" id="eye1"></i>
                </span>
            </div>
        </div>

        <div class="mb-3 text-start">
            <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
            <div class="input-group">
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                <span class="input-group-text" onclick="togglePassword('password_confirmation', 'eye2')">
                    <i class="fas fa-eye" id="eye2"></i>
                </span>
            </div>
        </div>

        <button type="submit" class="btn btn-reset w-100 mt-2">
            <i class="fas fa-check-circle me-1"></i> Guardar nueva contraseña
        </button>
    </form>

    <a href="{{ route('login') }}" class="volver-login">
        <i class="fas fa-arrow-left"></i> Volver al inicio de sesión
    </a>
</div>

<script>
    function togglePassword(fieldId, iconId) {
        const input = document.getElementById(fieldId);
        const icon = document.getElementById(iconId);
        const isPassword = input.type === "password";
        input.type = isPassword ? "text" : "password";
        icon.classList.toggle("fa-eye");
        icon.classList.toggle("fa-eye-slash");
    }
</script>

</body>
</html>
