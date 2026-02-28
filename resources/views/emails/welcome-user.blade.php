<x-mail::message>
# Bienvenido/a a SIGPAE

¡Hola {{ $profesional->nombre }} {{ $profesional->apellido }}!

Tu cuenta ha sido creada correctamente en el sistema **SIGPAE**.

Sus datos de acceso son:

**Usuario**: {{ $profesional->usuario }}

Para activar su cuenta y definir su contraseña, haga click en el siguietne enlace:

<x-mail::button :url="$url">
Activar cuenta
</x-mail::button>

Este enlace expirará en **{{ config('auth.passwords.profesionales.expire') }} minutos**.

Una vez creada tu contraseña, deberá completar sus datos personales antes de acceder al sistema.

Si tienes alguna duda, puedes comunicarte con el equipo de soporte.

Gracias por usar SIGPAE,  
El equipo de SIGPAE

@slot('subcopy')
Si tienes problemas para hacer clic en el botón "Crear Contraseña", copia y pega la siguiente URL en tu navegador web: [{{ $url }}]({{ $url }})
@endslot
</x-mail::message>