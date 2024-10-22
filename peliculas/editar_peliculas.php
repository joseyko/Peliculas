<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: auth/login.php"); // Redirigir a la página de inicio de sesión
    exit();
}

include '../includes/conexion.php'; // Ajusta la ruta para incluir conexion.php

// Verificar si se ha pasado el ID de la película
if (!isset($_GET['id'])) {
    header("Location: peliculas.php");
    exit();
}

// Obtener la película por ID
$pelicula_id = $_GET['id'];
$sql = "SELECT p.id, p.titulo, p.descripcion, p.fecha_estreno, p.imagen FROM peliculas p WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pelicula_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header("Location: peliculas.php");
    exit();
}

$pelicula = $resultado->fetch_assoc();

// Manejar el envío del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $fecha_estreno = $_POST['fecha_estreno'];

    // Procesar la imagen
    $imagen = $_FILES['imagen'];
    $ruta_imagen = $pelicula['imagen']; // Mantener la imagen anterior si no se sube una nueva

    if ($imagen['error'] == 0) {
        $ruta_imagen = 'imagenes/' . basename($imagen['name']);
        move_uploaded_file($imagen['tmp_name'], $ruta_imagen);
    }

    // Actualizar la película en la base de datos
    $stmt = $conn->prepare("UPDATE peliculas SET titulo = ?, descripcion = ?, fecha_estreno = ?, imagen = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $titulo, $descripcion, $fecha_estreno, $ruta_imagen, $pelicula_id);
    $stmt->execute();

    header("Location: peliculas.php?mensaje=Película actualizada con éxito");
    exit();
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Película</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2 class="text-center">Editar Película</h2>

        <form action="editar_peliculas.php?id=<?php echo $pelicula['id']; ?>" method="POST" enctype="multipart/form-data" novalidate>
            <div class="form-group">
                <label for="titulo">Título</label>
                <input type="text" name="titulo" class="form-control" value="<?php echo htmlspecialchars($pelicula['titulo']); ?>" required>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea name="descripcion" class="form-control" rows="4" required><?php echo htmlspecialchars($pelicula['descripcion']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="fecha_estreno">Fecha de Estreno</label>
                <input type="date" name="fecha_estreno" class="form-control" value="<?php echo htmlspecialchars($pelicula['fecha_estreno']); ?>" required>
            </div>
            <div class="form-group">
                <label for="imagen">Cargar Imagen (opcional)</label>
                <input type="file" name="imagen" class="form-control" accept="image/*">
            </div>
            <button type="submit" class="btn btn-success btn-block">Actualizar Película</button>
        </form>

        <div class="text-right mt-3">
            <a href="peliculas.php" class="btn btn-primary">Volver a la Lista de Películas</a>
        </div>
    </div>
</body>
</html>
