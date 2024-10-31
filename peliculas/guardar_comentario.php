<?php
session_start();
include '../includes/conexion.php'; // Conexión a la base de datos

// Verifica que el usuario esté autenticado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: auth/login2.php"); // Redirigir al inicio de sesión si no está autenticado
    exit();
}

// Obtiene los datos del formulario
$usuario_id = $_SESSION['usuario_id'];
$movie_id = isset($_POST['movie_id']) ? intval($_POST['movie_id']) : 0;
$comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';

if ($movie_id && $comentario) {
    // Insertar el comentario en la base de datos
    $stmt = $conn->prepare("INSERT INTO comentarios (usuario_id, movie_id, comentario) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $usuario_id, $movie_id, $comentario);

    if ($stmt->execute()) {
        header("Location: detalle_pelicula.php?id=" . $movie_id . "&mensaje=Comentario agregado exitosamente");
        exit();
    } else {
        echo "Error: No se pudo guardar el comentario.";
    }

    $stmt->close();
} else {
    echo "Error: El comentario no puede estar vacío.";
}

$conn->close();
?>