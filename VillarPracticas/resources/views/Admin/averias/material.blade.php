<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
	<title>Panel de Administración</title>

	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="{{ asset('css/admin/averias.css') }}">

</head>

<body>

	<!-- ================= NAVBAR SUPERIOR ================= -->
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

	<div class="layout">

		<!-- ===== SIDEBAR / BOTTOM BAR ===== -->
		<aside class="side-navbar">
			<h3>Gestion</h3>
			<a href="{{ url('/Admin/gestion/usuarios') }}">
				<button class="navbtn" type="button">Usuarios</button>
			</a>
			<a href="{{ url('/Admin/gestion/materiales') }}">
				<button class="navbtn" type="button">Material</button>
			</a>
			<a href="{{ url('/Admin/gestion/espacios') }}">
				<button class="navbtn" type="button">Espacios</button>
			</a>
			<a href="{{ url('/Admin/gestion/reservas') }}">
				<button class="navbtn" type="button">Reservas</button>
			</a>

			<h3 style="margin-top: 1.2rem;">Averias</h3>
			<a href="{{ url('/Admin/averias/material') }}">
				<button class="navbtn active" type="button">Material</button>
			</a>

			<h3 style="margin-top: 1.2rem;">Estadisticas</h3>
			<a href="{{ url('/Admin/estadisticas/espacios') }}">
				<button class="navbtn" type="button">Espacio</button>
			</a>
			<a href="{{ url('/Admin/estadisticas/material') }}">
				<button class="navbtn" type="button">Material</button>
			</a>

			<div class="bottom-actions">
				<a href="{{ route('logout') }}">
					<button class="logout" type="button">Cerrar sesión</button>
				</a>
			</div>
		</aside>

		<!-- ===== CONTENIDO PRINCIPAL ===== -->
		<main class="content">
			<section class="panel">

				<div class="panel-header">
					<div class="panel-title">
						<h2>Averias de material</h2>
						<p>Recepcion y seguimiento de incidencias notificadas.</p>
					</div>

					<div class="toolbar-right">
						<button class="btn btn-outline" type="button">Exportar listado</button>
					</div>
				</div>

				<div class="panel-body">
					<div class="toolbar">
						<div class="search">
							<span>Buscar ticket</span>
							<input type="text" placeholder="ID, material o estado">
						</div>
					</div>

					<section class="card table-card">
						<div class="card-head">
							<h3>Incidencias recibidas</h3>
						</div>
						<div class="card-body">
							<div style="overflow:auto; max-height: 360px;">
								<table>
									<thead>
										<tr>
											<th>ID</th>
											<th>Material</th>
											<th>Problema</th>
											<th>Fecha</th>
											<th>Estado</th>
											<th>Acciones</th>
										</tr>
									</thead>
									<tbody>
									</tbody>
								</table>
							</div>
						</div>
					</section>
				</div>
			</section>
		</main>
	</div>

</body>

</html>
