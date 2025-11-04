<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
</head>
<body>
    
    <h2>Cambio de contraseña</h2>

    <form method="POST" action="{{ route('password.change') }}">
        @csrf
        <input type="password" name="current_password" placeholder="Contraseña actual" required>
        <input type="password" name="new_password" placeholder="Nueva contraseña" required>
        <input type="password" name="new_password_confirmation" placeholder="Confirmar nueva contraseña" required>
        <button type="submit">Cambiar contraseña</button>
    </form>

</body>
</html>