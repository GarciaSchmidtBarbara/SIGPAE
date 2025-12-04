<x-mail::message>
# Solicitud de Restablecimiento de Contraseña

¡Hola!

Recibimos una solicitud para restablecer la contraseña de tu cuenta SIGPAE.

Haz clic en el botón de abajo para cambiar tu contraseña. Este enlace expirará en **{{ config('auth.passwords.profesionales.expire') }} minutos**.

<x-mail::button :url="$url">
Restablecer Contraseña
</x-mail::button>

Si no solicitaste un restablecimiento de contraseña, no se requiere ninguna otra acción.

Gracias por usar SIGPAE,  
El equipo de SIGPAE

@slot('subcopy')
Si tienes problemas para hacer clic en el botón "Restablecer Contraseña", copia y pega la siguiente URL en tu navegador web: [{{ $url }}]({{ $url }})
@endslot
</x-mail::message>
