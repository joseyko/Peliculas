<?php
session_start();
include '../includes/conexion.php'; // Ajusta la ruta según tu estructura

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../auth/login2.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';
$movie_id = isset($_POST['movie_id']) ? intval($_POST['movie_id']) : 0;

// Validar que el comentario y movie_id no estén vacíos
if (empty($comentario) || $movie_id <= 0) {
    echo "Comentario inválido o película no especificada.";
    exit();
}

// Verificar si el movie_id existe en la tabla `peliculas`
$stmt_verificar = $conn->prepare("SELECT id FROM peliculas WHERE id = ?");
$stmt_verificar->bind_param("i", $movie_id);
$stmt_verificar->execute();
$resultado_verificar = $stmt_verificar->get_result();

if ($resultado_verificar->num_rows === 0) {
    echo "La película especificada no existe.";
    exit();
}

// Insertar el comentario
$stmt = $conn->prepare("INSERT INTO comentarios (usuario_id, movie_id, comentario, fecha) VALUES (?, ?, ?, NOW())");
$stmt->bind_param("iis", $usuario_id, $movie_id, $comentario);

try {
    $stmt->execute();
    echo "Comentario guardado exitosamente.";
    header("Location: detalle_pelicula.php?id=" . $movie_id);
    exit();
} catch (mysqli_sql_exception $e) {
    echo "Error al guardar el comentario: " . $e->getMessage();
}

$stmt_verificar->close();
$stmt->close();
$conn->close();
?>

