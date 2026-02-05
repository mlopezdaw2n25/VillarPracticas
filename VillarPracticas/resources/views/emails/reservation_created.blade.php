Hola {{ $data['user_name'] }},

Tu reserva se ha creado correctamente.

Fecha: {{ $data['date'] }}

Franjas horarias:
@foreach($data['slots'] as $slot)
- {{ $slot }}
@endforeach

Recursos:
@foreach($data['items'] as $item)
- {{ $item }}
@endforeach

Gracias por utilizar Centre Villar.

Un saludo.
