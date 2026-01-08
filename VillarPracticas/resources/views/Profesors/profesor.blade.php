<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Principal</title>

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
            background: rgba(255, 255, 255, 0.9);
            padding: 1.5rem 1rem;
            box-shadow: 4px 0 12px rgba(0, 0, 0, 0.1);
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
        /* CALENDARIO */
        /* ===================== */

        .calendar-container {
            background: white;
            padding: 2rem;
            border-radius: 14px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            max-width: 420px;
            width: 100%;
            height: auto;
        }

        .calendar-container h2 {
            text-align: center;
            margin-bottom: 1.2rem;
            font-weight: 500;
            color: #333;
        }

        /* Días */
        .days {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 0.5rem;
            margin-bottom: 1.2rem;
        }

        .day {
            padding: 0.6rem;
            border: none;
            border-radius: 8px;
            background: #edf2f7;
            cursor: pointer;
            font-size: 0.85rem;
        }

        .day.active {
            background: #667eea;
            color: white;
        }

        /* Horas */
        .slots {
            display: grid;
            gap: 0.5rem;
        }

        .slot {
            padding: 0.6rem;
            border-radius: 8px;
            border: 1px solid #ddd;
            background: #f9fafb;
            cursor: pointer;
            transition: 0.2s;
        }

        .slot:hover {
            background: #667eea;
            color: white;
        }

        .slot.patio {
            background: #fde68a;
            border-color: #f59e0b;
            cursor: default;
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

<!-- LAYOUT -->
<div class="layout">

    <!-- Navbar lateral -->
    <aside class="side-navbar">
        <button>Inicio</button>
        <button>Mis reservas</button>
        <button>Cerrar sesión</button>
    </aside>

    <!-- Contenido -->
    <main class="content">
        <div class="calendar-container">
            <h2>Calendario</h2>

            <div class="days">
                <button class="day active" onclick="selectDay(this)">Lunes</button>
                <button class="day" onclick="selectDay(this)">Martes</button>
                <button class="day" onclick="selectDay(this)">Miércoles</button>
                <button class="day" onclick="selectDay(this)">Jueves</button>
                <button class="day" onclick="selectDay(this)">Viernes</button>
            </div>

            <div class="slots">
                <button class="slot">08:00 - 09:00</button>
                <button class="slot">09:00 - 10:00</button>
                <button class="slot">10:00 - 11:00</button>

                <button class="slot patio">11:00 - 11:30 · Patio</button>

                <button class="slot">11:30 - 12:30</button>
                <button class="slot">12:30 - 13:30</button>
                <button class="slot">13:30 - 14:30</button>
                <button class="slot">14:30 - 15:00</button>
            </div>
        </div>
    </main>

</div>

<script>
function selectDay(button) {
    document.querySelectorAll('.day').forEach(d => d.classList.remove('active'));
    button.classList.add('active');
}
</script>

</body>
</html>
