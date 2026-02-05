<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Centre Villar</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Fuente -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/home.css') }}">
</head>

<body>

<!-- NAVBAR -->
<header class="navbar">
    <div class="navbar-container">

        <div class="logo">
            <img src="{{ asset('img/image.png') }}" alt="Centre Villar">
        </div>

        <nav class="menu">
            <a href="https://centrevillar.com/el-centro/">El Centro</a>
            <a href="https://centrevillar.com/proyecto-educativo/">Proyecto Educativo</a>
            <a href="https://centrevillar.com/secretaria-es/">Secretaría</a>
            <a href="https://centrevillar.com/contacto/">Contacto</a>
        </nav>

    </div>
</header>

<!-- LOGIN -->
<div class="login-wrapper">
    <div class="login-container">

        <h2>Iniciar sesión</h2>

        <form action="{{ url('/') }}" method="POST">
            @csrf

            <div class="form-group">
                <label>Email</label>
                <input type="text" name="email" placeholder="correo@ejemplo.com" required>
            </div>

            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit">Entrar</button>

            @if(session('error'))
                <p class="error">{{ session('error') }}</p>
            @endif

        </form>

    </div>
</div>

</body>
</html>
