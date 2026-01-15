<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Centre Villar</title>

    <!-- Fuente minimalista -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500&display=swap" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        /* ===================== */
        /* NAVBAR */
        /* ===================== */

        .navbar {
            background: #ffffff;
            border-bottom: 1px solid #e5e5e5;
        }

        .navbar-container {
            max-width: 1300px;
            margin: auto;
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo img {
            height: 40px;
        }

        .menu {
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .menu a,
        .dropdown span {
            text-decoration: none;
            color: #222;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: color 0.2s;
        }

        .menu a:hover,
        .dropdown span:hover {
            color: #3b5cff;
        }

        .lang {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            cursor: pointer;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .lang img {
            width: 18px;
        }

        /* ===================== */
        /* LOGIN */
        /* ===================== */

        .login-wrapper {
            min-height: calc(100vh - 80px);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            background: #ffffff;
            padding: 2.5rem;
            width: 100%;
            max-width: 380px;
            border-radius: 12px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
        }

        .login-container h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: 500;
            color: #333;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        label {
            display: block;
            margin-bottom: 0.4rem;
            font-size: 0.9rem;
            color: #555;
        }

        input {
            width: 100%;
            padding: 0.7rem;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 0.95rem;
            outline: none;
            transition: border 0.3s;
        }

        input:focus {
            border-color: #667eea;
        }

        button {
            width: 100%;
            padding: 0.8rem;
            border: none;
            border-radius: 8px;
            background: #667eea;
            color: #fff;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background: #5a67d8;
        }
    </style>
</head>
<body>

<!-- NAVBAR -->
<header class="navbar">
    <div class="navbar-container">

        <div class="logo">
            <img src="https://centrevillar.com/wp-content/uploads/2024/02/image-1.png" alt="Centre Villar">
        </div>

        <nav class="menu">
            <a href="#">El Centro</a>
            <a href="#">Proyecto Educativo</a>
            <a href="#">Secretaría</a>
            <div class="dropdown"><span>Cursos ▾</span></div>
            <div class="dropdown"><span>Actualidad ▾</span></div>
            <a href="#">Contacto</a>
        </nav>

        <div class="lang">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/ce/Flag_of_Catalonia.svg/32px-Flag_of_Catalonia.svg.png" alt="CA">
            <span>CA</span>
        </div>

    </div>
</header>

<!-- LOGIN VILLAR PRACTICAS-->
<div class="login-wrapper">
    <div class="login-container">
        <h2>Iniciar sesión</h2>
        <form action="{{ url('/') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="email">Email</label>
                <input type="text" id="email" placeholder="correo@ejemplo.com" name="email" required >
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" placeholder="••••••••" name="password" required>
            </div>

            <button type="submit">Entrar</button>
            <br><br>
            @if(session('error'))
                <p style="color:red; text-align:center;">
                    {{ session('error') }}
                </p>
            @endif

        </form>
    </div>
</div>

</body>
</html>
