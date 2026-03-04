<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reservar recursos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/listado.css') }}">
</head>

<body>

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

    <aside class="side-navbar">
        <h3>Reserva</h3>

        <a href="{{ url('Profesors/profesor/' . $id) }}">
            <button type="button">⬅ Volver</button>
        </a>

        <a href="{{ route('logout') }}">
            <button type="button">Cerrar sesión</button>
        </a>
    </aside>

    <main class="content">
        <section class="calendar-container">

            <!-- LEFT PANEL -->
            <div class="calendar-left">

                <div class="title">
                    <h2>Selecciona materiales / espacios</h2>
                    <p>Elige la cantidad de cada recurso por franja horaria.</p>
                </div>

                <div class="selected-info" id="summaryBox">
                    Cargando...
                </div>

                <div style="margin-top:1rem;">
                    <button id="btnReserve"
                            class="mini primary"
                            style="width:100%; padding:.9rem;"
                            disabled>
                        Confirmar reserva
                    </button>
                </div>

                <div class="resources-gallery" id="resourceGallery"></div>

                <div id="errorBox"
                     style="margin-top:1rem;color:#b91c1c;font-weight:700;"></div>
            </div>

            <!-- RIGHT PANEL -->
            <div class="calendar-right">

                <div class="title">
                    <h2>Listado</h2>
                    <p>Selecciona recursos para tus franjas.</p>
                </div>

                <div style="margin:12px 0;padding:10px;background:#fff;border-radius:12px;border:1px solid #eee;">
                    <strong>Franjas seleccionadas:</strong>
                    <div id="selectedSlotsBox"
                         style="margin-top:6px;color:#374151;"></div>
                </div>

                <!-- LISTADO POR FRANJAS -->
                <div id="resourcesContainer">

                    @foreach($slots as $slot)
                        <div style="margin-bottom:20px;
                                    background:#fff;
                                    padding:15px;
                                    border-radius:14px;
                                    box-shadow:0 2px 8px rgba(0,0,0,.05);">

                            <h4 style="margin-bottom:10px;">
                                {{ substr($slot->start_time,0,5) }}
                                -
                                {{ substr($slot->end_time,0,5) }}
                            </h4>

                            @if(isset($resourcesBySlot[$slot->id]) && count($resourcesBySlot[$slot->id]) > 0)

                                @foreach($resourcesBySlot[$slot->id] as $resource)

                                    <div style="display:flex;
                                                justify-content:space-between;
                                                align-items:center;
                                                padding:8px 0;
                                                border-bottom:1px solid #f0f0f0;">

                                        <div>
                                            <strong>{{ $resource['name'] }}</strong>
                                            <div style="font-size:13px;color:#6b7280;">
                                                {{ $resource['type'] }}
                                            </div>
                                        </div>

                                        <div style="text-align:right;">

                                            <div style="font-weight:600;">
                                                Disponibles:
                                                {{ $resource['remaining'] }}

                                                @php
                                                    $hasReturnInfo = array_key_exists('last_return_defectuoso', $resource) && $resource['last_return_defectuoso'] !== null;
                                                    $hasIncident = $hasReturnInfo && $resource['last_return_defectuoso'] === true;
                                                    $circleTitle = !$hasReturnInfo
                                                        ? 'Sin devoluciones registradas'
                                                        : ($hasIncident ? 'Última devolución con incidencia' : 'Última devolución sin incidencias');
                                                    $circleColor = $hasIncident ? '#dc2626' : '#16a34a';
                                                @endphp

                                                <span title="{{ $circleTitle }}"
                                                      style="display:inline-block;
                                                             width:10px;
                                                             height:10px;
                                                             background:{{ $circleColor }};
                                                             border-radius:50%;
                                                             margin-left:6px;">
                                                </span>
                                            </div>

                                        </div>

                                    </div>

                                @endforeach

                            @else
                                <p style="color:#6b7280;">No hay recursos disponibles.</p>
                            @endif

                        </div>
                    @endforeach

                </div>

            </div>

        </section>
    </main>
</div>

<script>
    window.__USER_ID__ = @json($id);
</script>

<script src="{{ asset('js/listado.js') }}"></script>

</body>
</html>