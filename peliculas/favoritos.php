<?php
session_start();
include_once '../includes/conexion.php';

$usuario_id = $_SESSION['usuario_id'];
$movie_id = isset($_POST['movie_id']) ? $_POST['movie_id'] : null;

// Verificar que el usuario esté autenticado y que se reciba el ID de la película
if (!$usuario_id || !$movie_id) {
    echo json_encode(['error' => 'Usuario no autenticado o ID de película no proporcionado']);
    exit();
}

// Comprobar si la película ya está en favoritos
$stmt = $conn->prepare("SELECT * FROM favoritos WHERE usuario_id = ? AND movie_id = ?");
$stmt->bind_param("ii", $usuario_id, $movie_id);
$stmt->execute();
$result = $stmt->get_result();
$en_favoritos = $result->num_rows > 0;
$stmt->close();

// Añadir o eliminar de favoritos según el estado actual
if ($en_favoritos) {
    $stmt = $conn->prepare("DELETE FROM favoritos WHERE usuario_id = ? AND movie_id = ?");
    $stmt->bind_param("ii", $usuario_id, $movie_id);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => 'Eliminado de favoritos']);
} else {
    $stmt = $conn->prepare("INSERT INTO favoritos (usuario_id, movie_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $usuario_id, $movie_id);
    $stmt->execute();
    $stmt->close();
    echo json_encode(['success' => 'Añadido a favoritos']);
}
?>
