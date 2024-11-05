<?php
session_start();
include_once '../includes/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../auth/login2.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$movie_id = $_POST['movie_id'];
$estado = $_POST['estado'];

// Verificar si la película ya está en el historial
$stmt = $conn->prepare("SELECT * FROM historial WHERE usuario_id = ? AND pelicula_id = ?");
$stmt->bind_param("ii", $usuario_id, $movie_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Actualizar el estado si ya existe
    $stmt_update = $conn->prepare("UPDATE historial SET estado = ? WHERE usuario_id = ? AND pelicula_id = ?");
    $stmt_update->bind_param("sii", $estado, $usuario_id, $movie_id);
    $stmt_update->execute();
    $stmt_update->close();
} else {
    // Insertar una nueva entrada en el historial si no existe
    $stmt_insert = $conn->prepare("INSERT INTO historial (usuario_id, pelicula_id, estado) VALUES (?, ?, ?)");
    $stmt_insert->bind_param("iis", $usuario_id, $movie_id, $estado);
    $stmt_insert->execute();
    $stmt_insert->close();
}

$stmt->close();
header("Location: detalle_pelicula.php?id=$movie_id");
exit();
?>
