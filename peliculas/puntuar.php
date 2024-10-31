<?php
session_start();
include '../includes/conexion.php';

$usuario_id = $_SESSION['usuario_id'];
$movie_id = $_POST['movie_id'];
$calificacion = $_POST['calificacion'];

// Verificar si ya existe una puntuación para esta película
$stmt = $conn->prepare("SELECT * FROM calificaciones WHERE usuario_id = ? AND movie_id = ?");
$stmt->bind_param("ii", $usuario_id, $movie_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Actualizar la calificación
    $stmt = $conn->prepare("UPDATE calificaciones SET calificacion = ? WHERE usuario_id = ? AND movie_id = ?");
    $stmt->bind_param("iii", $calificacion, $usuario_id, $movie_id);
} else {
    // Insertar nueva calificación
    $stmt = $conn->prepare("INSERT INTO calificaciones (usuario_id, movie_id, calificacion) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $usuario_id, $movie_id, $calificacion);
}

$stmt->execute();

header("Location: peliculas.php");
exit();
?>
