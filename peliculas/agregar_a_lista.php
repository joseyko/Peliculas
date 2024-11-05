<?php
session_start();
include_once '../includes/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../auth/login2.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$pelicula_id = $_POST['pelicula_id'] ?? null;
$lista_id = $_POST['lista_id'] ?? null;

if ($pelicula_id && $lista_id) {
    // Verificar que la lista pertenece al usuario
    $stmt = $conn->prepare("SELECT id FROM listas WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $lista_id, $usuario_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Insertar en listas_peliculas
        $stmt_insert = $conn->prepare("INSERT INTO peliculas_en_lista (lista_id, pelicula_id) VALUES (?, ?)");
        $stmt_insert->bind_param("ii", $lista_id, $pelicula_id);
        $stmt_insert->execute();
        $stmt_insert->close();

        echo "Película añadida a la lista.";
        header("Location: detalle_pelicula.php?id=$pelicula_id");
    } else {
        echo "No tienes permisos para añadir películas a esta lista.";
    }
    $stmt->close();
} else {
    echo "Datos incompletos.";
}
?>
