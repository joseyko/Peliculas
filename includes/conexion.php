<?php
$servidor = "localhost";
$usuario = "root";
$contrasena = "";
$base_datos = "peliculas_db";  // Asegúrate de que el nombre de la base de datos es correcto

// Crear la conexión
$conn = new mysqli($servidor, $usuario, $contrasena, $base_datos);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
