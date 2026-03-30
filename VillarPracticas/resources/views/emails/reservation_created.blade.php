<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
</head>
<body style="font-family: Arial, sans-serif;">

    <h2>Confirmación de reserva</h2>

    Hola {{ $user->display_name ?? 'Usuario' }},


    <p>Tu reserva se ha creado correctamente con los siguientes datos:</p>

    <p><strong>Fecha:</strong> {{ $date }}</p>

    @foreach($reservations as $reservation)

        <hr>

        <p><strong>Franja horaria:</strong> {{ $reservation['slot_time'] }}</p>

        <ul>
            @foreach($reservation['items'] as $item)
                <li>
                    {{ $item['name'] }} x {{ $item['quantity'] }}
                </li>
            @endforeach
        </ul>

    @endforeach

    <hr>

    <p>Gracias por utilizar el sistema de reservas.</p>
    <p><strong>Centre Villar</strong></p>

</body>
</html>
