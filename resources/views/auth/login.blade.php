<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Iniciar Sesión | {{ $config->nombre_empresa ?? 'Dizany' }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }

        .login-card {
            background: #fff;
            border-radius: 15px;
            padding: 40px 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            position: relative;
        }

        .login-card img.logo {
            width: 80px;
            margin-bottom: 15px;
        }

        .login-card h1 {
            font-size: 24px;
            font-weight: 700;
            color: #333;
        }

        .login-card p {
            font-size: 14px;
            color: #666;
            margin-bottom: 25px;
        }

        .form-control {
            border-radius: 8px;
        }

        .input-group-text {
            background-color: #f0f0f0;
        }

        .btn-login {
            background-color: #0069ed;
            color: white;
            font-weight: bold;
            border-radius: 8px;
            padding: 10px;
        }

        .btn-login:hover {
            background-color: #0053ba;
            color: white;
        }

        .footer-text {
            font-size: 0.85rem;
            color: #aaa;
            margin-top: 20px;
        }

        .password-toggle {
            cursor: pointer;
        }

        .shake {
            animation: shake 0.3s ease-in-out 2;
        }

        @keyframes shake {
            0% { transform: translateX(0); }
            25% { transform: translateX(-6px); }
            50% { transform: translateX(6px); }
            75% { transform: translateX(-6px); }
            100% { transform: translateX(0); }
        }

        .error-message {
            color: #dc3545;
            font-size: 0.9rem;
            margin-bottom: 10px;
            display: none;
        }
    </style>
</head>
<body>

        <div class="login-card" id="loginCard">
        {{-- Logo dinámico --}}
        @if($config && $config->logo)
                <img src="{{ asset($config->logo) }}" alt="Logo" class="logo">
            @else
                <img src="{{ asset('images/logo.png') }}" alt="Logo por defecto" class="logo">
            @endif
        {{-- Nombre de empresa dinámico --}}
        <h1>
            {{ $config->nombre_empresa ?? 'Dizany' }}
        </h1>

        <p>Bienvenido, por favor inicia sesión</p>

        <div id="error-message" class="error-message"></div>

        <form id="loginForm" method="POST">
            @csrf
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control" name="usuario" required placeholder="Usuario">
                </div>
            </div>

            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" id="password" name="password" required placeholder="Contraseña">
                    <span class="input-group-text password-toggle" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggle-icon"></i>
                    </span>
                </div>
            </div>

            <button type="submit" class="btn btn-login w-100">Iniciar Sesión</button>

            <div class="mt-3 text-end">
                <a href="{{ route('password.request') }}" class="text-decoration-none text-primary small">¿Olvidaste tu contraseña?</a>
            </div>
        </form>

        <p class="footer-text">&copy; {{ date('Y') }} {{ $config->nombre_empresa ?? 'Dizany' }}. Todos los derechos reservados.</p>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById("password");
            const icon = document.getElementById("toggle-icon");
            input.type = input.type === "password" ? "text" : "password";
            icon.classList.toggle("fa-eye");
            icon.classList.toggle("fa-eye-slash");
        }

        document.addEventListener("DOMContentLoaded", function () {
            const form = document.getElementById("loginForm");
            const errorDiv = document.getElementById("error-message");
            const loginCard = document.getElementById("loginCard");

            form.addEventListener("submit", function (e) {
                e.preventDefault();

                const formData = new FormData(form);

                fetch("{{ route('login.ajax') }}", {
                    method: "POST",
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = data.redirect_to;
                    } else {
                        showError(data.message || 'Credenciales incorrectas');
                    }
                })
                .catch(() => showError("Error de servidor o conexión"));
            });

            function showError(msg) {
                errorDiv.textContent = msg;
                errorDiv.style.display = "block";
                loginCard.classList.add("shake");

                setTimeout(() => {
                    errorDiv.style.display = "none";
                    loginCard.classList.remove("shake");
                }, 3000);
            }
        });
    </script>

</body>
</html>
