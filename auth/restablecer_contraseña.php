<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_GET['token'])) {
    include 'conexion.php';
    
    $token = $_GET['token'];
    $nueva_contrasena = password_hash($_POST['nueva_contrasena'], PASSWORD_DEFAULT);

    // Verificar el token
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE token_recuperacion = ? AND token_expiracion > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        // Actualizar la contraseña
        $stmt = $conn->prepare("UPDATE usuarios SET contrasena = ?, token_recuperacion = NULL, token_expiracion = NULL WHERE token_recuperacion = ?");
        $stmt->bind_param("ss", $nueva_contrasena, $token);
        $stmt->execute();

        echo "Tu contraseña ha sido restablecida.";
    } else {
        echo "El token no es válido o ha expirado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer Contraseña</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2>Restablecer Contraseña</h2>
        <form action="auth/restablecer_contraseña.php?token=<?php echo htmlspecialchars($_GET['token']); ?>" method="POST"> <!-- Ruta actualizada -->
            <div class="form-group">
                <label for="nueva_contraseña">Nueva Contraseña</label>
                <input type="password" name="nueva_contrasena" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Restablecer</button>
        </form>
    </div>
</body>
</html>
