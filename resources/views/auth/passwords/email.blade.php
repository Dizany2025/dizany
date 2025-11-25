<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña | Dizany</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Estilos Bootstrap y FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', sans-serif;
        }

        .card-email {
            background-color: #fff;
            border-radius: 15px;
            padding: 40px 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            max-width: 420px;
            width: 100%;
            text-align: center;
        }

        .card-email h4 {
            font-weight: 700;
            color: #333;
        }

        .form-control {
            border-radius: 8px;
        }

        .btn-enviar {
            background-color: #0069ed;
            color: white;
            font-weight: bold;
            border-radius: 8px;
        }

        .btn-enviar:hover {
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
            font-size: 32px;
            color: #2575fc;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    <div class="card-email">
        <div class="logo">
            <i class="fas fa-unlock-alt"></i>
        </div>
        <h4>¿Olvidaste tu contraseña?</h4>
        <p class="text-muted mb-4">Ingresa tu correo y te enviaremos un enlace para restablecerla.</p>

        <!-- Mensajes -->
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first('email') }}
            </div>
        @endif

        <!-- Formulario -->
        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="mb-3 text-start">
                <label for="email" class="form-label">Correo electrónico</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="ejemplo@correo.com" required autofocus>
            </div>

            <button type="submit" class="btn btn-enviar w-100">
                <i class="fas fa-paper-plane me-1"></i> Enviar enlace
            </button>
        </form>

        <a href="{{ route('login') }}" class="volver-login">
            <i class="fas fa-arrow-left"></i> Volver al inicio de sesión
        </a>
    </div>

</body>
</html>
