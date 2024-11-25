<?php
session_start();
include_once '../includes/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../auth/login2.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_perfil'])) {
    $nombre_usuario = $_POST['nombre_usuario'] ?? null;
    $email = $_POST['email'] ?? null;
    $foto_perfil = $_FILES['foto_perfil'] ?? null;

    if ($nombre_usuario === null || $email === null) {
        echo "Error: Nombre de usuario o correo no proporcionado.";
        exit();
    }

    $directorio_destino = '../uploads/';
    if (!is_dir($directorio_destino)) {
        mkdir($directorio_destino, 0755, true);
    }

    $ruta_completa = null;
    if (!empty($foto_perfil['name'])) {
        $nombre_archivo = $usuario_id . '_' . basename($foto_perfil['name']);
        $ruta_completa = $directorio_destino . $nombre_archivo;

        if (!move_uploaded_file($foto_perfil['tmp_name'], $ruta_completa)) {
            echo "Error: No se pudo cargar la foto de perfil.";
            $ruta_completa = null;
        }
    }

    if ($ruta_completa) {
        $stmt = $conn->prepare("UPDATE usuarios SET nombre_usuario = ?, email = ?, foto_perfil = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nombre_usuario, $email, $ruta_completa, $usuario_id);
    } else {
        $stmt = $conn->prepare("UPDATE usuarios SET nombre_usuario = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $nombre_usuario, $email, $usuario_id);
    }

    if ($stmt->execute()) {
        $_SESSION['nombre_usuario'] = $nombre_usuario;
        $_SESSION['email'] = $email;
        if ($ruta_completa) {
            $_SESSION['foto_perfil'] = $ruta_completa;
        }
        header("Location: perfil.php?mensaje=perfil_actualizado");
        exit();
    } else {
        echo "Error: No se pudo actualizar el perfil.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-dark text-light">
    <div class="container mt-5">
        <h2 class="text-warning text-center">Editar Perfil</h2>
        <div class="card bg-secondary shadow mt-4">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="nombre_usuario" class="form-label">Nombre de Usuario</label>
                        <input type="text" id="nombre_usuario" name="nombre_usuario" class="form-control" value="<?php echo htmlspecialchars($_SESSION['nombre_usuario'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="foto_perfil" class="form-label">Foto de Perfil</label>
                        <input type="file" id="foto_perfil" name="foto_perfil" class="form-control">
                    </div>
                    <button type="submit" name="actualizar_perfil" class="btn btn-warning">Guardar Cambios</button>
                    <a href="perfil.php" class="btn btn-secondary">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
