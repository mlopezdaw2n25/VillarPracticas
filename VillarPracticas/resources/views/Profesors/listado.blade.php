<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reservar Hora</title>

    <!-- Fuente -->
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
        /* NAVBAR SUPERIOR */
        /* ===================== */

        .navbar {
            background: white;
            border-bottom: 1px solid #e5e5e5;
        }

        .navbar-container {
            max-width: 1300px;
            margin: auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo img {
            height: 40px;
        }

        .menu {
            display: flex;
            gap: 2rem;
        }

        .menu a,
        .dropdown span {
            text-decoration: none;
            color: #222;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
        }

        .menu a:hover,
        .dropdown span:hover {
            color: #3b5cff;
        }

        .lang {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.9rem;
        }

        .lang img {
            width: 18px;
        }

        /* ===================== */
        /* LAYOUT */
        /* ===================== */

        .layout {
            display: flex;
            height: calc(100vh - 60px);
        }

        /* Navbar lateral */
        .side-navbar {
            width: 220px;
            background: rgba(255,255,255,0.9);
            padding: 1.5rem 1rem;
            box-shadow: 4px 0 12px rgba(0,0,0,0.1);
        }

        .side-navbar button {
            width: 100%;
            padding: 0.7rem;
            margin-bottom: 0.8rem;
            border: none;
            border-radius: 8px;
            background: #667eea;
            color: white;
            font-size: 0.95rem;
            cursor: pointer;
        }

        .side-navbar button:hover {
            background: #5a67d8;
        }

        /* Contenido central */
        .content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 4rem;
        }

        /* ===================== */
        /* TARJETA RESERVA */
        /* ===================== */

        .reservation-card {
            background: white;
            padding: 2.5rem;
            border-radius: 16px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.25);
            width: 100%;
            max-width: 420px;
        }

        .reservation-card h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: 500;
            color: #333;
        }

        .info {
            background: #f1f5f9;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        .info strong {
            display: block;
            margin-bottom: 0.3rem;
            color: #444;
        }

        label {
            display: block;
            margin-bottom: 0.4rem;
            font-size: 0.85rem;
            color: #555;
        }

        input, select, textarea {
            width: 100%;
            padding: 0.7rem;
            margin-bottom: 1.1rem;
            border-radius: 8px;
            border: 1px solid #ddd;
            font-size: 0.9rem;
        }

        textarea {
            resize: none;
            height: 80px;
        }

        button.confirm {
            width: 100%;
            padding: 0.8rem;
            border: none;
            border-radius: 10px;
            background: #667eea;
            color: white;
            font-size: 0.95rem;
            cursor: pointer;
        }

        button.confirm:hover {
            background: #5a67d8;
        }

        .back {
            margin-top: 1rem;
            text-align: center;
            font-size: 0.85rem;
        }

        .back a {
            color: #667eea;
            text-decoration: none;
        }
    </style>
</head>

<body>

<!-- NAVBAR SUPERIOR -->
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

<!-- LAYOUT -->
<div class="layout">

    <!-- NAVBAR LATERAL -->
    <aside class="side-navbar">
        <button>Inicio</button>
        <button>Mis reservas</button>
        <button>Cerrar sesión</button>
    </aside>

    <!-- CONTENIDO -->
    <main class="content">
        <div class="reservation-card">
            <h2>Reservar Hora</h2>

            <div class="info">
                <strong>Día:</strong> Lunes  
                <strong>Hora:</strong> 09:00 - 10:00
            </div>

            <form>
                <label>Nombre</label>
                <input type="text" placeholder="Tu nombre" required>

                <label>Plató</label>
                <select required>
                    <option value="">Selecciona un plató</option>
                    <option>Plató 1</option>
                    <option>Plató 2</option>
                    <option>Plató Superior</option>
                </select>

                <label>Material</label>
                <select required>
                    <option value="">Selecciona material</option>
                    <option>Cámara</option>
                    <option>Microfonos</option>
                    <option>Cables varios</option>
                </select>

                <label>Observaciones</label>
                <textarea placeholder="Opcional"></textarea>

                <button type="submit" class="confirm">Confirmar reserva</button>
            </form>

            <div class="back">
                <a href="index.html">← Volver al calendario</a>
            </div>
        </div>
    </main>

</div>

</body>
</html>
