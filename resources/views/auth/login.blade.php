<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Iniciar Sesión | {{ $config->nombre_empresa ?? 'Dizany' }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        /* ===============================
   BASE
================================ */
body {
    background: linear-gradient(135deg, #0f172a, #1e3a8a);
    height: 100vh;
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Segoe UI', sans-serif;
}

/* ===============================
   LOGIN CARD
================================ */
.login-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 42px 34px;
    width: 100%;
    max-width: 420px;
    text-align: center;
    box-shadow:
        0 20px 40px rgba(0,0,0,0.18),
        0 4px 10px rgba(0,0,0,0.08);
    animation: fadeIn 0.6s ease-in-out;
}

/* ===============================
   LOGO
================================ */
.login-logo {
    height: 110px;
    width: auto;
    max-width: 240px;
    object-fit: contain;
    margin-bottom: 12px;
    filter: drop-shadow(0 6px 12px rgba(0,0,0,0.18));
}

/* Separador bajo el logo */
.logo-divider {
    width: 48px;
    height: 3px;
    background: #84cc16; /* verde del logo */
    border-radius: 2px;
    margin: 10px auto 18px;
}

/* ===============================
   TEXTOS
================================ */
.login-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 6px;
    color: #1f2937;
}

.login-subtitle {
    font-size: 0.95rem;
    color: #6b7280;
    margin-bottom: 26px;
}

.footer-text {
    font-size: 0.85rem;
    color: #9ca3af;
    margin-top: 22px;
}

/* ===============================
   FORM
================================ */
.form-control {
    border-radius: 8px;
    height: 44px;
}

.input-group-text {
    background-color: #f3f4f6;
    border-radius: 8px 0 0 8px;
}

.password-toggle {
    cursor: pointer;
}

/* ===============================
   BUTTON
================================ */
.btn-login {
    background-color: #2563eb;
    color: white;
    font-weight: 600;
    border-radius: 8px;
    padding: 12px;
    transition: all 0.2s ease-in-out;
}

.btn-login:hover {
    background-color: #1d4ed8;
    transform: translateY(-1px);
}

/* ===============================
   LINKS
================================ */
a.text-primary {
    color: #2563eb !important;
}

a.text-primary:hover {
    text-decoration: underline;
}

/* ===============================
   ERROR
================================ */
.error-message {
    color: #dc2626;
    font-size: 0.9rem;
    margin-bottom: 12px;
    display: none;
}

/* ===============================
   ANIMATIONS
================================ */
@keyframes shake {
    0% { transform: translateX(0); }
    25% { transform: translateX(-6px); }
    50% { transform: translateX(6px); }
    75% { transform: translateX(-6px); }
    100% { transform: translateX(0); }
}

.shake {
    animation: shake 0.3s ease-in-out 2;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

    </style>
</head>
<body>

        <div class="login-card" id="loginCard">
        {{-- Logo dinámico --}}
        @if($config && $config->logo)
            <img src="{{ asset($config->logo) }}" alt="Logo" class="login-logo">
        @else
            <img src="{{ asset('images/logo.png') }}" alt="Logo por defecto" class="login-logo">
        @endif

        <div class="logo-divider"></div>

        {{-- Nombre de empresa dinámico --}}
        <h1 class="login-title">
            {{ $config->nombre_empresa ?? 'Dizany' }}
        </h1>

        <p class="login-subtitle">
            Bienvenido, por favor inicia sesión
        </p>


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
