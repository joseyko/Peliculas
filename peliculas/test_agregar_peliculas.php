<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: auth/login.php");
    exit();
}

include '../includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    echo "Formulario enviado";
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $fecha_estreno = $_POST['fecha_estreno'];
    var_dump($_FILES);
    $imagen = $_FILES['imagen'];
    $ruta_imagen = '../assets/imagenes/' . basename($imagen['name']);
    
    if (move_uploaded_file($imagen['tmp_name'], $ruta_imagen)) {
        echo "Imagen subida correctamente: $ruta_imagen";
    } else {
        echo "Error al subir la imagen.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba Agregar Película</title>
</head>
<body>
    <form action="" method="POST" enctype="multipart/form-data">
        <input type="text" name="titulo" placeholder="Título" required>
        <textarea name="descripcion" placeholder="Descripción" required></textarea>
        <input type="date" name="fecha_estreno" required>
        <input type="file" name="imagen" required>
        <button type="submit">Agregar Película</button>
    </form>
</body>
</html>
