<?php
session_start();
include 'includes/conexion.php'; // Asegurarse de incluir la conexión desde la carpeta includes

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: auth/login.php"); // Redirigir a la página de inicio de sesión
    exit();
}

// Manejar el envío del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pelicula_id = $_POST['pelicula_id'];
    $puntuacion = $_POST['puntuacion'];
    $usuario_id = $_SESSION['usuario_id'];

    // Preparar la consulta para insertar la puntuación
    $stmt = $conn->prepare("INSERT INTO puntuaciones (usuario_id, pelicula_id, puntuacion) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $usuario_id, $pelicula_id, $puntuacion);
    
    if ($stmt->execute()) {
        header("Location: peliculas.php?mensaje=Puntuación registrada con éxito");
    } else {
        echo "<div class='alert alert-danger'>Error al registrar la puntuación: " . $conn->error . "</div>"; // Mostrar mensaje de error
    }

    $stmt->close();
    $conn->close();
    exit();
}
?>
