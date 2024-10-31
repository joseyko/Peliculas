<?php
session_start();
include '../includes/conexion.php';
include_once 'funciones.php'; // Asegúrate de usar include_once y la ruta correcta

if (!isset($_SESSION['usuario_id'])) {
    header("Location: auth/login2.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$movie_id = isset($_POST['movie_id']) ? intval($_POST['movie_id']) : 0;

// Función para agregar o eliminar de favoritos
function toggleFavorito($conn, $usuario_id, $movie_id) {
    if (esFavorito($conn, $usuario_id, $movie_id)) { // Llamada a esFavorito, que ahora debería estar disponible
        $stmt_delete = $conn->prepare("DELETE FROM favoritos WHERE usuario_id = ? AND movie_id = ?");
        $stmt_delete->bind_param("ii", $usuario_id, $movie_id);
        $stmt_delete->execute();
        $stmt_delete->close();
        return "Eliminado de favoritos";
    } else {
        $stmt_insert = $conn->prepare("INSERT INTO favoritos (usuario_id, movie_id) VALUES (?, ?)");
        $stmt_insert->bind_param("ii", $usuario_id, $movie_id);
        $stmt_insert->execute();
        $stmt_insert->close();
        return "Agregado a favoritos";
    }
}

// Ejecutar acción de agregar o quitar favoritos y redirigir
if ($movie_id) {
    $mensaje = toggleFavorito($conn, $usuario_id, $movie_id);
    header("Location: peliculas.php?favorito_exito=1&mensaje=" . urlencode($mensaje));
} else {
    header("Location: peliculas.php?favorito_exito=0&mensaje=" . urlencode("Error: No se pudo agregar a favoritos."));
}

$conn->close();
exit();
