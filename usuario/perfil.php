<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: auth/login.php");
    exit();
}

include '../includes/conexion.php';

// Obtener la información del usuario
$usuario_id = $_SESSION['usuario_id'];
$sql_usuario = "SELECT nombre_usuario, email FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql_usuario);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();

// Manejar la actualización del perfil
$error = ''; // Inicializar la variable de error
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar_perfil'])) {
    $nuevo_nombre = trim($_POST['nombre_usuario']);
    $nuevo_email = trim($_POST['email']);

    // Validar que el nuevo correo electrónico no esté ya en uso
    $sql_email_check = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
    $stmt_email_check = $conn->prepare($sql_email_check);
    $stmt_email_check->bind_param("si", $nuevo_email, $usuario_id);
    $stmt_email_check->execute();
    $resultado_email_check = $stmt_email_check->get_result();

    if ($resultado_email_check->num_rows > 0) {
        $error = "El correo electrónico ya está en uso.";
    } else {
        $sql_actualizar = "UPDATE usuarios SET nombre_usuario = ?, email = ? WHERE id = ?";
        $stmt_actualizar = $conn->prepare($sql_actualizar);
        $stmt_actualizar->bind_param("ssi", $nuevo_nombre, $nuevo_email, $usuario_id);

        if ($stmt_actualizar->execute()) {
            $_SESSION['nombre_usuario'] = $nuevo_nombre;  // Actualizar el nombre en la sesión
            header("Location: perfil.php?mensaje=Perfil actualizado con éxito");
            exit();
        } else {
            $error = "Error al actualizar el perfil.";
        }
    }
}

// Manejar el cambio de contraseña
$error_contrasena = ''; // Inicializar la variable de error
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cambiar_contrasena'])) {
    $contrasena_actual = $_POST['contrasena_actual'];
    $nueva_contrasena = $_POST['nueva_contrasena'];
    $confirmar_contrasena = $_POST['confirmar_contrasena'];

    // Verificar la contraseña actual
    $sql_contrasena = "SELECT contrasena FROM usuarios WHERE id = ?";
    $stmt_contrasena = $conn->prepare($sql_contrasena);
    $stmt_contrasena->bind_param("i", $usuario_id);
    $stmt_contrasena->execute();
    $resultado_contrasena = $stmt_contrasena->get_result();
    $usuario_contrasena = $resultado_contrasena->fetch_assoc();

    if (password_verify($contrasena_actual, $usuario_contrasena['contrasena'])) {
        if ($nueva_contrasena === $confirmar_contrasena) {
            // Cambiar la contraseña
            $hash_contrasena = password_hash($nueva_contrasena, PASSWORD_DEFAULT);
            $sql_actualizar_contrasena = "UPDATE usuarios SET contrasena = ? WHERE id = ?";
            $stmt_actualizar_contrasena = $conn->prepare($sql_actualizar_contrasena);
            $stmt_actualizar_contrasena->bind_param("si", $hash_contrasena, $usuario_id);

            if ($stmt_actualizar_contrasena->execute()) {
                header("Location: perfil.php?mensaje=Contraseña actualizada con éxito");
                exit();
            } else {
                $error_contrasena = "Error al actualizar la contraseña.";
            }
        } else {
            $error_contrasena = "Las nuevas contraseñas no coinciden.";
        }
    } else {
        $error_contrasena = "La contraseña actual es incorrecta.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2>Perfil de Usuario</h2>

        <?php if (isset($_GET['mensaje'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_GET['mensaje']); ?>
            </div>
        <?php endif; ?>

        <!-- Mostrar el perfil -->
        <form action="perfil.php" method="POST">
            <div class="form-group">
                <label for="nombre_usuario">Nombre de Usuario:</label>
                <input type="text" name="nombre_usuario" class="form-control" value="<?php echo htmlspecialchars($usuario['nombre_usuario']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Correo Electrónico:</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
            </div>
            <?php if ($error): ?>
                <div class="alert alert-danger mt-2">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <button type="submit" name="actualizar_perfil" class="btn btn-primary">Actualizar Perfil</button>
        </form>

        <hr>

        <!-- Cambiar contraseña -->
        <h3>Cambiar Contraseña</h3>
        <form action="perfil.php" method="POST">
            <div class="form-group">
                <label for="contrasena_actual">Contraseña Actual:</label>
                <input type="password" name="contrasena_actual" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="nueva_contrasena">Nueva Contraseña:</label>
                <input type="password" name="nueva_contrasena" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="confirmar_contrasena">Confirmar Nueva Contraseña:</label>
                <input type="password" name="confirmar_contrasena" class="form-control" required>
            </div>
            <button type="submit" name="cambiar_contrasena" class="btn btn-warning">Cambiar Contraseña</button>

            <?php if (isset($error_contrasena)): ?>
                <div class="alert alert-danger mt-2">
                    <?php echo htmlspecialchars($error_contrasena); ?>
                </div>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
