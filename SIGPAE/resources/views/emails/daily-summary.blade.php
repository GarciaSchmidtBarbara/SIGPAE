<x-mail::message>
# Resumen Diario de Actividades

¡Hola **{{ $profesional->usuario }}**!

Aquí tienes tus eventos programados en SIGPAE para el día de hoy, **{{ now()->format('d/m/Y') }}**, a la hora que solicitaste.

---

## 🗓️ Eventos de Hoy

@if ($eventosHoy->isEmpty())
<p>🎉 ¡No tienes eventos programados para hoy! Disfruta tu tiempo.</p>
@else
<x-mail::table>
| Hora | Tipo | Descripción / Notas |
| :--- | :--- | :--- |
@foreach ($eventosHoy as $evento)
| {{ \Carbon\Carbon::parse($evento->fecha_hora)->format('H:i') }} | {{ $evento->tipo_evento->value }} | {{ $evento->notas ?: 'Sin notas.' }} |
@endforeach
</x-mail::table>
@endif

---

**Nota Importante:**

Si necesitas modificar estos eventos o revisar tu agenda completa, inicia sesión en la plataforma.

Gracias por usar SIGPAE,
<br>
El equipo de SIGPAE
</x-mail::message>