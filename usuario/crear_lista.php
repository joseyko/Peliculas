<?php
session_start();

include_once '../includes/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../auth/login2.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$nombre = $_POST['nombre'] ?? null;
$descripcion = $_POST['descripcion'] ?? null;
$privacidad = $_POST['privacidad'] ?? 'privada';

if ($nombre) {
    $stmt = $conn->prepare("INSERT INTO listas (usuario_id, nombre, descripcion, privacidad) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $usuario_id, $nombre, $descripcion, $privacidad);

    if ($stmt->execute()) {
        echo "Lista creada con éxito.";
        header("Location: perfil.php");
    } else {
        echo "Error al crear la lista.";
    }
    $stmt->close();
} else {
    echo "Por favor, proporciona un nombre para la lista.";
}
?>
