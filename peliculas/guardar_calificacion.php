<?php
session_start();
include '../includes/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: auth/login2.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$movie_id = isset($_POST['movie_id']) ? intval($_POST['movie_id']) : 0;
$calificacion = isset($_POST['calificacion']) ? intval($_POST['calificacion']) : 0;

// Verificar que la película existe en la base de datos
$verificar_pelicula = $conn->prepare("SELECT id FROM peliculas WHERE id = ?");
$verificar_pelicula->bind_param("i", $movie_id);
$verificar_pelicula->execute();
$verificar_resultado = $verificar_pelicula->get_result();

if ($verificar_resultado->num_rows === 0) {
    echo "Error: La película no existe.";
    exit();
}

if ($movie_id && $calificacion >= 1 && $calificacion <= 5) {
    // Verificar si el usuario ya calificó esta película
    $stmt = $conn->prepare("SELECT id FROM calificaciones WHERE usuario_id = ? AND movie_id = ?");
    $stmt->bind_param("ii", $usuario_id, $movie_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Actualizar calificación existente
        $stmt = $conn->prepare("UPDATE calificaciones SET calificacion = ?, fecha = CURRENT_TIMESTAMP WHERE usuario_id = ? AND movie_id = ?");
        $stmt->bind_param("iii", $calificacion, $usuario_id, $movie_id);
    } else {
        // Insertar nueva calificación
        $stmt = $conn->prepare("INSERT INTO calificaciones (usuario_id, movie_id, calificacion) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $usuario_id, $movie_id, $calificacion);
    }

    if ($stmt->execute()) {
        header("Location: detalle_pelicula.php?id=" . $movie_id . "&mensaje=Calificación guardada exitosamente");
        exit();
    } else {
        echo "Error: No se pudo guardar la calificación.";
    }

    $stmt->close();
} else {
    echo "Error: Calificación inválida.";
}

$conn->close();
?>
