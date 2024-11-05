<?php
session_start();
include_once '../includes/conexion.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../auth/login2.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$favorito_id = $_GET['id'] ?? null;

if ($favorito_id) {
    $stmt = $conn->prepare("DELETE FROM favoritos WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $favorito_id, $usuario_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: perfil.php");
exit();
