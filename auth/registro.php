<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuarios</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2 class="mt-5">Registro de Usuario</h2>
        <form action="auth/registro.php" method="POST"> <!-- Ruta actualizada -->
            <div class="form-group">
                <label for="nombre_usuario">Nombre de Usuario</label>
                <input type="text" name="nombre_usuario" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="contrasena">Contraseña</label>
                <input type="password" name="contrasena" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Registrarse</button>
        </form>
    </div>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        include 'conexion.php'; // Incluir la conexión

        // Recoger los datos del formulario
        $nombre_usuario = $_POST['nombre_usuario'];
        $email = $_POST['email'];
        $contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT); // Encriptar la contraseña

        // Insertar el usuario en la base de datos
        $sql = "INSERT INTO usuarios (nombre_usuario, email, contrasena) VALUES ('$nombre_usuario', '$email', '$contrasena')";

        // Verificar si la inserción fue exitosa
        if ($conn->query($sql) === TRUE) {
            echo "<div class='alert alert-success mt-3'>Registro exitoso.</div>";
        } else {
            echo "<div class='alert alert-danger mt-3'>Error: " . $conn->error . "</div>";
        }

        // Cerrar la conexión
        $conn->close();
    }
    ?>
</body>
</html>
