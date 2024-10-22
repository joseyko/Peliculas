<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2>Recuperar Contraseña</h2>
        <form action="auth/enviar_recuperacion.php" method="POST"> <!-- Ruta actualizada -->
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Enviar Enlace de Recuperación</button>
        </form>
    </div>
</body>
</html>
