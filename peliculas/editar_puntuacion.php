<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: auth/login.php"); // Redirigir a la página de inicio de sesión
    exit();
}

include '../includes/conexion.php'; // Ajusta la ruta para incluir conexion.php

// Manejar el envío del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pelicula_id = $_POST['pelicula_id'];
    $nueva_puntuacion = $_POST['puntuacion'];
    $usuario_id = $_SESSION['usuario_id'];

    // Actualizar la puntuación en la base de datos
    $stmt = $conn->prepare("UPDATE puntuaciones SET puntuacion = ? WHERE pelicula_id = ? AND usuario_id = ?");
    $stmt->bind_param("iii", $nueva_puntuacion, $pelicula_id, $usuario_id);
    $stmt->execute();

    // Redirigir a la lista de películas con un mensaje de éxito
    header("Location: peliculas.php?mensaje=Puntuación actualizada con éxito");
    exit();
}

// Obtener la película y la puntuación actual del usuario
$pelicula_id = $_GET['pelicula_id'];
$usuario_id = $_SESSION['usuario_id'];

$stmt = $conn->prepare("SELECT p.titulo, pu.puntuacion FROM peliculas p
                         LEFT JOIN puntuaciones pu ON p.id = pu.pelicula_id AND pu.usuario_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$pelicula = $resultado->fetch_assoc();

if (!$pelicula) {
    // Redirigir si no se encuentra la película
    header("Location: peliculas.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Puntuación</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h2 class="text-center">Editar Puntuación de "<?php echo htmlspecialchars($pelicula['titulo']); ?>"</h2>
        
        <form action="editar_puntuacion.php" method="POST" novalidate>
            <input type="hidden" name="pelicula_id" value="<?php echo $pelicula_id; ?>">
            <div class="form-group">
                <label for="puntuacion">Puntuación (1-5):</label>
                <input type="number" name="puntuacion" class="form-control" min="1" max="5" value="<?php echo htmlspecialchars($pelicula['puntuacion']); ?>" required>
            </div>
            <button type="submit" class="btn btn-success btn-block">Actualizar Puntuación</button>
        </form>

        <div class="text-right mt-3">
            <a href="peliculas.php" class="btn btn-primary">Volver a la Lista de Películas</a>
        </div>
    </div>
</body>
</html>
