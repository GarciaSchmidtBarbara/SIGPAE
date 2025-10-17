<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h2>Iniciar sesión</h2>

    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="/login">
        @csrf
        <label for="usuario">Usuario:</label>
        <input type="text" name="usuario" required><br><br>

        <label for="password">Contraseña:</label>
        <input type="password" name="password" required><br><br>

        <button type="submit">Ingresar</button>
    </form>
        <form method="POST" action="/probar-post">
        @csrf
        <button type="submit">Probar POST</button>
    </form>

</body>
</html>
