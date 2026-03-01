<x-mail::message>
# Bienvenido/a a SIGPAE

¡Hola {{ $profesional->nombre }} {{ $profesional->apellido }}!

Su cuenta ha sido creada correctamente en el sistema **SIGPAE**.

### Datos de acceso:

**Usuario:** {{ $profesional->usuario }}

Para activar su cuenta y definir su contraseña, haga clic en el siguiente botón:

<x-mail::button :url="$url">
Activar cuenta
</x-mail::button>

Este enlace expirará en **{{ config('auth.passwords.profesionales.expire') }} minutos**.

Una vez creada su contraseña, deberá completar sus datos personales antes de acceder al sistema.

Si usted no solicitó esta cuenta, puede ignorar este mensaje.

Saludos cordiales,  
Equipo SIGPAE

@slot('subcopy')
Si tiene problemas para hacer clic en el botón "Activar cuenta", copie y pegue la siguiente URL en su navegador:

{{ $url }}
@endslot
</x-mail::message>